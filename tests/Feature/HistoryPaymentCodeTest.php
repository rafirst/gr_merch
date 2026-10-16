<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Item;
use App\Models\StockOut;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryPaymentCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_out_history_shows_reference_based_on_transaction_type(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $sale = $this->createStockOut($cabang, $admin);
        $sale->update(['nama_customer' => 'Customer Histori']);
        $deliveryOrder = $this->createStockOut($cabang, $admin, 'DO');
        $deliveryOrder->update(['nomor_spk' => 'SPK-HIST-123']);
        $request = $this->createStockOut($cabang, $admin, 'request');
        $request->update(['nomor_im' => 'IM-HIST-456']);

        $this->actingAs($admin)->get(route('history.index', ['tipe' => 'out']))
            ->assertOk()
            ->assertViewIs('history.index')
            ->assertSee('Referensi')
            ->assertSee('Customer Histori')
            ->assertSee('SPK-HIST-123')
            ->assertSee('IM-HIST-456')
            ->assertDontSee('<th>Oleh</th>', false);
    }

    public function test_stock_out_history_shows_placeholder_when_reference_is_empty(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $this->createStockOut($cabang, $admin, 'DO');

        $this->actingAs($admin)->get(route('history.index', ['tipe' => 'out']))
            ->assertOk()
            ->assertSee('<span class="history-reference">', false)
            ->assertSee('>-</span>', false);
    }

    public function test_stock_out_history_respects_the_branch_scope(): void
    {
        $ownCabang = $this->createCabang('C01');
        $otherCabang = $this->createCabang('C02');
        $admin = $this->createUser('admin_ho', null);
        $staff = $this->createUser('staff_cabang', $ownCabang->id);
        $visible = $this->createStockOut($ownCabang, $admin);
        $visible->update(['nama_customer' => 'Customer Terlihat']);
        $hidden = $this->createStockOut($otherCabang, $admin);
        $hidden->update(['nama_customer' => 'Customer Tersembunyi']);

        $this->actingAs($staff)->get(route('history.index', ['tipe' => 'out']))
            ->assertOk()
            ->assertSee('Customer Terlihat')
            ->assertDontSee('Customer Tersembunyi');
    }

    private function createCabang(string $code): Cabang
    {
        return Cabang::create([
            'kode_cabang' => $code,
            'nama_cabang' => 'Cabang '.$code,
        ]);
    }

    private function createUser(string $role, ?int $cabangId): User
    {
        return User::create([
            'name' => ucfirst($role),
            'username' => $role.'-'.uniqid(),
            'password' => 'password',
            'role' => $role,
            'cabang_id' => $cabangId,
        ]);
    }

    private function createStockOut(Cabang $cabang, User $creator, string $jenis = 'penjualan'): StockOut
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
            'jenis' => $jenis,
            'jenis_pembayaran' => 'transfer',
            'harga_jual' => 120000,
            'discount' => 0,
            'total' => 120000,
            'status' => 'approved',
            'tanggal' => now()->toDateString(),
            'user_id' => $creator->id,
            'approved_by' => $creator->id,
            'approved_at' => now(),
        ]);
    }
}
