<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Item;
use App\Models\StockOut;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StockOutEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_button_is_rendered_for_pending_and_approved_but_not_rejected(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $approved = $this->createStockOut($cabang, $admin, 'approved');
        $pending = $this->createStockOut($cabang, $admin, 'pending');
        $rejected = $this->createStockOut($cabang, $admin, 'rejected');

        $this->actingAs($admin)->get(route('stockout.index'))
            ->assertOk()
            ->assertSee(route('stockout.edit', $approved), false)
            ->assertSee(route('stockout.edit', $pending), false)
            ->assertDontSee(route('stockout.edit', $rejected), false);
    }

    public function test_edit_page_loads_with_every_row_of_the_batch(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $first = $this->createStockOut($cabang, $admin, 'approved', 'batch-edit-1');
        $second = $this->createStockOut($cabang, $admin, 'approved', 'batch-edit-1');

        $this->actingAs($admin)->get(route('stockout.edit', $first))
            ->assertOk()
            ->assertViewIs('stockout.edit')
            ->assertSee('value="'.$first->item_id.'"', false)
            ->assertSee('value="'.$second->item_id.'"', false);
    }

    public function test_edit_page_is_forbidden_for_rejected_transactions(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $stockOut = $this->createStockOut($cabang, $admin, 'rejected');

        $this->actingAs($admin)->get(route('stockout.edit', $stockOut))->assertForbidden();
    }

    public function test_staff_cannot_open_edit_page_for_another_branch(): void
    {
        $otherCabang = $this->createCabang('C02');
        $ownCabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $staff = $this->createUser('staff_cabang', $ownCabang->id);
        $stockOut = $this->createStockOut($otherCabang, $admin, 'approved');

        $this->actingAs($staff)->get(route('stockout.edit', $stockOut))->assertForbidden();
    }

    public function test_updating_quantity_moves_stock_accordingly(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $item = $this->createItem($cabang, 10);
        $stockOut = $this->createStockOut($cabang, $admin, 'approved', null, $item, 2);

        // Stok 10 sudah terpotong 2 saat pencatatan, sehingga sisa riil 8.
        $this->assertSame(8, $item->fresh()->stok_items);

        $this->actingAs($admin)->put(route('stockout.update', $stockOut), [
            'item_id' => [$item->id],
            'jumlah' => [4],
        ])->assertRedirect(route('stockout.index'));

        $this->assertSame(6, $item->fresh()->stok_items);
        $this->assertSame(4, $stockOut->fresh()->jumlah);
    }

    public function test_updating_item_returns_stock_to_the_previous_item(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $oldItem = $this->createItem($cabang, 10);
        $newItem = $this->createItem($cabang, 10);
        $stockOut = $this->createStockOut($cabang, $admin, 'approved', null, $oldItem, 3);

        $this->actingAs($admin)->put(route('stockout.update', $stockOut), [
            'item_id' => [$newItem->id],
            'jumlah' => [2],
        ])->assertRedirect(route('stockout.index'));

        $this->assertSame(10, $oldItem->fresh()->stok_items);
        $this->assertSame(8, $newItem->fresh()->stok_items);
        $this->assertSame($newItem->id, $stockOut->fresh()->item_id);
    }

    public function test_update_rejects_quantity_above_available_stock(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $item = $this->createItem($cabang, 10);
        $stockOut = $this->createStockOut($cabang, $admin, 'approved', null, $item, 2);

        $this->actingAs($admin)->put(route('stockout.update', $stockOut), [
            'item_id' => [$item->id],
            'jumlah' => [99],
        ])->assertSessionHasErrors('jumlah');

        $this->assertSame(8, $item->fresh()->stok_items);
        $this->assertSame(2, $stockOut->fresh()->jumlah);
    }

    public function test_updating_pending_transaction_does_not_touch_stock(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $item = $this->createItem($cabang, 10);
        $stockOut = $this->createStockOut($cabang, $admin, 'pending', null, $item, 2);

        $this->actingAs($admin)->put(route('stockout.update', $stockOut), [
            'item_id' => [$item->id],
            'jumlah' => [5],
        ])->assertRedirect(route('stockout.index'));

        // Transaksi pending belum memotong stok sehingga stok tetap utuh.
        $this->assertSame(10, $item->fresh()->stok_items);
        $this->assertSame(5, $stockOut->fresh()->jumlah);
        $this->assertSame('pending', $stockOut->fresh()->status);
    }

    public function test_update_keeps_every_row_of_the_batch_consistent(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $firstItem = $this->createItem($cabang, 10);
        $secondItem = $this->createItem($cabang, 10);
        $first = $this->createStockOut($cabang, $admin, 'approved', 'batch-keep', $firstItem, 1);
        $second = $this->createStockOut($cabang, $admin, 'approved', 'batch-keep', $secondItem, 1);
        $third = $this->createStockOut($cabang, $admin, 'approved', 'batch-keep', $secondItem, 1);

        $this->actingAs($admin)->put(route('stockout.update', $first), [
            'item_id' => [$firstItem->id, $secondItem->id],
            'jumlah' => [2, 1],
        ])->assertRedirect(route('stockout.index'));

        $this->assertSame(2, $first->fresh()->jumlah);
        $this->assertSame(1, $second->fresh()->jumlah);
        $this->assertNull($third->fresh());
        $this->assertSame(2, StockOut::where('batch_id', 'batch-keep')->count());
        $this->assertSame(8, $firstItem->fresh()->stok_items);
        $this->assertSame(9, $secondItem->fresh()->stok_items);
    }

    public function test_update_can_add_a_row_to_an_existing_batch(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $firstItem = $this->createItem($cabang, 10);
        $secondItem = $this->createItem($cabang, 10);
        $stockOut = $this->createStockOut($cabang, $admin, 'approved', 'batch-add', $firstItem, 1);

        $this->actingAs($admin)->put(route('stockout.update', $stockOut), [
            'item_id' => [$firstItem->id, $secondItem->id],
            'jumlah' => [1, 2],
        ])->assertRedirect(route('stockout.index'));

        $rows = StockOut::where('batch_id', 'batch-add')->orderBy('id')->get();
        $this->assertCount(2, $rows);
        $this->assertSame($firstItem->id, $rows[0]->item_id);
        $this->assertSame($secondItem->id, $rows[1]->item_id);
        $this->assertSame(8, $secondItem->fresh()->stok_items);
    }

    public function test_update_preserves_payment_code_and_proof(): void
    {
        Storage::fake('public');
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $item = $this->createItem($cabang, 10);
        $stockOut = $this->createStockOut($cabang, $admin, 'approved', null, $item, 1);
        $proofPath = UploadedFile::fake()->create('bukti.pdf', 80, 'application/pdf')->store('stockout-payment-proof', 'public');
        $stockOut->update(['kode_pembayaran' => 'BAY-KEEP', 'bukti_pembayaran' => $proofPath]);

        $this->actingAs($admin)->put(route('stockout.update', $stockOut), [
            'item_id' => [$item->id],
            'jumlah' => [3],
        ])->assertRedirect(route('stockout.index'));

        $stockOut->refresh();
        $this->assertSame('BAY-KEEP', $stockOut->kode_pembayaran);
        $this->assertSame($proofPath, $stockOut->bukti_pembayaran);
    }

    public function test_staff_cannot_update_transaction_from_another_branch(): void
    {
        $otherCabang = $this->createCabang('C02');
        $ownCabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $staff = $this->createUser('staff_cabang', $ownCabang->id);
        $item = $this->createItem($otherCabang, 10);
        $stockOut = $this->createStockOut($otherCabang, $admin, 'approved', null, $item, 1);

        $this->actingAs($staff)->put(route('stockout.update', $stockOut), [
            'item_id' => [$item->id],
            'jumlah' => [2],
        ])->assertForbidden();

        $this->assertSame(1, $stockOut->fresh()->jumlah);
    }

    public function test_rejected_transaction_cannot_be_updated(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $item = $this->createItem($cabang, 10);
        $stockOut = $this->createStockOut($cabang, $admin, 'rejected', null, $item, 1);

        $this->actingAs($admin)->put(route('stockout.update', $stockOut), [
            'item_id' => [$item->id],
            'jumlah' => [3],
        ])->assertForbidden();

        $this->assertSame(1, $stockOut->fresh()->jumlah);
        $this->assertSame(10, $item->fresh()->stok_items);
    }

    public function test_payment_proof_can_be_saved_from_the_edit_page(): void
    {
        Storage::fake('public');
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $stockOut = $this->createStockOut($cabang, $admin, 'approved');

        $this->actingAs($admin)->get(route('stockout.edit', $stockOut))
            ->assertOk()
            ->assertSee('name="_redirect_to" value="edit"', false)
            ->assertSee('id="bukti-payment"', false);

        $this->actingAs($admin)->post(route('stockout.bukti-pembayaran.update', $stockOut), [
            'kode_pembayaran' => 'BAY-EDIT-1',
            'bukti_pembayaran' => UploadedFile::fake()->create('bukti.pdf', 90, 'application/pdf'),
            '_redirect_to' => 'edit',
        ])->assertRedirect(route('stockout.index'));

        $this->assertSame('BAY-EDIT-1', $stockOut->fresh()->kode_pembayaran);
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

    private function createItem(Cabang $cabang, int $stock): Item
    {
        return Item::create([
            'kode_items' => 'ITEM-'.uniqid(),
            'nama_items' => 'Test Item',
            'kategori' => 'jacket',
            'harga_items' => 100000,
            'stok_items' => $stock,
            'harga_jual' => 120000,
            'cabang_id' => $cabang->id,
        ]);
    }

    private function createStockOut(Cabang $cabang, User $creator, string $status, ?string $batchId = null, ?Item $item = null, int $jumlah = 1): StockOut
    {
        $item ??= $this->createItem($cabang, 10);

        return StockOut::create([
            'item_id' => $item->id,
            'cabang_id' => $cabang->id,
            'jumlah' => $jumlah,
            'jenis' => 'DO',
            'harga_jual' => null,
            'discount' => 0,
            'total' => null,
            'status' => $status,
            'tanggal' => now()->toDateString(),
            'batch_id' => $batchId,
            'user_id' => $creator->id,
            'approved_by' => $status === 'approved' ? $creator->id : null,
            'approved_at' => $status === 'approved' ? now() : null,
        ]);
    }
}
