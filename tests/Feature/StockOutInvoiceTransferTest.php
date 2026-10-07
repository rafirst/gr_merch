<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Item;
use App\Models\StockOut;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOutInvoiceTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_shows_transfer_account_of_the_transaction_branch(): void
    {
        $cabang = $this->createCabang('PLG');
        $admin = $this->createAdmin();
        $stockOut = $this->createStockOut($cabang, $admin, 'transfer');

        $this->actingAs($admin)->get(route('stockout.invoice', $stockOut))
            ->assertOk()
            ->assertSee('Silahkan transfer bank melalui:', false)
            ->assertSee('BCA 0216198888', false)
            ->assertSee('a.n Tunas Auto Graha', false);
    }

    public function test_invoice_uses_the_account_matching_the_transaction_branch(): void
    {
        $cabang = $this->createCabang('LLG');
        $admin = $this->createAdmin();
        $stockOut = $this->createStockOut($cabang, $admin, 'transfer');

        $this->actingAs($admin)->get(route('stockout.invoice', $stockOut))
            ->assertOk()
            ->assertSee('BCA 0217817777', false)
            ->assertDontSee('BCA 0216198888', false);
    }

    public function test_invoice_hides_transfer_info_for_qris_payments(): void
    {
        $cabang = $this->createCabang('PLG');
        $admin = $this->createAdmin();
        $stockOut = $this->createStockOut($cabang, $admin, 'qris');

        $this->actingAs($admin)->get(route('stockout.invoice', $stockOut))
            ->assertOk()
            ->assertDontSee('Silahkan transfer bank melalui:', false)
            ->assertDontSee('BCA 0216198888', false);
    }

    public function test_invoice_hides_transfer_info_when_branch_has_no_account(): void
    {
        $cabang = $this->createCabang('XXX');
        $admin = $this->createAdmin();
        $stockOut = $this->createStockOut($cabang, $admin, 'transfer');

        $this->actingAs($admin)->get(route('stockout.invoice', $stockOut))
            ->assertOk()
            ->assertDontSee('Silahkan transfer bank melalui:', false);
    }

    private function createCabang(string $code): Cabang
    {
        return Cabang::create([
            'kode_cabang' => $code,
            'nama_cabang' => 'Cabang '.$code,
        ]);
    }

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Administrator',
            'username' => 'admin-'.uniqid(),
            'password' => 'password',
            'role' => 'admin_ho',
            'cabang_id' => null,
        ]);
    }

    private function createStockOut(Cabang $cabang, User $creator, string $jenisPembayaran): StockOut
    {
        $item = Item::create([
            'kode_items' => 'ITEM-'.uniqid(),
            'nama_items' => 'Test Item',
            'kategori' => 'jacket',
            'harga_items' => 100000,
            'stok_items' => 10,
            'harga_jual' => 120000,
            'cabang_id' => $cabang->id,
        ]);

        return StockOut::create([
            'item_id' => $item->id,
            'cabang_id' => $cabang->id,
            'jumlah' => 1,
            'jenis' => 'penjualan',
            'jenis_pembayaran' => $jenisPembayaran,
            'nama_customer' => 'Customer Test',
            'harga_jual' => 120000,
            'discount' => 10,
            'total' => 108000,
            'status' => 'approved',
            'tanggal' => now()->toDateString(),
            'user_id' => $creator->id,
            'approved_by' => $creator->id,
            'approved_at' => now(),
        ]);
    }
}
