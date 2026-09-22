<?php

namespace App\Imports;

use App\Models\Cabang;
use App\Models\Item;
use App\Models\StockIn;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class StockInImport implements OnEachRow, WithHeadingRow
{
    private const BRANCH_CODES = [
        '1' => 'THO',
        '2' => 'PLG',
        '3' => 'LLG',
        '4' => 'TME',
        '5' => 'PRB',
        '6' => 'POL',
    ];

    private int $importedRows = 0;

    private int $skippedRows = 0;

    public function getImportedRows(): int
    {
        return $this->importedRows;
    }

    public function getSkippedRows(): int
    {
        return $this->skippedRows;
    }

    public function onRow(Row $row): void
    {
        $data = $row->toArray();
        /** @var User $user */
        $user = Auth::user();
        $kodeItems = $data['kode_items'] ?? $data['kode_item'] ?? null;
        $kodeCabang = (string) ($data['kode_cabang'] ?? '');
        $kodeCabang = self::BRANCH_CODES[$kodeCabang] ?? $kodeCabang;
        $cabang = $user->isAdminHo()
            ? Cabang::where('kode_cabang', $kodeCabang)->first()
            : $user->cabang;
        $item = $cabang
            ? Item::where('kode_items', $kodeItems)->where('cabang_id', $cabang->id)->first()
            : null;
        $jumlah = filter_var($data['jumlah'] ?? null, FILTER_VALIDATE_INT);

        if (! $cabang || ! $item || ! $jumlah || $jumlah < 1 || empty($data['sumber'])) {
            $this->skippedRows++;

            return;
        }

        DB::transaction(function () use ($data, $item, $cabang, $jumlah, $user): void {
            StockIn::create([
                'item_id' => $item->id,
                'cabang_id' => $cabang->id,
                'jumlah' => $jumlah,
                'tanggal' => now()->toDateString(),
                'sumber' => $data['sumber'],
                'user_id' => $user->id,
            ]);

            $item->increment('stok_items', $jumlah);
        });

        $this->importedRows++;
    }
}
