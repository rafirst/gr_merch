<?php

namespace Tests\Feature;

use App\Exports\HistoryExport;
use App\Models\Cabang;
use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockInEditRequest;
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

    public function test_stock_out_history_exports_add_sales_details_and_batch_totals_once(): void
    {
        $cabang = Cabang::create([
            'kode_cabang' => 'TAG-EXPORT',
            'nama_cabang' => 'TAG Export',
        ]);
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin_ho',
            'cabang_id' => null,
        ]);
        $batchId = '22222222-2222-4222-8222-222222222222';

        foreach ([
            ['ITEM-UMBRELLA-RED', 'Umbrella Red', 100000],
            ['ITEM-UMBRELLA-BLACK', 'Umbrella Black', 200000],
        ] as [$itemCode, $itemName, $price]) {
            $item = Item::create([
                'kode_items' => $itemCode,
                'nama_items' => $itemName,
                'kategori' => 'jacket',
                'harga_items' => 50000,
                'stok_items' => 5,
                'harga_jual' => $price,
                'cabang_id' => $cabang->id,
            ]);
            StockOut::create([
                'item_id' => $item->id,
                'cabang_id' => $cabang->id,
                'jumlah' => 1,
                'jenis' => 'penjualan',
                'jenis_pembayaran' => 'transfer',
                'harga_jual' => $price,
                'discount' => 10,
                'total' => $price * 0.9,
                'nama_customer' => 'Rafi',
                'nik_ktp' => '1234567890123456',
                'alamat_customer' => 'Palembang',
                'batch_id' => $batchId,
                'status' => 'approved',
                'tanggal' => '2026-11-13',
                'user_id' => $admin->id,
            ]);
        }

        $requestItem = Item::create([
            'kode_items' => 'ITEM-REQUEST-EXPORT',
            'nama_items' => 'Request Item',
            'kategori' => 'jacket',
            'harga_items' => 50000,
            'stok_items' => 5,
            'harga_jual' => 0,
            'cabang_id' => $cabang->id,
        ]);
        StockOut::create([
            'item_id' => $requestItem->id,
            'cabang_id' => $cabang->id,
            'jumlah' => 1,
            'jenis' => 'request',
            'status' => 'approved',
            'tanggal' => '2026-11-13',
            'user_id' => $admin->id,
        ]);

        $this->actingAs($admin);
        $export = new HistoryExport(['tipe' => 'out']);
        $rows = $export->collection();
        $mappedRows = $rows->map(fn (StockOut $row): array => $export->map($row))->values();

        $this->assertSame(
            ['Tanggal', 'No.Invoice', 'Cabang', 'Item', 'Jumlah', 'Jenis', 'Referensi', 'NIK', 'Alamat', 'Subtotal', 'Diskon', 'PPN', 'Total', 'Status', 'Pembayaran'],
            $export->headings(),
        );
        $this->assertCount(3, $mappedRows);

        foreach ($rows->values() as $index => $row) {
            $this->assertSame(str_pad((string) $row->id, 6, '0', STR_PAD_LEFT), $mappedRows[$index][1]);
            $this->assertSame('TAG Export', $mappedRows[$index][2]);
        }

        $summaryRows = $mappedRows->filter(fn (array $row): bool => $row[11] !== '-')->values();
        $this->assertCount(1, $summaryRows);
        $this->assertSame('Rp 29.700', $summaryRows[0][11]);
        $this->assertSame('Rp 299.700', $summaryRows[0][12]);
        $this->assertSame('1234567890123456', $summaryRows[0][7]);
        $this->assertSame('Palembang', $summaryRows[0][8]);
        $this->assertSame('Rp 100.000', $mappedRows->firstWhere(3, 'Umbrella Red')[9]);
        $this->assertSame('Rp 10.000', $mappedRows->firstWhere(3, 'Umbrella Red')[10]);
        $otherSaleRows = $mappedRows->filter(fn (array $row): bool => $row[5] === 'Penjualan' && $row[11] === '-')->values();
        $this->assertCount(1, $otherSaleRows);
        $this->assertSame('-', $otherSaleRows[0][12]);
        $requestRows = $mappedRows->filter(fn (array $row): bool => $row[5] === 'Request')->values();
        $requestRecord = $rows->first(fn (StockOut $row): bool => $row->jenis === 'request');
        $this->assertSame(['-', '-', '-', '-', '-', '-'], array_slice($requestRows[0], 7, 6));
        $this->assertSame(str_pad((string) $requestRecord->id, 6, '0', STR_PAD_LEFT), $requestRows[0][1]);
        $this->assertSame('TAG Export', $requestRows[0][2]);

        $filteredExport = new HistoryExport(['tipe' => 'out', 'item' => 'Umbrella Red']);
        $filteredRows = $filteredExport->collection();
        $this->assertCount(1, $filteredRows);
        $filteredSummary = $filteredExport->map($filteredRows->first());
        $this->assertSame('Rp 29.700', $filteredSummary[11]);
        $this->assertSame('Rp 299.700', $filteredSummary[12]);

        $excelContent = Excel::raw(new HistoryExport(['tipe' => 'out']), ExcelFormat::XLSX);
        $temporaryFile = tempnam(sys_get_temp_dir(), 'history-stock-out-export-');

        try {
            file_put_contents($temporaryFile, $excelContent);
            $spreadsheet = IOFactory::load($temporaryFile);
            $exportedRows = $spreadsheet->getActiveSheet()->toArray();

            $this->assertSame('No.Invoice', $exportedRows[0][1]);
            $this->assertSame('Cabang', $exportedRows[0][2]);
            $this->assertSame('Item', $exportedRows[0][3]);
            $this->assertSame('NIK', $exportedRows[0][7]);
            $this->assertSame('Alamat', $exportedRows[0][8]);
            $this->assertSame('Subtotal', $exportedRows[0][9]);
            $this->assertSame('Diskon', $exportedRows[0][10]);
            $this->assertSame('PPN', $exportedRows[0][11]);
            $this->assertSame('Total', $exportedRows[0][12]);
            $this->assertNotContains('DPP', $exportedRows[0]);
            $this->assertSame('Status', $exportedRows[0][13]);
            $this->assertSame('Pembayaran', $exportedRows[0][14]);
            $this->assertSame(1, count(array_filter(
                array_slice($exportedRows, 1),
                fn (array $row): bool => $row[11] === 'Rp 29.700',
            )));
        } finally {
            @unlink($temporaryFile);
        }

        $pdfResponse = $this->get(route('history.export', ['tipe' => 'out']));
        $pdfResponse->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());
    }

    public function test_items_and_stockin_excel_exports_download_xlsx_files(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin_ho',
            'cabang_id' => null,
        ]);

        foreach (['items.export.excel', 'stockin.export.excel'] as $routeName) {
            $response = $this->actingAs($admin)->get(route($routeName));

            $response->assertOk()
                ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
        }
    }

    public function test_staff_cabang_cannot_access_items_or_stockin_excel_exports(): void
    {
        $cabang = Cabang::create([
            'kode_cabang' => 'TAG-07',
            'nama_cabang' => 'TAG Cabang Export',
        ]);
        /** @var User $staff */
        $staff = User::factory()->create([
            'role' => 'staff_cabang',
            'cabang_id' => $cabang->id,
        ]);

        foreach (['items.export.excel', 'stockin.export.excel'] as $routeName) {
            $this->actingAs($staff)->get(route($routeName))->assertForbidden();
        }
    }

    public function test_items_and_stockin_index_show_export_choices_to_admin_only(): void
    {
        $cabang = Cabang::create([
            'kode_cabang' => 'TAG-08',
            'nama_cabang' => 'TAG Cabang Export View',
        ]);
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin_ho',
            'cabang_id' => null,
        ]);
        /** @var User $staff */
        $staff = User::factory()->create([
            'role' => 'staff_cabang',
            'cabang_id' => $cabang->id,
        ]);

        $this->actingAs($admin)->get(route('items.index'))
            ->assertOk()
            ->assertSee(route('items.export.excel'), false)
            ->assertSee('Pilih Format Ekspor');
        $this->get(route('stockin.index'))
            ->assertOk()
            ->assertSee(route('stockin.export.excel'), false)
            ->assertSee('Pilih Format Ekspor');

        $this->actingAs($staff)->get(route('items.index'))
            ->assertOk()
            ->assertDontSee(route('items.export.excel'), false)
            ->assertDontSee('Export Excel');
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

    public function test_history_export_matches_the_selected_summary_type_and_filters(): void
    {
        $cabang = Cabang::create([
            'kode_cabang' => 'TAG-06',
            'nama_cabang' => 'TAG Cabang Filter',
        ]);
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin_ho',
            'cabang_id' => null,
        ]);
        $items = collect([
            ['ITEM-IN-FILTER', 'Barang Masuk Export'],
            ['ITEM-SALE-FILTER', 'Penjualan Export'],
            ['ITEM-DO-FILTER', 'DO Export'],
            ['ITEM-APPROVAL-FILTER', 'Approval Export'],
        ])->mapWithKeys(fn (array $data): array => [
            $data[0] => Item::create([
                'kode_items' => $data[0],
                'nama_items' => $data[1],
                'kategori' => 'jacket',
                'harga_items' => 100000,
                'stok_items' => 5,
                'harga_jual' => 120000,
                'cabang_id' => $cabang->id,
            ]),
        ]);

        $stockIn = StockIn::create([
            'item_id' => $items['ITEM-IN-FILTER']->id,
            'cabang_id' => $cabang->id,
            'jumlah' => 2,
            'tanggal' => '2026-10-12',
            'sumber' => 'Input manual',
            'user_id' => $admin->id,
        ]);
        StockOut::create([
            'item_id' => $items['ITEM-SALE-FILTER']->id,
            'cabang_id' => $cabang->id,
            'jumlah' => 1,
            'jenis' => 'penjualan',
            'nama_customer' => 'Customer Filter',
            'status' => 'approved',
            'tanggal' => '2026-10-11',
            'user_id' => $admin->id,
        ]);
        $deliveryOrder = StockOut::create([
            'item_id' => $items['ITEM-DO-FILTER']->id,
            'cabang_id' => $cabang->id,
            'jumlah' => 1,
            'jenis' => 'DO',
            'nomor_spk' => 'SPK-FILTER',
            'status' => 'approved',
            'tanggal' => '2026-10-10',
            'user_id' => $admin->id,
        ]);
        $approval = StockOut::create([
            'item_id' => $items['ITEM-APPROVAL-FILTER']->id,
            'cabang_id' => $cabang->id,
            'jumlah' => 1,
            'jenis' => 'request',
            'status' => 'approved',
            'tanggal' => '2026-10-09',
            'user_id' => $admin->id,
            'approved_by' => $admin->id,
            'approved_at' => '2026-10-09 12:00:00',
        ]);
        StockInEditRequest::create([
            'stock_in_id' => $stockIn->id,
            'old_item_id' => $items['ITEM-IN-FILTER']->id,
            'new_item_id' => $items['ITEM-SALE-FILTER']->id,
            'old_jumlah' => 2,
            'new_jumlah' => 3,
            'old_tanggal' => '2026-10-12',
            'new_tanggal' => '2026-10-13',
            'requested_by' => $admin->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => '2026-10-13 12:00:00',
        ]);

        $this->actingAs($admin);
        $summary = new HistoryExport;
        $stockInExport = new HistoryExport(['tipe' => 'in']);
        $stockOutExport = new HistoryExport(['tipe' => 'out', 'jenis' => 'DO', 'item' => 'DO Export']);
        $approvalExport = new HistoryExport(['tipe' => 'approval', 'item' => 'Approval Export']);

        $this->assertCount(4, $summary->collection());
        $this->assertCount(4, (new HistoryExport(['jenis' => 'DO']))->collection());
        $this->assertCount(1, $stockInExport->collection());
        $this->assertSame(['Tanggal', 'Item', 'Cabang', 'Jumlah', 'Oleh'], $stockInExport->headings());
        $this->assertCount(1, $stockOutExport->collection());
        $this->assertSame('SPK-FILTER', $stockOutExport->map($deliveryOrder)[6]);
        $this->assertSame(['Tanggal', 'No.Invoice', 'Cabang', 'Item', 'Jumlah', 'Jenis', 'Referensi', 'NIK', 'Alamat', 'Subtotal', 'Diskon', 'PPN', 'Total', 'Status', 'Pembayaran'], $stockOutExport->headings());
        $this->assertSame(str_pad((string) $deliveryOrder->id, 6, '0', STR_PAD_LEFT), $stockOutExport->map($deliveryOrder)[1]);
        $this->assertSame($cabang->nama_cabang, $stockOutExport->map($deliveryOrder)[2]);
        $this->assertSame(['-', '-', '-', '-', '-', '-'], array_slice($stockOutExport->map($deliveryOrder), 7, 6));
        $this->assertCount(1, $approvalExport->collection());
        $this->assertSame('Approval Transaksi', $approvalExport->map($approval)[4]);
        $this->assertSame('Edit Stok', $approvalExport->map(StockInEditRequest::first())[4]);
        $this->assertSame('Tipe', $approvalExport->headings()[4]);
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

    public function test_history_export_links_keep_the_selected_filters(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin_ho',
            'cabang_id' => null,
        ]);
        $filters = [
            'tipe' => 'out',
            'jenis' => 'DO',
            'item' => 'Umbrella',
            'dari' => '2026-10-01',
            'sampai' => '2026-10-16',
        ];

        $this->actingAs($admin)->get(route('history.index', $filters))
            ->assertOk()
            ->assertSee(e(route('history.export', $filters)), false)
            ->assertSee(e(route('history.export.excel', $filters)), false);
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
