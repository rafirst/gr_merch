<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ItemsImportTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return [
            'kode_items',
            'nama_items',
            'kategori',
            'harga_items',
            'harga_jual',
            'kode_cabang',
        ];
    }
}
