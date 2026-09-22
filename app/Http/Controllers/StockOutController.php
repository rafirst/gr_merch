<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockOut;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockOutController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $query = StockOut::with(['item', 'cabang', 'user', 'approver']);

        if (! $user->isAdminHo()) {
            $query->where('cabang_id', $user->cabang_id);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $groupedStockOuts = $query->latest('tanggal')->get()
            ->groupBy(fn (StockOut $stockOut): string => $stockOut->batch_id ?: 'single-'.$stockOut->id)
            ->map(function ($group): StockOut {
                $stockOut = $group->first();
                $stockOut->setAttribute('item_count', $group->count());

                return $stockOut;
            })
            ->values();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $stockOuts = new LengthAwarePaginator(
            $groupedStockOuts->forPage($page, 15)->values(),
            $groupedStockOuts->count(),
            15,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => request()->query()]
        );

        return view('stockout.index', compact('stockOuts'));
    }

    public function create()
    {
        /** @var User $user */
        $user = Auth::user();
        $items = $user->isAdminHo() ? Item::orderBy('nama_items')->get() : Item::where('cabang_id', $user->cabang_id)->orderBy('nama_items')->get();

        return view('stockout.create', compact('items'));
    }

    public function show(StockOut $stockOut)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isAdminHo() && $stockOut->cabang_id !== $user->cabang_id) {
            abort(403, 'Anda tidak memiliki akses ke transaksi cabang lain.');
        }

        $stockOut->load(['item', 'cabang', 'user', 'approver']);
        $stockOuts = $stockOut->batch_id
            ? StockOut::with(['item', 'cabang', 'user', 'approver'])->where('batch_id', $stockOut->batch_id)->get()
            : collect([$stockOut]);

        return view('stockout.show', compact('stockOut', 'stockOuts'));
    }

    public function invoice(StockOut $stockOut)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isAdminHo() && $stockOut->cabang_id !== $user->cabang_id) {
            abort(403, 'Anda tidak memiliki akses ke transaksi cabang lain.');
        }

        $stockOut->load(['item', 'cabang', 'user', 'approver']);
        $stockOuts = $stockOut->batch_id
            ? StockOut::with(['item', 'cabang', 'user', 'approver'])->where('batch_id', $stockOut->batch_id)->get()
            : collect([$stockOut]);

        return view('stockout.invoice', compact('stockOut', 'stockOuts'));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'item_id' => 'required|array|min:1',
            'item_id.*' => 'required|exists:items,id',
            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|integer|min:1',
            'jenis' => 'required|in:penjualan,DO,request',
            'nomor_spk' => 'required_if:jenis,DO|required_if:jenis,request|nullable|string|max:100',
            'nomor_telepon' => ['nullable', 'regex:/^[0-9]+$/', 'max:30'],
            'nama_customer' => 'nullable|string|max:255',
            'pic_penjualan' => 'nullable|string|max:255',
            'harga_jual' => 'nullable|array',
            'harga_jual.*' => 'nullable|numeric|min:0',
            'discount' => 'required_if:jenis,penjualan|nullable|in:member,retail',
            'voucher' => 'required_if:jenis,DO|nullable|in:500k,1jt',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $items = Item::whereIn('id', $data['item_id'])->get()->keyBy('id');
        $quantitiesByItem = [];

        foreach ($data['item_id'] as $index => $itemId) {
            $item = $items->get($itemId);

            if (! $user->isAdminHo() && $item->cabang_id !== $user->cabang_id) {
                abort(403, 'Anda tidak memiliki akses ke item cabang lain.');
            }

            $quantitiesByItem[$itemId] = ($quantitiesByItem[$itemId] ?? 0) + (int) $data['jumlah'][$index];
        }

        foreach ($quantitiesByItem as $itemId => $quantity) {
            $item = $items->get($itemId);

            if ($quantity > $item->stok_items) {
                return back()->withErrors(['jumlah' => 'Stok '.$item->nama_items.' tidak mencukupi. Stok tersedia: '.$item->stok_items])->withInput();
            }
        }

        // Penjualan dan DO langsung approved; hanya Request yang perlu approval.
        $isPenjualan = $data['jenis'] === 'penjualan';
        $requiresApproval = $data['jenis'] === 'request';

        $discount = $isPenjualan
            ? match ($data['discount'] ?? 'retail') {
                'member' => 20,
                'retail' => 15,
                default => 15,
            }
        : 0;
        $stockOutRows = [];

        foreach ($data['item_id'] as $index => $itemId) {
            $item = $items->get($itemId);
            $hargaJual = $isPenjualan ? ($data['harga_jual'][$index] ?? $item->harga_items) : null;
            $jumlah = (int) $data['jumlah'][$index];
            $total = $isPenjualan ? $hargaJual * $jumlah * (1 - ($discount / 100)) : null;

            $stockOutRows[] = compact('item', 'jumlah', 'hargaJual', 'total');
        }

        $batchId = (string) Str::uuid();

        DB::transaction(function () use ($data, $user, $isPenjualan, $requiresApproval, $discount, $stockOutRows, $batchId) {
            foreach ($stockOutRows as $row) {
                $item = $row['item'];

                StockOut::create([
                    'item_id' => $item->id,
                    'cabang_id' => $item->cabang_id,
                    'jumlah' => $row['jumlah'],
                    'jenis' => $data['jenis'],
                    'nomor_spk' => $data['nomor_spk'] ?? null,
                    'nomor_telepon' => $data['nomor_telepon'] ?? null,
                    'nama_customer' => $data['nama_customer'] ?? null,
                    'pic_penjualan' => $data['pic_penjualan'] ?? null,
                    'batch_id' => $batchId,
                    'harga_jual' => $row['hargaJual'],
                    'discount' => $discount,
                    'voucher' => $data['voucher'] ?? null,
                    'total' => $row['total'],
                    'status' => $requiresApproval ? 'pending' : 'approved',
                    'tanggal' => $data['tanggal'],
                    'keterangan' => $data['keterangan'] ?? null,
                    'user_id' => $user->id,
                    'approved_by' => $requiresApproval ? null : $user->id,
                    'approved_at' => $requiresApproval ? null : now(),
                ]);

                if (! $requiresApproval) {
                    $item->decrement('stok_items', $row['jumlah']);
                }
            }
        });

        $pesan = $isPenjualan
            ? 'Penjualan berhasil dicatat & stok berkurang.'
            : 'Permintaan barang keluar berhasil diajukan, menunggu approval Admin Pusat.';

        return redirect()->route('stockout.index')->with('success', $pesan);
    }
}
