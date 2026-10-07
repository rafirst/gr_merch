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

    public function test_stock_out_history_shows_payment_code_instead_of_the_creator_name(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $stockOut = $this->createStockOut($cabang, $admin);
        $stockOut->update(['kode_pembayaran' => 'BAY-2026-000123']);

        $this->actingAs($admin)->get(route('history.index', ['tipe' => 'out']))
            ->assertOk()
            ->assertViewIs('history.index')
            ->assertSee('Kode Pembayaran')
            ->assertSee('BAY-2026-000123')
            ->assertDontSee('<th>Oleh</th>', false);
    }

    public function test_stock_out_history_renders_a_placeholder_when_payment_code_is_empty(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $this->createStockOut($cabang, $admin, 'DO');

        $this->actingAs($admin)->get(route('history.index', ['tipe' => 'out']))
            ->assertOk()
            ->assertSee('history-payment-code-empty', false);
    }

    public function test_stock_out_history_respects_the_branch_scope(): void
    {
        $ownCabang = $this->createCabang('C01');
        $otherCabang = $this->createCabang('C02');
        $admin = $this->createUser('admin_ho', null);
        $staff = $this->createUser('staff_cabang', $ownCabang->id);
        $visible = $this->createStockOut($ownCabang, $admin);
        $visible->update(['kode_pembayaran' => 'BAY-VISIBLE']);
        $hidden = $this->createStockOut($otherCabang, $admin);
        $hidden->update(['kode_pembayaran' => 'BAY-HIDDEN']);

        $this->actingAs($staff)->get(route('history.index', ['tipe' => 'out']))
            ->assertOk()
            ->assertSee('BAY-VISIBLE')
            ->assertDontSee('BAY-HIDDEN');
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
