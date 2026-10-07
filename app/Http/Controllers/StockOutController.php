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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

    public function edit(StockOut $stockOut)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isAdminHo() && $stockOut->cabang_id !== $user->cabang_id) {
            abort(403, 'Anda tidak memiliki akses ke transaksi cabang lain.');
        }

        if ($stockOut->status === 'rejected') {
            abort(403, 'Transaksi yang ditolak tidak dapat diubah.');
        }

        $stockOut->load(['item', 'cabang', 'user', 'approver']);
        $stockOuts = $stockOut->batch_id
            ? StockOut::with(['item', 'cabang', 'user', 'approver'])->where('batch_id', $stockOut->batch_id)->orderBy('id')->get()
            : collect([$stockOut]);
        $items = $user->isAdminHo()
            ? Item::with('cabang')->orderBy('nama_items')->get()
            : Item::with('cabang')->where('cabang_id', $user->cabang_id)->orderBy('nama_items')->get();

        return view('stockout.edit', compact('stockOut', 'stockOuts', 'items'));
    }

    public function update(Request $request, StockOut $stockOut)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isAdminHo() && $stockOut->cabang_id !== $user->cabang_id) {
            abort(403, 'Anda tidak memiliki akses ke transaksi cabang lain.');
        }

        if ($stockOut->status === 'rejected') {
            abort(403, 'Transaksi yang ditolak tidak dapat diubah.');
        }

        $data = $request->validate([
            'item_id' => 'required|array|min:1',
            'item_id.*' => 'required|exists:items,id',
            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|integer|min:1',
            'pic_penjualan' => 'nullable|string|max:255',
            'nomor_spk' => 'nullable|string|max:100',
            'nomor_im' => 'nullable|string|max:100',
            'jenis_pembayaran' => 'nullable|in:qris,transfer',
            'nama_customer' => 'nullable|string|max:255',
            'nik_ktp' => 'nullable|string|max:16',
            'jabatan' => 'nullable|string|max:100',
            'nomor_telepon' => 'nullable|string|max:30',
            'alamat_customer' => 'nullable|string|max:2000',
            'keterangan' => 'nullable|string|max:2000',
        ], [], [
            'jumlah' => 'jumlah',
        ]);

        $batchId = $stockOut->batch_id;
        $batchRows = $batchId
            ? StockOut::with('item')->where('batch_id', $batchId)->orderBy('id')->get()
            : StockOut::with('item')->whereKey($stockOut->id)->get();

        $items = Item::whereIn('id', $data['item_id'])->get()->keyBy('id');

        foreach ($data['item_id'] as $index => $itemId) {
            $item = $items->get($itemId);

            if (! $item) {
                return back()->withErrors(['item_id' => 'Item tidak ditemukan.'])->withInput();
            }

            if (! $user->isAdminHo() && $item->cabang_id !== $user->cabang_id) {
                abort(403, 'Anda tidak memiliki akses ke item cabang lain.');
            }
        }

        DB::transaction(function () use ($data, $stockOut, $batchRows, $items) {
            $isApproved = $stockOut->status === 'approved';

            if ($isApproved) {
                foreach ($batchRows as $row) {
                    if ($row->item) {
                        $row->item->increment('stok_items', $row->jumlah);
                    }
                }

                $items = $items->map(fn ($item) => $item->fresh());
            }

            $firstRow = $batchRows->first() ?? $stockOut;
            $isPenjualan = $firstRow->jenis === 'penjualan';
            $discountRate = (float) ($firstRow->discount ?? 0);
            $shared = [
                'jenis_pembayaran' => $data['jenis_pembayaran'] ?? $firstRow->jenis_pembayaran,
                'nomor_spk' => $data['nomor_spk'] ?? $firstRow->nomor_spk,
                'nomor_im' => $data['nomor_im'] ?? $firstRow->nomor_im,
                'nama_customer' => $data['nama_customer'] ?? $firstRow->nama_customer,
                'nik_ktp' => $firstRow->isRetailNonKtpSale() ? null : ($data['nik_ktp'] ?? $firstRow->nik_ktp),
                'jabatan' => $data['jabatan'] ?? $firstRow->jabatan,
                'nomor_telepon' => $data['nomor_telepon'] ?? $firstRow->nomor_telepon,
                'alamat_customer' => $data['alamat_customer'] ?? $firstRow->alamat_customer,
                'pic_penjualan' => $data['pic_penjualan'] ?? $firstRow->pic_penjualan,
                'keterangan' => $data['keterangan'] ?? $firstRow->keterangan,
            ];

            $remainingIds = [];
            $orderedRows = $batchRows->values();

            foreach ($data['item_id'] as $index => $itemId) {
                $item = $items->get($itemId);
                $quantity = (int) $data['jumlah'][$index];
                $row = $orderedRows->shift();

                if ($stockOut->status === 'approved') {
                    $available = (int) $item->stok_items;

                    if ($quantity > $available) {
                        throw ValidationException::withMessages([
                            'jumlah' => 'Stok '.$item->nama_items.' tidak mencukupi. Stok tersedia: '.$available,
                        ]);
                    }
                }

                $hargaJual = $isPenjualan ? (float) $item->harga_jual : null;
                $total = $isPenjualan ? $hargaJual * $quantity * (1 - ($discountRate / 100)) : null;

                if ($row) {
                    $row->update(array_merge($shared, [
                        'item_id' => $item->id,
                        'cabang_id' => $item->cabang_id,
                        'jumlah' => $quantity,
                        'harga_jual' => $hargaJual,
                        'total' => $total,
                    ]));
                    $remainingIds[] = $row->id;
                } else {
                    $created = StockOut::create(array_merge($shared, [
                        'item_id' => $item->id,
                        'cabang_id' => $item->cabang_id,
                        'jumlah' => $quantity,
                        'jenis' => $firstRow->jenis,
                        'harga_jual' => $hargaJual,
                        'discount' => $firstRow->discount,
                        'paket_bundling' => $firstRow->paket_bundling,
                        'total' => $total,
                        'batch_id' => $firstRow->batch_id,
                        'status' => $firstRow->status,
                        'tanggal' => $firstRow->tanggal,
                        'user_id' => $firstRow->user_id,
                        'approved_by' => $firstRow->approved_by,
                        'approved_at' => $firstRow->approved_at,
                    ]));
                    $remainingIds[] = $created->id;
                }

                if ($isApproved) {
                    $item->decrement('stok_items', $quantity);
                }
            }

            StockOut::whereIn('id', $batchRows->pluck('id')->diff($remainingIds))->delete();
        });

        return redirect()->route('stockout.index')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function updatePaymentProof(Request $request, StockOut $stockOut)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isAdminHo() && $stockOut->cabang_id !== $user->cabang_id) {
            abort(403, 'Anda tidak memiliki akses ke transaksi cabang lain.');
        }

        if ($stockOut->status !== 'approved') {
            return redirect()->route('stockout.index')->with('error', 'Bukti pembayaran hanya untuk transaksi approved.');
        }

        $data = $request->validate([
            'kode_pembayaran' => 'required|string|max:100',
            'bukti_pembayaran' => 'nullable|file|mimes:pdf|max:5120',
            '_redirect_to' => 'nullable|in:index,edit',
        ]);

        $batchRows = $stockOut->batch_id
            ? StockOut::where('batch_id', $stockOut->batch_id)->get()
            : collect([$stockOut]);

        $proofPath = $batchRows->firstWhere('bukti_pembayaran')?->bukti_pembayaran;

        if ($request->hasFile('bukti_pembayaran')) {
            if ($proofPath) {
                Storage::disk('public')->delete($proofPath);
            }

            $proofPath = $request->file('bukti_pembayaran')->store('stockout-payment-proof', 'public');
        }

        StockOut::whereIn('id', $batchRows->pluck('id'))->update([
            'kode_pembayaran' => $data['kode_pembayaran'],
            'bukti_pembayaran' => $proofPath,
        ]);

        $redirect = ($data['_redirect_to'] ?? 'index') === 'edit'
            ? redirect()->route('stockout.edit', $stockOut)
            : redirect()->route('stockout.index');

        return $redirect->with('success', 'Bukti pembayaran berhasil disimpan.');
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
            'jenis_pembayaran' => 'required_if:jenis,penjualan|nullable|in:qris,transfer',
            'nomor_spk' => 'required_if:jenis,DO|nullable|string|max:100',
            'nomor_im' => 'required_if:jenis,request|nullable|string|max:100',
            'nama_customer' => 'required_if:jenis,penjualan|nullable|string|max:255',
            'nik_ktp' => [
                Rule::requiredIf(fn (): bool => $this->butuhNikKtp($request)),
                'nullable',
                'string',
                'regex:/^[0-9]{16}$/',
            ],
            'jabatan' => [
                Rule::requiredIf(fn (): bool => $this->butuhJabatan($request)),
                'nullable',
                'string',
                'max:100',
            ],
            'nomor_telepon' => [
                Rule::requiredIf(fn (): bool => $this->butuhKontakCustomer($request)),
                'nullable',
                'regex:/^[0-9]+$/',
                'max:30',
            ],
            'alamat_customer' => [
                Rule::requiredIf(fn (): bool => $this->butuhKontakCustomer($request)),
                'nullable',
                'string',
                'max:2000',
            ],
            'pic_penjualan' => 'nullable|string|max:255',
            'discount' => 'required_if:jenis,penjualan|nullable|in:member,retail,retail_non_ktp',
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
        $isMemberSale = $isPenjualan && ($data['discount'] ?? null) === 'member';
        $isRetailNonKtpSale = $isPenjualan && ($data['discount'] ?? null) === 'retail_non_ktp';

        $discount = match ($data['discount'] ?? null) {
            'member' => StockOut::DISCOUNT_TAG_MEMBER,
            'retail' => StockOut::DISCOUNT_RETAIL_KTP,
            'retail_non_ktp' => StockOut::DISCOUNT_RETAIL_NON_KTP,
            default => 0,
        };
        $requiresApproval = $data['jenis'] === 'request'
            || ($isPenjualan && ($data['discount'] ?? 'retail') === 'member');
        $stockOutRows = [];

        foreach ($data['item_id'] as $index => $itemId) {
            $item = $items->get($itemId);
            $hargaJual = $isPenjualan ? (float) $item->harga_jual : null;
            $jumlah = (int) $data['jumlah'][$index];
            $total = $isPenjualan ? $hargaJual * $jumlah * (1 - ($discount / 100)) : null;

            $stockOutRows[] = compact('item', 'jumlah', 'hargaJual', 'total');
        }

        $batchId = (string) Str::uuid();

        DB::transaction(function () use ($data, $user, $requiresApproval, $discount, $isMemberSale, $isRetailNonKtpSale, $stockOutRows, $batchId) {
            foreach ($stockOutRows as $row) {
                $item = $row['item'];

                StockOut::create([
                    'item_id' => $item->id,
                    'cabang_id' => $item->cabang_id,
                    'jumlah' => $row['jumlah'],
                    'jenis' => $data['jenis'],
                    'jenis_pembayaran' => $data['jenis_pembayaran'] ?? null,
                    'nomor_spk' => $data['jenis'] === 'DO' ? ($data['nomor_spk'] ?? null) : null,
                    'nomor_im' => $data['jenis'] === 'request' ? ($data['nomor_im'] ?? null) : null,
                    'nomor_telepon' => $isRetailNonKtpSale ? null : ($data['nomor_telepon'] ?? null),
                    'nama_customer' => $data['nama_customer'] ?? null,
                    // NIK khusus Retail - KTP, jabatan khusus TAG Member.
                    'nik_ktp' => $isMemberSale || $isRetailNonKtpSale ? null : ($data['nik_ktp'] ?? null),
                    'jabatan' => $isMemberSale ? ($data['jabatan'] ?? null) : null,
                    'alamat_customer' => $isRetailNonKtpSale ? null : ($data['alamat_customer'] ?? null),
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

    /**
     * NIK KTP hanya wajib untuk penjualan Retail - KTP.
     * Jenis DO/request tidak memakai data identitas customer.
     */
    private function butuhNikKtp(Request $request): bool
    {
        return $request->input('jenis') === 'penjualan'
            && $request->input('discount') === 'retail';
    }

    private function butuhKontakCustomer(Request $request): bool
    {
        return $request->input('jenis') === 'penjualan'
            && $request->input('discount') !== 'retail_non_ktp';
    }

    /**
     * Jabatan wajib untuk penjualan TAG Member.
     */
    private function butuhJabatan(Request $request): bool
    {
        return $request->input('jenis') === 'penjualan'
            && $request->input('discount') === 'member';
    }
}
