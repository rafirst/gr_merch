<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Item;
use App\Models\TransferBarang;
use App\Models\TransferBarangItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransferBarangTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dispatch_reduces_source_stock_and_creates_transfer_in_process(): void
    {
        $source = $this->createCabang('SRC');
        $destination = $this->createCabang('DST');
        $admin = $this->createUser('admin_ho', null);
        $item = $this->createItem($source, 12);

        $response = $this->actingAs($admin)->post(route('transfers.store'), [
            'from_cabang_id' => $source->id,
            'to_cabang_id' => $destination->id,
            'items' => [
                ['item_id' => $item->id, 'jumlah_dikirim' => 5],
            ],
        ]);

        $transfer = TransferBarang::with('items')->firstOrFail();
        $response->assertRedirect(route('transfers.index'));
        $this->assertSame(TransferBarang::STATUS_PROSES, $transfer->status);
        $this->assertSame(7, $item->fresh()->stok_items);
        $this->assertSame(5, $transfer->items->first()->jumlah_dikirim);
    }

    public function test_staff_receipt_rejects_an_unnoted_discrepancy_and_adds_actual_quantity(): void
    {
        [$transfer, $line, $destination, $sourceItem] = $this->createPendingTransfer();
        $staff = $this->createUser('staff_cabang', $destination->id);

        $this->actingAs($staff)->post(route('transfers.receive', $transfer), [
            'items' => [$line->id => ['jumlah_diterima' => 3]],
        ])->assertSessionHasErrors("items.{$line->id}.catatan_selisih");

        $this->get(route('transfers.index'))
            ->assertOk()
            ->assertSee('TRF-'.str_pad((string) $transfer->id, 5, '0', STR_PAD_LEFT))
            ->assertSee('Menunggu')
            ->assertSee('transfer-staff-receive')
            ->assertSee('transfer-staff-detail');

        $this->post(route('transfers.receive', $transfer), [
            'items' => [$line->id => ['jumlah_diterima' => 3, 'catatan_selisih' => 'Satu unit rusak']],
        ])->assertRedirect(route('transfers.index', ['tab' => 'received']));

        $this->get(route('transfers.index'))
            ->assertOk()
            ->assertSee('Diterima')
            ->assertSee('transfer-staff-detail')
            ->assertSee('fa-eye');

        $targetItem = Item::where('kode_items', $sourceItem->kode_items)
            ->where('cabang_id', $destination->id)
            ->firstOrFail();
        $this->assertSame(3, $targetItem->stok_items);
        $this->assertSame(TransferBarang::STATUS_DITERIMA, $transfer->fresh()->status);
        $this->assertSame(3, $line->fresh()->jumlah_diterima);
        $this->assertSame('Satu unit rusak', $line->fresh()->catatan_selisih);
    }

    public function test_staff_can_view_transfer_details_only_for_their_destination_branch(): void
    {
        [$transfer, , $destination] = $this->createPendingTransfer();
        $staff = $this->createUser('staff_cabang', $destination->id);
        $otherBranch = $this->createCabang('OTH');
        $otherStaff = $this->createUser('staff_cabang', $otherBranch->id);

        $this->actingAs($staff)->get(route('transfers.show', $transfer))
            ->assertOk()
            ->assertViewIs('transfers.show')
            ->assertDontSee('transfer-detail-edit');

        $this->actingAs($otherStaff)->get(route('transfers.show', $transfer))->assertForbidden();
    }

    public function test_staff_cannot_create_or_receive_transfers_for_another_branch(): void
    {
        [$transfer, $line, $destination] = $this->createPendingTransfer();
        $otherBranch = $this->createCabang('OTH');
        $staff = $this->createUser('staff_cabang', $otherBranch->id);

        $this->actingAs($staff)->post(route('transfers.store'), [
            'from_cabang_id' => $transfer->from_cabang_id,
            'to_cabang_id' => $destination->id,
            'items' => [['item_id' => $line->source_item_id, 'jumlah_dikirim' => 1]],
        ])->assertForbidden();

        $this->post(route('transfers.receive', $transfer), [
            'items' => [$line->id => ['jumlah_diterima' => 4, 'catatan_selisih' => '']],
        ])->assertForbidden();
    }

    public function test_admin_can_view_and_update_a_transfer_in_process(): void
    {
        [$transfer, , , $sourceItem] = $this->createPendingTransfer();
        $admin = User::where('role', 'admin_ho')->firstOrFail();

        $this->actingAs($admin)->get(route('transfers.show', $transfer))
            ->assertOk()
            ->assertViewIs('transfers.show');
        $this->get(route('transfers.edit', $transfer))
            ->assertOk()
            ->assertViewIs('transfers.edit');

        $this->put(route('transfers.update', $transfer), [
            'from_cabang_id' => $transfer->from_cabang_id,
            'to_cabang_id' => $transfer->to_cabang_id,
            'items' => [['item_id' => $sourceItem->id, 'jumlah_dikirim' => 6]],
        ])->assertRedirect(route('transfers.index'));

        $this->assertSame(4, $sourceItem->fresh()->stok_items);
        $this->assertSame(6, $transfer->fresh()->items()->firstOrFail()->jumlah_dikirim);
        $this->assertSame(TransferBarang::STATUS_PROSES, $transfer->fresh()->status);
    }

    public function test_admin_cancelling_a_transfer_in_process_restores_source_stock(): void
    {
        [$transfer, , , $sourceItem] = $this->createPendingTransfer();
        $admin = User::where('role', 'admin_ho')->firstOrFail();

        $this->actingAs($admin)->delete(route('transfers.destroy', $transfer))
            ->assertRedirect(route('transfers.index'));

        $this->assertSame(10, $sourceItem->fresh()->stok_items);
        $this->assertSame(TransferBarang::STATUS_BATAL, $transfer->fresh()->status);
    }

    public function test_admin_cancelling_an_accepted_transfer_reverses_both_branch_stocks(): void
    {
        [$transfer, $line, $destination, $sourceItem] = $this->createPendingTransfer();
        $staff = $this->createUser('staff_cabang', $destination->id);
        $admin = User::where('role', 'admin_ho')->firstOrFail();

        $this->actingAs($staff)->post(route('transfers.receive', $transfer), [
            'items' => [$line->id => ['jumlah_diterima' => 3, 'catatan_selisih' => 'Selisih']],
        ])->assertRedirect(route('transfers.index', ['tab' => 'received']));

        $targetItem = Item::where('kode_items', $sourceItem->kode_items)
            ->where('cabang_id', $destination->id)
            ->firstOrFail();

        $this->actingAs($admin)->delete(route('transfers.destroy', $transfer))
            ->assertRedirect(route('transfers.index'));

        $this->assertSame(10, $sourceItem->fresh()->stok_items);
        $this->assertSame(0, $targetItem->fresh()->stok_items);
        $this->assertSame(TransferBarang::STATUS_BATAL, $transfer->fresh()->status);
    }

    public function test_admin_can_upload_proof_photos_when_creating_a_transfer(): void
    {
        Storage::fake('public');
        $source = $this->createCabang('SRC');
        $destination = $this->createCabang('DST');
        $admin = $this->createUser('admin_ho', null);
        $item = $this->createItem($source, 12);
        $photo = UploadedFile::fake()->image('proof.jpg');

        $this->actingAs($admin)->post(route('transfers.store'), [
            'from_cabang_id' => $source->id,
            'to_cabang_id' => $destination->id,
            'items' => [['item_id' => $item->id, 'jumlah_dikirim' => 5]],
            'bukti_foto' => [$photo],
        ])->assertRedirect(route('transfers.index'));

        $transfer = TransferBarang::firstOrFail();
        $this->assertCount(1, $transfer->bukti_foto);
        $this->assertTrue(Storage::disk('public')->exists($transfer->bukti_foto[0]));
    }

    public function test_admin_can_replace_proof_photos_when_editing_a_transfer(): void
    {
        Storage::fake('public');
        [$transfer, , , $sourceItem] = $this->createPendingTransfer();
        $admin = User::where('role', 'admin_ho')->firstOrFail();
        $oldPhotoPath = UploadedFile::fake()->image('old-proof.jpg')->store('transfer-proofs', 'public');
        $transfer->update(['bukti_foto' => [$oldPhotoPath]]);
        $newPhoto = UploadedFile::fake()->image('new-proof.jpg');

        $this->actingAs($admin)->put(route('transfers.update', $transfer), [
            'from_cabang_id' => $transfer->from_cabang_id,
            'to_cabang_id' => $transfer->to_cabang_id,
            'items' => [['item_id' => $sourceItem->id, 'jumlah_dikirim' => 4]],
            'hapus_bukti_foto' => [$oldPhotoPath],
            'bukti_foto' => [$newPhoto],
        ])->assertRedirect(route('transfers.index'));

        $transfer->refresh();
        $this->assertCount(1, $transfer->bukti_foto);
        $this->assertNotSame($oldPhotoPath, $transfer->bukti_foto[0]);
        $this->assertFalse(Storage::disk('public')->exists($oldPhotoPath));
        $this->assertTrue(Storage::disk('public')->exists($transfer->bukti_foto[0]));
    }

    private function createPendingTransfer(): array
    {
        $source = $this->createCabang('SRC');
        $destination = $this->createCabang('DST');
        $admin = $this->createUser('admin_ho', null);
        $item = $this->createItem($source, 10);

        $this->actingAs($admin)->post(route('transfers.store'), [
            'from_cabang_id' => $source->id,
            'to_cabang_id' => $destination->id,
            'items' => [['item_id' => $item->id, 'jumlah_dikirim' => 4]],
        ])->assertRedirect(route('transfers.index'));

        $transfer = TransferBarang::firstOrFail();

        return [$transfer, TransferBarangItem::firstOrFail(), $destination, $item];
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
            'kode_items' => 'ITEM-001',
            'nama_items' => 'Test Item',
            'kategori' => 'jacket',
            'harga_items' => 100000,
            'stok_items' => $stock,
            'harga_jual' => 120000,
            'cabang_id' => $cabang->id,
        ]);
    }
}
