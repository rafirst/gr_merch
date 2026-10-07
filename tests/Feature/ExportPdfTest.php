<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Item;
use App\Models\StockOut;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
