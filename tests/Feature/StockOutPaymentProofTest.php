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

class StockOutPaymentProofTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_button_only_renders_for_approved_transactions(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $approved = $this->createStockOut($cabang, $admin, 'approved');
        $pending = $this->createStockOut($cabang, $admin, 'pending');

        $this->actingAs($admin)->get(route('stockout.index'))
            ->assertOk()
            ->assertViewIs('stockout.index')
            ->assertSee('data-target="#paymentModal-'.$approved->id.'"', false)
            ->assertSee('id="paymentModal-'.$approved->id.'"', false)
            ->assertDontSee('paymentModal-'.$pending->id, false);
    }

    public function test_payment_proof_and_code_are_saved_for_approved_transactions(): void
    {
        Storage::fake('public');
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $stockOut = $this->createStockOut($cabang, $admin, 'approved');

        $this->actingAs($admin)->post(route('stockout.bukti-pembayaran.update', $stockOut), [
            '_stockout_id' => $stockOut->id,
            'kode_pembayaran' => 'BAY-2026-000123',
            'bukti_pembayaran' => UploadedFile::fake()->create('bukti.pdf', 120, 'application/pdf'),
        ])->assertRedirect(route('stockout.index'));

        $stockOut->refresh();
        $this->assertSame('BAY-2026-000123', $stockOut->kode_pembayaran);
        $this->assertNotNull($stockOut->bukti_pembayaran);
        $this->assertTrue(Storage::disk('public')->exists($stockOut->bukti_pembayaran));
    }

    public function test_payment_proof_is_shared_across_all_rows_of_the_same_batch(): void
    {
        Storage::fake('public');
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $batchId = 'batch-1';
        $first = $this->createStockOut($cabang, $admin, 'approved', $batchId);
        $second = $this->createStockOut($cabang, $admin, 'approved', $batchId);

        $this->actingAs($admin)->post(route('stockout.bukti-pembayaran.update', $first), [
            'kode_pembayaran' => 'BAY-BATCH-1',
            'bukti_pembayaran' => UploadedFile::fake()->create('bukti.pdf', 80, 'application/pdf'),
        ])->assertRedirect(route('stockout.index'));

        $this->assertSame('BAY-BATCH-1', $first->fresh()->kode_pembayaran);
        $this->assertSame($first->fresh()->bukti_pembayaran, $second->fresh()->bukti_pembayaran);
    }

    public function test_replacing_the_proof_deletes_the_previous_file(): void
    {
        Storage::fake('public');
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $stockOut = $this->createStockOut($cabang, $admin, 'approved');
        $oldPath = UploadedFile::fake()->create('old.pdf', 80, 'application/pdf')->store('stockout-payment-proof', 'public');
        $stockOut->update(['kode_pembayaran' => 'BAY-OLD', 'bukti_pembayaran' => $oldPath]);

        $this->actingAs($admin)->post(route('stockout.bukti-pembayaran.update', $stockOut), [
            'kode_pembayaran' => 'BAY-NEW',
            'bukti_pembayaran' => UploadedFile::fake()->create('new.pdf', 80, 'application/pdf'),
        ])->assertRedirect(route('stockout.index'));

        $stockOut->refresh();
        $this->assertSame('BAY-NEW', $stockOut->kode_pembayaran);
        $this->assertNotSame($oldPath, $stockOut->bukti_pembayaran);
        $this->assertFalse(Storage::disk('public')->exists($oldPath));
        $this->assertTrue(Storage::disk('public')->exists($stockOut->bukti_pembayaran));
    }

    public function test_payment_modal_shows_edit_button_when_proof_exists(): void
    {
        Storage::fake('public');
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $stockOut = $this->createStockOut($cabang, $admin, 'approved');
        $proofPath = UploadedFile::fake()->create('bukti.pdf', 80, 'application/pdf')->store('stockout-payment-proof', 'public');
        $stockOut->update(['kode_pembayaran' => 'BAY-2026-000123', 'bukti_pembayaran' => $proofPath]);

        $response = $this->actingAs($admin)->get(route('stockout.index'));

        $response->assertOk()
            ->assertSee('id="paymentModal-'.$stockOut->id.'"', false)
            ->assertSee('Simpan Bukti', false)
            ->assertSee(route('stockout.edit', $stockOut), false)
            ->assertSee('BAY-2026-000123', false);
    }

    public function test_payment_modal_hides_edit_button_without_complete_proof(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $this->createStockOut($cabang, $admin, 'approved');

        $this->actingAs($admin)->get(route('stockout.index'))
            ->assertOk()
            ->assertSee('Simpan Bukti', false)
            ->assertDontSee('stockout-modal-edit-button', false);
    }

    public function test_show_page_lists_payment_code_and_proof(): void
    {
        Storage::fake('public');
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $stockOut = $this->createStockOut($cabang, $admin, 'approved');
        $proofPath = UploadedFile::fake()->create('bukti.pdf', 80, 'application/pdf')->store('stockout-payment-proof', 'public');
        $stockOut->update(['kode_pembayaran' => 'BAY-2026-000123', 'bukti_pembayaran' => $proofPath]);

        $this->actingAs($admin)->get(route('stockout.show', $stockOut))
            ->assertOk()
            ->assertViewIs('stockout.show')
            ->assertSee('Payment')
            ->assertSee('Kode Pembayaran')
            ->assertSee('Bukti Pembayaran')
            ->assertSee('BAY-2026-000123')
            ->assertSee(Storage::disk('public')->url($proofPath), false);
    }

    public function test_show_page_shows_placeholder_when_payment_is_missing(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $stockOut = $this->createStockOut($cabang, $admin, 'approved');

        $this->actingAs($admin)->get(route('stockout.show', $stockOut))
            ->assertOk()
            ->assertSee('Payment')
            ->assertDontSee('Lihat Bukti Pembayaran');
    }

    public function test_invoice_button_only_renders_for_approved_transactions(): void
    {
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $approved = $this->createStockOut($cabang, $admin, 'approved');
        $pending = $this->createStockOut($cabang, $admin, 'pending');

        $this->actingAs($admin)->get(route('stockout.show', $approved))
            ->assertOk()
            ->assertSee('href="'.route('stockout.invoice', $approved).'"', false);

        $this->actingAs($admin)->get(route('stockout.show', $pending))
            ->assertOk()
            ->assertDontSee(route('stockout.invoice', $pending), false);
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Storage::fake('public');
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $stockOut = $this->createStockOut($cabang, $admin, 'approved');

        $this->actingAs($admin)->post(route('stockout.bukti-pembayaran.update', $stockOut), [
            'kode_pembayaran' => 'BAY-2026-000123',
            'bukti_pembayaran' => UploadedFile::fake()->create('bukti.txt', 80, 'text/plain'),
        ])->assertSessionHasErrors('bukti_pembayaran');

        $this->assertNull($stockOut->fresh()->bukti_pembayaran);
    }

    public function test_non_pdf_payment_proof_is_rejected(): void {}

    public function test_image_upload_is_rejected(): void
    {
        Storage::fake('public');
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $stockOut = $this->createStockOut($cabang, $admin, 'approved');

        $this->actingAs($admin)->post(route('stockout.bukti-pembayaran.update', $stockOut), [
            'kode_pembayaran' => 'BAY-2026-000123',
            'bukti_pembayaran' => UploadedFile::fake()->create('bukti.png', 120, 'image/png'),
        ])->assertSessionHasErrors('bukti_pembayaran');

        $this->assertNull($stockOut->fresh()->bukti_pembayaran);
    }

    public function test_payment_code_is_required(): void
    {
        Storage::fake('public');
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $stockOut = $this->createStockOut($cabang, $admin, 'approved');

        $this->actingAs($admin)->post(route('stockout.bukti-pembayaran.update', $stockOut), [
            'kode_pembayaran' => '',
        ])->assertSessionHasErrors('kode_pembayaran');
    }

    public function test_payment_proof_is_rejected_for_transactions_that_are_not_approved(): void
    {
        Storage::fake('public');
        $cabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $stockOut = $this->createStockOut($cabang, $admin, 'pending');

        $this->actingAs($admin)->post(route('stockout.bukti-pembayaran.update', $stockOut), [
            'kode_pembayaran' => 'BAY-2026-000123',
        ])->assertRedirect();

        $this->assertNull($stockOut->fresh()->kode_pembayaran);
    }

    public function test_staff_cannot_save_payment_proof_for_another_branch(): void
    {
        Storage::fake('public');
        $otherCabang = $this->createCabang('C02');
        $ownCabang = $this->createCabang('C01');
        $admin = $this->createUser('admin_ho', null);
        $staff = $this->createUser('staff_cabang', $ownCabang->id);
        $stockOut = $this->createStockOut($otherCabang, $admin, 'approved');

        $this->actingAs($staff)->post(route('stockout.bukti-pembayaran.update', $stockOut), [
            'kode_pembayaran' => 'BAY-2026-000123',
        ])->assertForbidden();

        $this->assertNull($stockOut->fresh()->kode_pembayaran);
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

    private function createStockOut(Cabang $cabang, User $creator, string $status, ?string $batchId = null): StockOut
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
            'jenis_pembayaran' => 'qris',
            'harga_jual' => 120000,
            'discount' => 10,
            'total' => 108000,
            'status' => $status,
            'tanggal' => now()->toDateString(),
            'batch_id' => $batchId,
            'user_id' => $creator->id,
            'approved_by' => $status === 'approved' ? $creator->id : null,
            'approved_at' => $status === 'approved' ? now() : null,
        ]);
    }
}
