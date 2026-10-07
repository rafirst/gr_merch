<?php

namespace App\Http\Controllers;

use App\Exports\HistoryExport;
use App\Exports\ItemsExport;
use App\Exports\ItemsImportTemplateExport;
use App\Exports\StockInImportTemplateExport;
use App\Exports\StockItemsExport;
use App\Imports\ItemsImport;
use App\Imports\StockInImport;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;

class ExportImportController extends Controller
{
    public function exportItems()
    {
        return $this->downloadPdf(
            new ItemsExport,
            'Laporan Merchandise',
            'Daftar merchandise beserta kategori, harga, dan stok saat ini.',
            'Data Merchandise',
            'data-items-gr-merch-'.now()->format('Ymd-His').'.pdf',
        );
    }

    public function exportItemsImportTemplate()
    {
        return Excel::download(new ItemsImportTemplateExport, 'template-import-items-gr-merch.xlsx');
    }

    public function exportHistory()
    {
        return $this->downloadPdf(
            new HistoryExport,
            'Laporan Histori Transaksi',
            'Catatan transaksi barang keluar yang tercatat dalam sistem.',
            'Histori Transaksi',
            'histori-transaksi-gr-merch-'.now()->format('Ymd-His').'.pdf',
        );
    }

    public function exportHistoryExcel()
    {
        abort_unless(auth()->user()?->isAdminHo(), 403, 'Hanya administrator yang dapat mengekspor Excel.');

        return Excel::download(
            new HistoryExport,
            'histori-transaksi-gr-merch-'.now()->format('Ymd-His').'.xlsx',
        );
    }

    public function exportStockIn()
    {
        return $this->downloadPdf(
            new StockItemsExport,
            'Laporan Stok Merchandise',
            'Ringkasan jumlah dan status stok merchandise saat ini.',
            'Data Merchandise',
            'data-stok-items-gr-merch-'.now()->format('Ymd-His').'.pdf',
        );
    }

    public function exportStockInImportTemplate()
    {
        return Excel::download(new StockInImportTemplateExport, 'template-import-barang-masuk-gr-merch.xlsx');
    }

    private function downloadPdf(object $export, string $title, string $subtitle, string $sectionTitle, string $filename): Response
    {
        $rows = $export->collection()
            ->map(fn ($row): array => $export->map($row))
            ->all();

        $pdf = new Dompdf;
        $pdf->loadHtml(view('exports.table-pdf', [
            'title' => $title,
            'subtitle' => $subtitle,
            'sectionTitle' => $sectionTitle,
            'headings' => $export->headings(),
            'rows' => $rows,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->render(), 'UTF-8');
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function showImportForm()
    {
        return view('items.import');
    }

    public function showStockInImportForm()
    {
        return view('stockin.import');
    }

    public function importStockIn(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $import = new StockInImport;

        try {
            Excel::import($import, $request->file('file'));
        } catch (PhpSpreadsheetException) {
            return redirect()->route('stockin.index')->with('error', 'Import gagal. File tidak dapat dibaca atau tidak berisi data.');
        }

        if ($import->getImportedRows() === 0) {
            return redirect()->route('stockin.index')->with('error', 'Import gagal. Tidak ada baris valid. Pastikan kode_items, jumlah, kode_cabang, dan sumber sudah diisi dengan benar.');
        }

        if ($import->getSkippedRows() > 0) {
            return redirect()->route('stockin.index')->with('error', "Import selesai dengan catatan. {$import->getImportedRows()} baris berhasil dan {$import->getSkippedRows()} baris dilewati.");
        }

        return redirect()->route('stockin.index')->with('success', "Barang masuk berhasil diimport ({$import->getImportedRows()} baris).");
    }

    public function importItems(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $import = new ItemsImport;

        try {
            Excel::import($import, $request->file('file'));
        } catch (PhpSpreadsheetException) {
            return redirect()->route('items.index')->with(
                'error',
                'Import gagal. File tidak berisi baris data yang dapat dibaca atau formatnya rusak. Gunakan template dan isi minimal satu baris data.',
            );
        }

        if ($import->getImportedRows() === 0) {
            return redirect()->route('items.index')->with(
                'error',
                'Import gagal. Tidak ada baris data yang valid. Pastikan kode_items, nama_items, dan kode_cabang sudah sesuai.',
            );
        }

        if ($import->getSkippedRows() > 0) {
            return redirect()->route('items.index')->with(
                'error',
                "Import selesai dengan catatan. {$import->getImportedRows()} baris berhasil diproses dan {$import->getSkippedRows()} baris tidak valid dilewati.",
            );
        }

        return redirect()->route('items.index')->with('success', "Data items berhasil diimport ({$import->getImportedRows()} baris).");
    }
}
