<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockOut;
use App\Models\User;
use Dompdf\Dompdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search): void {
                $searchQuery->where('nama_customer', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($itemQuery) use ($search): void {
                        $itemQuery->where('nama_items', 'like', "%{$search}%")
                            ->orWhere('kode_items', 'like', "%{$search}%");
                    });
            });
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

    public function downloadInvoice(StockOut $stockOut): Response|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isAdminHo() && $stockOut->cabang_id !== $user->cabang_id) {
            abort(403, 'Anda tidak memiliki akses ke transaksi cabang lain.');
        }

        try {
            $stockOut->load(['item', 'cabang', 'user', 'approver']);
            $stockOuts = $stockOut->batch_id
                ? StockOut::with(['item', 'cabang', 'user', 'approver'])->where('batch_id', $stockOut->batch_id)->get()
                : collect([$stockOut]);

            $pdf = new Dompdf([
                'chroot' => public_path(),
                'defaultMediaType' => 'print',
            ]);
            $pdf->loadHtml(view('stockout.invoice', [
                'stockOut' => $stockOut,
                'stockOuts' => $stockOuts,
                'isPdf' => true,
            ])->render(), 'UTF-8');
            $pdf->setPaper('A4', 'portrait');
            $pdf->render();

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="invoice-'.str_pad((string) $stockOut->id, 6, '0', STR_PAD_LEFT).'.pdf"',
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => config('app.debug')
                    ? 'Gagal membuat PDF invoice: '.$exception->getMessage()
                    : 'PDF invoice gagal dibuat. Silakan coba kembali atau hubungi administrator.',
            ], 500);
        }
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
            'jenis_pembayaran' => 'required|in:qris,transfer',
            'nomor_spk' => 'required_if:jenis,DO|nullable|string|max:100',
            'nomor_im' => 'required_if:jenis,request|nullable|string|max:100',
            'nama_customer' => 'required|string|max:255',
            'nik_ktp' => ['required', 'string', 'regex:/^[0-9]{16}$/'],
            'nomor_telepon' => ['required', 'regex:/^[0-9]+$/', 'max:30'],
            'alamat_customer' => 'required|string|max:2000',
            'pic_penjualan' => 'nullable|string|max:255',
            'harga_jual' => 'nullable|array',
            'harga_jual.*' => 'nullable|numeric|min:0',
            'discount' => 'required_if:jenis,penjualan|nullable|in:member,retail',
            'paket_bundling' => 'required_if:jenis,DO|nullable|in:paket_a,paket_b,paket_c',
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

        $isPenjualan = $data['jenis'] === 'penjualan';

        $discount = $isPenjualan
            ? match ($data['discount'] ?? 'retail') {
                'member' => 15,
                'retail' => 10,
                default => 10,
            }
        : 0;
        $requiresApproval = $data['jenis'] === 'request'
            || ($isPenjualan && ($data['discount'] ?? 'retail') === 'member');
        $stockOutRows = [];

        foreach ($data['item_id'] as $index => $itemId) {
            $item = $items->get($itemId);
            $hargaJual = $isPenjualan ? ($data['harga_jual'][$index] ?? $item->harga_items) : null;
            $jumlah = (int) $data['jumlah'][$index];
            $total = $isPenjualan ? $hargaJual * $jumlah * (1 - ($discount / 100)) : null;

            $stockOutRows[] = compact('item', 'jumlah', 'hargaJual', 'total');
        }

        $batchId = (string) Str::uuid();

        DB::transaction(function () use ($data, $user, $requiresApproval, $discount, $stockOutRows, $batchId) {
            foreach ($stockOutRows as $row) {
                $item = $row['item'];

                StockOut::create([
                    'item_id' => $item->id,
                    'cabang_id' => $item->cabang_id,
                    'jumlah' => $row['jumlah'],
                    'jenis' => $data['jenis'],
                    'jenis_pembayaran' => $data['jenis_pembayaran'],
                    'nomor_spk' => $data['jenis'] === 'DO' ? ($data['nomor_spk'] ?? null) : null,
                    'nomor_im' => $data['jenis'] === 'request' ? ($data['nomor_im'] ?? null) : null,
                    'nomor_telepon' => $data['nomor_telepon'],
                    'nama_customer' => $data['nama_customer'],
                    'nik_ktp' => $data['nik_ktp'],
                    'alamat_customer' => $data['alamat_customer'],
                    'pic_penjualan' => $data['pic_penjualan'] ?? null,
                    'batch_id' => $batchId,
                    'harga_jual' => $row['hargaJual'],
                    'discount' => $discount,
                    'paket_bundling' => $data['jenis'] === 'DO' ? ($data['paket_bundling'] ?? null) : null,
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

        $pesan = match (true) {
            $isPenjualan && $requiresApproval => 'Penjualan TAG Member berhasil diajukan dan menunggu approval Admin Pusat.',
            $isPenjualan => 'Penjualan berhasil dicatat & stok berkurang.',
            $data['jenis'] === 'DO' => 'DO berhasil dicatat & stok berkurang.',
            default => 'Permintaan barang keluar berhasil diajukan, menunggu approval Admin Pusat.',
        };

        return redirect()->route('stockout.index')->with('success', $pesan);
    }
}
