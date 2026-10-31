<?php

namespace App\Http\Controllers;

use App\Exports\HistoryExport;
use App\Exports\ItemsExport;
use App\Exports\ItemsImportTemplateExport;
use App\Exports\StockInImportTemplateExport;
use App\Exports\StockItemsExport;
use App\Imports\ItemsImport;
use App\Imports\StockInImport;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\User;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

    public function exportItemsExcel()
    {
        return Excel::download(
            new ItemsExport,
            'data-items-gr-merch-'.now()->format('Ymd-His').'.xlsx',
        );
    }

    public function exportItemsImportTemplate()
    {
        return Excel::download(new ItemsImportTemplateExport, 'template-import-items-gr-merch.xlsx');
    }

    public function exportHistory(Request $request)
    {
        $filters = $this->historyFilters($request);
        $export = new HistoryExport($filters);
        $rows = $export->collection();
        $sectionTitle = match ($filters['tipe'] ?? null) {
            'in' => 'Barang Masuk',
            'out' => 'Barang Keluar',
            'approval' => 'Items Approval',
            default => 'Histori Transaksi',
        };
        $subtitle = match ($filters['tipe'] ?? null) {
            'in' => 'Riwayat transaksi barang masuk sesuai filter yang dipilih.',
            'out' => 'Riwayat transaksi barang keluar sesuai filter yang dipilih.',
            'approval' => 'Riwayat persetujuan sesuai filter yang dipilih.',
            default => 'Seluruh riwayat transaksi sesuai filter yang dipilih.',
        };

        if ($rows->isEmpty()) {
            /** @var User|null $user */
            $user = Auth::user();

            Log::warning('History PDF export returned no visible transactions.', [
                'user_id' => Auth::id(),
                'user_role' => $user?->role,
                'user_cabang_id' => $user?->cabang_id,
                'visible_rows' => 0,
                'total_stock_in_rows' => StockIn::query()->count(),
                'total_stock_out_rows' => StockOut::query()->count(),
            ]);
        }

        return $this->downloadPdf(
            $export,
            'Laporan Histori Transaksi',
            $subtitle,
            $sectionTitle,
            'histori-transaksi-gr-merch-'.now()->format('Ymd-His').'.pdf',
            $rows,
        );
    }

    public function exportHistoryExcel(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user?->isAdminHo(), 403, 'Hanya administrator yang dapat mengekspor Excel.');

        return Excel::download(
            new HistoryExport($this->historyFilters($request)),
            'histori-transaksi-gr-merch-'.now()->format('Ymd-His').'.xlsx',
        );
    }

    /**
     * @return array{tipe?: string|null, jenis?: string|null, item?: string|null, dari?: string|null, sampai?: string|null}
     */
    private function historyFilters(Request $request): array
    {
        return $request->validate([
            'tipe' => 'nullable|in:in,out,approval',
            'jenis' => 'nullable|in:penjualan,request,DO',
            'item' => 'nullable|string|max:255',
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date',
        ]);
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

    public function exportStockInExcel()
    {
        return Excel::download(
            new StockItemsExport,
            'data-stok-items-gr-merch-'.now()->format('Ymd-His').'.xlsx',
        );
    }

    public function exportStockInImportTemplate()
    {
        return Excel::download(new StockInImportTemplateExport, 'template-import-barang-masuk-gr-merch.xlsx');
    }

    private function downloadPdf(
        object $export,
        string $title,
        string $subtitle,
        string $sectionTitle,
        string $filename,
        ?Collection $collection = null,
    ): Response {
        $rows = ($collection ?? $export->collection())
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
