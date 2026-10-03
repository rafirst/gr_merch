<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Item;
use App\Models\TransferBarang;
use App\Models\TransferBarangItem;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class TransferBarangController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isAdminHo()) {
            $query = TransferBarang::with(['fromCabang', 'toCabang', 'creator', 'items.sourceItem']);

            if (in_array($request->input('status'), [
                TransferBarang::STATUS_DITERIMA,
                TransferBarang::STATUS_PROSES,
                TransferBarang::STATUS_BATAL,
            ], true)) {
                $query->where('status', (string) $request->input('status'));
            }
            if ($request->filled('search')) {
                $search = trim((string) $request->input('search'));
                $query->whereHas('items.sourceItem', function ($itemQuery) use ($search): void {
                    $itemQuery->where(function ($detailsQuery) use ($search): void {
                        $detailsQuery->where('nama_items', 'like', "%{$search}%")
                            ->orWhere('kode_items', 'like', "%{$search}%");
                    });
                });
            }

            $transfers = $query->latest()->paginate(15)->withQueryString();

            return view('transfers.index', compact('transfers'));
        }

        $status = $request->query('status');
        if ($status === null && $request->query('tab') !== null) {
            $status = $request->query('tab') === 'received'
                ? TransferBarang::STATUS_DITERIMA
                : TransferBarang::STATUS_PROSES;
        }

        $query = TransferBarang::with(['fromCabang', 'creator', 'items.sourceItem'])
            ->where('to_cabang_id', $user->cabang_id)
            ->whereIn('status', [TransferBarang::STATUS_PROSES, TransferBarang::STATUS_DITERIMA]);

        if (in_array($status, [TransferBarang::STATUS_PROSES, TransferBarang::STATUS_DITERIMA], true)) {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($searchQuery) use ($search): void {
                if (ctype_digit($search)) {
                    $searchQuery->whereKey((int) $search);
                } elseif (preg_match('/^TRF-?0*(\d+)$/i', $search, $matches) === 1) {
                    $searchQuery->whereKey((int) $matches[1]);
                }

                $searchQuery->orWhereHas('fromCabang', function ($branchQuery) use ($search): void {
                    $branchQuery->where('nama_cabang', 'like', "%{$search}%");
                })->orWhereHas('items.sourceItem', function ($itemQuery) use ($search): void {
                    $itemQuery->where('nama_items', 'like', "%{$search}%")
                        ->orWhere('kode_items', 'like', "%{$search}%");
                });
            });
        }

        $transfers = $query->latest()->paginate(15)->withQueryString();

        return view('transfers.index', compact('transfers', 'status'));
    }

    public function show(TransferBarang $transferBarang): View
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless(
            $user->isAdminHo() || ($user->cabang_id !== null && (int) $transferBarang->to_cabang_id === (int) $user->cabang_id),
            403,
            'Anda tidak memiliki akses ke transfer cabang lain.'
        );

        $transferBarang->load(['fromCabang', 'toCabang', 'creator', 'receiver', 'items.sourceItem', 'items.targetItem']);

        return view('transfers.show', ['transfer' => $transferBarang]);
    }

    public function edit(TransferBarang $transferBarang): View
    {
        abort_unless($transferBarang->status === TransferBarang::STATUS_PROSES, 404);

        $transferBarang->load(['items.sourceItem']);
        $cabangs = Cabang::orderBy('nama_cabang')->get();
        $items = Item::with('cabang')->orderBy('nama_items')->get();
        $stockAdjustments = $transferBarang->items
            ->groupBy('source_item_id')
            ->map(fn (Collection $lines): int => (int) $lines->sum('jumlah_dikirim'));
        $itemStockSummaries = $this->buildItemStockSummaries($items, $cabangs, $stockAdjustments);

        return view('transfers.edit', [
            'transfer' => $transferBarang,
            'cabangs' => $cabangs,
            'items' => $items,
            'stockAdjustments' => $stockAdjustments,
            'itemStockSummaries' => $itemStockSummaries,
        ]);
    }

    public function create(): View
    {
        $cabangs = Cabang::orderBy('nama_cabang')->get();
        $items = Item::with('cabang')->orderBy('nama_items')->get();
        $stockAdjustments = collect();
        $itemStockSummaries = $this->buildItemStockSummaries($items, $cabangs, $stockAdjustments);

        return view('transfers.create', compact('cabangs', 'items', 'itemStockSummaries', 'stockAdjustments'));
    }

    private function buildItemStockSummaries(Collection $items, Collection $cabangs, Collection $stockAdjustments): Collection
    {
        return $items->groupBy('kode_items')->map(function ($branchItems) use ($cabangs, $stockAdjustments): array {
            $sample = $branchItems->first();

            return [
                'code' => (string) $sample->kode_items,
                'name' => $sample->nama_items,
                'branches' => $cabangs->map(function (Cabang $cabang) use ($branchItems, $stockAdjustments): array {
                    $branchItem = $branchItems->firstWhere('cabang_id', $cabang->id);

                    return [
                        'name' => $cabang->nama_cabang,
                        'stock' => (int) ($branchItem->stok_items ?? 0) + (int) $stockAdjustments->get($branchItem->id ?? null, 0),
                    ];
                })->all(),
            ];
        })->values();
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $data = $request->validate([
            'from_cabang_id' => 'required|exists:cabangs,id|different:to_cabang_id',
            'to_cabang_id' => 'required|exists:cabangs,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|distinct|exists:items,id',
            'items.*.jumlah_dikirim' => 'required|integer|min:1',
            'bukti_foto' => 'nullable|array|max:5',
            'bukti_foto.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $proofPhotoPaths = $this->storeProofPhotos($data['bukti_foto'] ?? []);

        try {
            DB::transaction(function () use ($data, $user, $proofPhotoPaths): void {
                $itemIds = collect($data['items'])->pluck('item_id')->sort()->values();
                $sourceItems = Item::whereIn('id', $itemIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                foreach ($data['items'] as $index => $row) {
                    $sourceItem = $sourceItems->get($row['item_id']);
                    if (! $sourceItem || (int) $sourceItem->cabang_id !== (int) $data['from_cabang_id']) {
                        throw ValidationException::withMessages([
                            "items.{$index}.item_id" => 'Item harus berasal dari cabang asal yang dipilih.',
                        ]);
                    }
                    if ($sourceItem->stok_items < $row['jumlah_dikirim']) {
                        throw ValidationException::withMessages([
                            "items.{$index}.jumlah_dikirim" => "Stok {$sourceItem->nama_items} tidak mencukupi. Stok tersedia: {$sourceItem->stok_items}.",
                        ]);
                    }
                }

                $transfer = TransferBarang::create([
                    'from_cabang_id' => $data['from_cabang_id'],
                    'to_cabang_id' => $data['to_cabang_id'],
                    'created_by' => $user->id,
                    'status' => TransferBarang::STATUS_PROSES,
                    'sent_at' => now(),
                    'bukti_foto' => $proofPhotoPaths,
                ]);

                foreach ($data['items'] as $row) {
                    $sourceItem = $sourceItems->get($row['item_id']);
                    $sourceItem->decrement('stok_items', $row['jumlah_dikirim']);

                    $transfer->items()->create([
                        'source_item_id' => $sourceItem->id,
                        'jumlah_dikirim' => $row['jumlah_dikirim'],
                    ]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($proofPhotoPaths);
            throw $exception;
        }

        return redirect()->route('transfers.index')->with('success', 'Transfer berhasil dibuat dan stok cabang asal telah dikurangi.');
    }

    public function update(Request $request, TransferBarang $transferBarang): RedirectResponse
    {
        $data = $request->validate([
            'from_cabang_id' => 'required|exists:cabangs,id|different:to_cabang_id',
            'to_cabang_id' => 'required|exists:cabangs,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|distinct|exists:items,id',
            'items.*.jumlah_dikirim' => 'required|integer|min:1',
            'bukti_foto' => 'nullable|array|max:5',
            'bukti_foto.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'hapus_bukti_foto' => 'nullable|array',
            'hapus_bukti_foto.*' => 'nullable|string|max:255',
        ]);

        $proofPhotoPaths = $this->storeProofPhotos($data['bukti_foto'] ?? []);
        $proofPhotosToDelete = [];

        try {
            DB::transaction(function () use ($data, $transferBarang, $proofPhotoPaths, &$proofPhotosToDelete): void {
                $transfer = TransferBarang::whereKey($transferBarang->id)->lockForUpdate()->firstOrFail();
                if ($transfer->status !== TransferBarang::STATUS_PROSES) {
                    throw ValidationException::withMessages(['transfer' => 'Hanya transfer berstatus proses yang dapat diubah.']);
                }

                $existingProofPhotos = $transfer->bukti_foto ?? [];
                $proofPhotosToDelete = array_values(array_intersect(
                    $data['hapus_bukti_foto'] ?? [],
                    $existingProofPhotos
                ));
                $remainingProofPhotos = array_values(array_diff($existingProofPhotos, $proofPhotosToDelete));
                if (count($remainingProofPhotos) + count($proofPhotoPaths) > 5) {
                    throw ValidationException::withMessages([
                        'bukti_foto' => 'Maksimal 5 foto bukti transfer dapat disimpan.',
                    ]);
                }

                $lines = TransferBarangItem::where('transfer_barang_id', $transfer->id)->get();
                $itemIds = $lines->pluck('source_item_id')
                    ->merge(collect($data['items'])->pluck('item_id'))
                    ->unique()
                    ->sort()
                    ->values();
                $lockedItems = Item::whereIn('id', $itemIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                foreach ($lines as $line) {
                    $lockedItems->get($line->source_item_id)->increment('stok_items', $line->jumlah_dikirim);
                }

                foreach ($data['items'] as $index => $row) {
                    $item = $lockedItems->get($row['item_id']);
                    if (! $item || (int) $item->cabang_id !== (int) $data['from_cabang_id']) {
                        throw ValidationException::withMessages([
                            "items.{$index}.item_id" => 'Item harus berasal dari cabang asal yang dipilih.',
                        ]);
                    }
                    if ($item->stok_items < $row['jumlah_dikirim']) {
                        throw ValidationException::withMessages([
                            "items.{$index}.jumlah_dikirim" => "Stok {$item->nama_items} tidak mencukupi. Stok tersedia: {$item->stok_items}.",
                        ]);
                    }
                }

                $transfer->update([
                    'from_cabang_id' => $data['from_cabang_id'],
                    'to_cabang_id' => $data['to_cabang_id'],
                    'bukti_foto' => array_merge($remainingProofPhotos, $proofPhotoPaths),
                ]);
                $transfer->items()->delete();

                foreach ($data['items'] as $row) {
                    $item = $lockedItems->get($row['item_id']);
                    $item->decrement('stok_items', $row['jumlah_dikirim']);
                    $transfer->items()->create([
                        'source_item_id' => $item->id,
                        'jumlah_dikirim' => $row['jumlah_dikirim'],
                    ]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($proofPhotoPaths);
            throw $exception;
        }

        Storage::disk('public')->delete($proofPhotosToDelete);

        return redirect()->route('transfers.index')->with('success', 'Transfer berhasil diperbarui.');
    }

    /** @param array<int, UploadedFile> $photos */
    private function storeProofPhotos(array $photos): array
    {
        $paths = [];
        foreach ($photos as $photo) {
            $path = $photo->store('transfer-proofs', 'public');
            if (! is_string($path)) {
                Storage::disk('public')->delete($paths);
                throw new RuntimeException('Foto bukti transfer gagal disimpan.');
            }

            $paths[] = $path;
        }

        return $paths;
    }

    public function destroy(TransferBarang $transferBarang): RedirectResponse
    {
        DB::transaction(function () use ($transferBarang): void {
            $transfer = TransferBarang::whereKey($transferBarang->id)->lockForUpdate()->firstOrFail();
            if ($transfer->status === TransferBarang::STATUS_BATAL) {
                return;
            }

            $lines = TransferBarangItem::where('transfer_barang_id', $transfer->id)->get();
            $itemIds = $lines->pluck('source_item_id')
                ->merge($lines->pluck('target_item_id')->filter())
                ->unique()
                ->sort()
                ->values();
            $lockedItems = Item::whereIn('id', $itemIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            if ($transfer->status === TransferBarang::STATUS_DITERIMA) {
                foreach ($lines as $line) {
                    if ($line->jumlah_diterima > 0) {
                        $targetItem = $lockedItems->get($line->target_item_id);
                        if (! $targetItem || $targetItem->stok_items < $line->jumlah_diterima) {
                            throw ValidationException::withMessages([
                                'transfer' => 'Transfer tidak dapat dibatalkan karena stok yang diterima sudah terpakai.',
                            ]);
                        }
                    }
                }
            }

            foreach ($lines as $line) {
                $lockedItems->get($line->source_item_id)->increment('stok_items', $line->jumlah_dikirim);
                if ($transfer->status === TransferBarang::STATUS_DITERIMA && $line->jumlah_diterima > 0) {
                    $lockedItems->get($line->target_item_id)->decrement('stok_items', $line->jumlah_diterima);
                }
            }

            $transfer->update(['status' => TransferBarang::STATUS_BATAL]);
        });

        return redirect()->route('transfers.index')->with('success', 'Transfer dibatalkan dan stok telah disesuaikan.');
    }

    public function receive(Request $request, TransferBarang $transferBarang): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless(! $user->isAdminHo() && (int) $transferBarang->to_cabang_id === (int) $user->cabang_id, 403);

        $data = $request->validate([
            'items' => 'required|array',
            'items.*.jumlah_diterima' => 'required|integer|min:0',
            'items.*.catatan_selisih' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($data, $transferBarang, $user): void {
            $transfer = TransferBarang::whereKey($transferBarang->id)->lockForUpdate()->firstOrFail();
            if ($transfer->status !== TransferBarang::STATUS_PROSES) {
                throw ValidationException::withMessages(['transfer' => 'Transfer ini sudah pernah diproses.']);
            }

            $lines = TransferBarangItem::with('sourceItem')->where('transfer_barang_id', $transfer->id)->lockForUpdate()->get();
            if (count($data['items']) !== $lines->count()) {
                throw ValidationException::withMessages(['items' => 'Detail penerimaan transfer tidak lengkap.']);
            }

            foreach ($lines as $line) {
                $receivedValue = $data['items'][$line->id]['jumlah_diterima'] ?? null;
                $note = trim($data['items'][$line->id]['catatan_selisih'] ?? '');

                if ($receivedValue === null || (int) $receivedValue > (int) $line->jumlah_dikirim) {
                    throw ValidationException::withMessages([
                        "items.{$line->id}.jumlah_diterima" => 'Qty diterima wajib diisi dan tidak boleh melebihi qty yang dikirim.',
                    ]);
                }
                $received = (int) $receivedValue;
                if ($received !== (int) $line->jumlah_dikirim && $note === '') {
                    throw ValidationException::withMessages([
                        "items.{$line->id}.catatan_selisih" => 'Catatan wajib diisi jika qty diterima berbeda dari qty yang dikirim.',
                    ]);
                }

                $sourceItem = $line->sourceItem;
                $targetItem = Item::firstOrCreate(
                    ['kode_items' => $sourceItem->kode_items, 'cabang_id' => $transfer->to_cabang_id],
                    [
                        'nama_items' => $sourceItem->nama_items,
                        'kategori' => $sourceItem->kategori,
                        'harga_items' => $sourceItem->harga_items,
                        'stok_items' => 0,
                        'harga_jual' => $sourceItem->harga_jual,
                        'foto' => $sourceItem->foto,
                    ]
                );

                if ($received > 0) {
                    $targetItem->increment('stok_items', $received);
                }
                $line->update([
                    'target_item_id' => $targetItem->id,
                    'jumlah_diterima' => $received,
                    'catatan_selisih' => $note ?: null,
                ]);
            }

            $transfer->update([
                'status' => TransferBarang::STATUS_DITERIMA,
                'received_by' => $user->id,
                'received_at' => now(),
            ]);
        });

        return redirect()->route('transfers.index', ['tab' => 'received'])->with('success', 'Transfer berhasil diterima dan stok cabang Anda telah diperbarui.');
    }
}
