<?php

namespace App\Http\Controllers;

use App\Exports\HistoryExport;
use App\Exports\ItemsExport;
use App\Imports\ItemsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportImportController extends Controller
{
    public function exportItems()
    {
        return Excel::download(new ItemsExport, 'data-items-gr-merch-'.now()->format('Ymd-His').'.xlsx');
    }

    public function exportHistory()
    {
        return Excel::download(new HistoryExport, 'histori-transaksi-gr-merch-'.now()->format('Ymd-His').'.xlsx');
    }

    public function showImportForm()
    {
        return view('items.import');
    }

    public function importItems(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new ItemsImport, $request->file('file'));

        return redirect()->route('items.index')->with('success', 'Data items berhasil diimport.');
    }
}
