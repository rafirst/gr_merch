<?php

namespace Tests\Feature;

use App\Exports\HistoryExport;
use App\Models\Cabang;
use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ExportPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_export_routes_download_pdf_files(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin_ho',
            'cabang_id' => null,
        ]);

        $this->actingAs($admin);

        foreach (['items.export', 'history.export', 'stockin.export'] as $routeName) {
            $response = $this->get(route($routeName));

            $response->assertOk()
                ->assertHeader('content-type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent());
        }
    }

    public function test_history_excel_export_downloads_an_xlsx_file(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin_ho',
            'cabang_id' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('history.export.excel'));

        $response->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_staff_cabang_cannot_access_history_excel_export(): void
    {
        $cabang = Cabang::create([
            'kode_cabang' => 'TAG-02',
            'nama_cabang' => 'TAG Cabang Staff',
        ]);
        /** @var User $staff */
        $staff = User::factory()->create([
            'role' => 'staff_cabang',
            'cabang_id' => $cabang->id,
        ]);

        $response = $this->actingAs($staff)->get(route('history.export.excel'));

        $response->assertForbidden();
    }

    public function test_staff_cabang_still_can_access_history_pdf_export(): void
    {
        $cabang = Cabang::create([
            'kode_cabang' => 'TAG-03',
            'nama_cabang' => 'TAG Cabang PDF',
        ]);
        /** @var User $staff */
        $staff = User::factory()->create([
            'role' => 'staff_cabang',
            'cabang_id' => $cabang->id,
        ]);

        $response = $this->actingAs($staff)->get(route('history.export'));

        $response->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_empty_history_pdf_export_logs_data_scope_diagnostics(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin_ho',
            'cabang_id' => null,
        ]);

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $message === 'History PDF export returned no visible transactions.'
                && $context['user_id'] === $admin->id
                && $context['user_role'] === 'admin_ho'
                && $context['user_cabang_id'] === null
                && $context['visible_rows'] === 0
                && $context['total_stock_in_rows'] === 0
                && $context['total_stock_out_rows'] === 0);

        $response = $this->actingAs($admin)->get(route('history.export'));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_history_exports_include_manual_and_imported_stock_in_transactions(): void
    {
        $cabang = Cabang::create([
            'kode_cabang' => 'TAG-05',
            'nama_cabang' => 'TAG Cabang Import',
        ]);
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin_ho',
            'cabang_id' => null,
        ]);
        $item = Item::create([
            'kode_items' => 'ITEM-IN-001',
            'nama_items' => 'Test Item Barang Masuk',
            'kategori' => 'jacket',
            'harga_items' => 100000,
            'stok_items' => 5,
            'harga_jual' => 120000,
            'cabang_id' => $cabang->id,
        ]);
        StockIn::create([
            'item_id' => $item->id,
            'cabang_id' => $cabang->id,
            'jumlah' => 2,
            'tanggal' => '2026-10-12',
            'sumber' => 'Input manual',
            'user_id' => $admin->id,
        ]);
        StockOut::create([
            'item_id' => $item->id,
            'cabang_id' => $cabang->id,
            'jumlah' => 1,
            'jenis' => 'penjualan',
            'harga_jual' => 120000,
            'total' => 120000,
            'status' => 'approved',
            'tanggal' => '2026-10-11',
            'user_id' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('stockin.import'), [
            'file' => UploadedFile::fake()->createWithContent(
                'stock-in-import.xlsx',
                Excel::raw(new class implements FromArray, WithHeadings
                {
                    public function array(): array
                    {
                        return [['ITEM-IN-001', 3, 'TAG-05', 'Import Excel']];
                    }

                    public function headings(): array
                    {
                        return ['kode_items', 'jumlah', 'kode_cabang', 'sumber'];
                    }
                }, ExcelFormat::XLSX),
            ),
        ])->assertRedirect(route('stockin.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('stock_ins', [
            'item_id' => $item->id,
            'jumlah' => 3,
            'sumber' => 'Import Excel',
        ]);

        $pdfResponse = $this->get(route('history.export'));
        $pdfResponse->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());

        $excelContent = Excel::raw(new HistoryExport, ExcelFormat::XLSX);
        $temporaryFile = tempnam(sys_get_temp_dir(), 'history-export-');

        try {
            file_put_contents($temporaryFile, $excelContent);
            $spreadsheet = IOFactory::load($temporaryFile);
            $exportedRows = $spreadsheet->getActiveSheet()->toArray();

            $this->assertSame('Tipe Transaksi', $exportedRows[0][1]);
            $this->assertSame('Barang Masuk', $exportedRows[1][1]);
            $this->assertSame('Import Excel', $exportedRows[1][6]);
            $this->assertSame('Barang Masuk', $exportedRows[2][1]);
            $this->assertSame('Input manual', $exportedRows[2][6]);
            $this->assertSame('Barang Keluar', $exportedRows[3][1]);
        } finally {
            @unlink($temporaryFile);
        }
    }

    public function test_history_index_hides_excel_option_for_staff_cabang(): void
    {
        $cabang = Cabang::create([
            'kode_cabang' => 'TAG-04',
            'nama_cabang' => 'TAG Cabang View',
        ]);
        /** @var User $staff */
        $staff = User::factory()->create([
            'role' => 'staff_cabang',
            'cabang_id' => $cabang->id,
        ]);
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin_ho',
            'cabang_id' => null,
        ]);

        $staffResponse = $this->actingAs($staff)->get(route('history.index'));

        $staffResponse->assertOk();
        $staffResponse->assertDontSee(route('history.export.excel'), false);
        $this->assertStringNotContainsString('Export Excel', $staffResponse->getContent());

        $adminResponse = $this->actingAs($admin)->get(route('history.index'));

        $adminResponse->assertOk();
        $adminResponse->assertSee(route('history.export.excel'), false);
    }

    public function test_stock_out_invoice_can_be_downloaded_as_a_pdf(): void
    {
        $cabang = Cabang::create([
            'kode_cabang' => 'TAG-01',
            'nama_cabang' => 'TAG Head Office',
        ]);
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin_ho',
            'cabang_id' => null,
        ]);
        $item = Item::create([
            'kode_items' => 'ITEM-001',
            'nama_items' => 'Test Item',
            'kategori' => 'jacket',
            'harga_items' => 100000,
            'stok_items' => 1,
            'harga_jual' => 120000,
            'cabang_id' => $cabang->id,
        ]);
        $stockOut = StockOut::create([
            'item_id' => $item->id,
            'cabang_id' => $cabang->id,
            'jumlah' => 1,
            'jenis' => 'penjualan',
            'jenis_pembayaran' => 'qris',
            'harga_jual' => 120000,
            'total' => 120000,
            'status' => 'approved',
            'tanggal' => '2026-10-02',
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('stockout.invoice.download', $stockOut));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="invoice-000001.pdf"');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
