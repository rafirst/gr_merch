<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Item;
use App\Models\StockOut;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StockOutDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_retail_non_ktp_sale_stores_zero_discount_without_nik(): void
    {
        [$user, $item] = $this->createSaleContext();
        $payload = $this->salePayload($item, 'retail_non_ktp');
        unset($payload['nomor_telepon'], $payload['alamat_customer']);

        $this->actingAs($user)->post(route('stockout.store'), $payload)
            ->assertRedirect(route('stockout.index'));

        $stockOut = StockOut::firstOrFail();

        $this->assertSame(0.0, (float) $stockOut->discount);
        $this->assertNull($stockOut->nik_ktp);
        $this->assertNull($stockOut->nomor_telepon);
        $this->assertNull($stockOut->alamat_customer);
        $this->assertSame('approved', $stockOut->status);
        $this->assertSame(240000.0, (float) $stockOut->total);
        $this->assertSame(120000.0, (float) $stockOut->harga_jual);
        $this->assertSame(8, $item->fresh()->stok_items);
        $this->assertSame('Retail - Non KTP', $stockOut->discountLabel());
        $this->assertFalse($stockOut->requiresNikKtp());
    }

    public function test_retail_ktp_sale_still_requires_nik(): void
    {
        [$user, $item] = $this->createSaleContext();

        $this->actingAs($user)->post(route('stockout.store'), $this->salePayload($item, 'retail'))
            ->assertSessionHasErrors('nik_ktp');

        $this->assertDatabaseMissing('stock_outs', ['item_id' => $item->id]);
        $this->assertSame(10, $item->fresh()->stok_items);
    }

    public function test_member_sale_still_requires_jabatan_and_waits_for_approval(): void
    {
        [$user, $item] = $this->createSaleContext();
        $payload = $this->salePayload($item, 'member');

        $this->actingAs($user)->post(route('stockout.store'), $payload)
            ->assertSessionHasErrors('jabatan');

        $payload['jabatan'] = 'Sales Manager';
        $this->actingAs($user)->post(route('stockout.store'), $payload)
            ->assertRedirect(route('stockout.index'));

        $stockOut = StockOut::firstOrFail();

        $this->assertSame(15.0, (float) $stockOut->discount);
        $this->assertNull($stockOut->nik_ktp);
        $this->assertSame('Sales Manager', $stockOut->jabatan);
        $this->assertSame('pending', $stockOut->status);
        $this->assertTrue($stockOut->butuhApproval());
    }

    public function test_foto_id_card_column_is_removed(): void
    {
        $this->assertFalse(Schema::hasColumn('stock_outs', 'foto_id_card'));
    }

    public function test_member_detail_shows_jabatan_without_photo_upload_row(): void
    {
        [$user, $item] = $this->createSaleContext();
        $stockOut = StockOut::create([
            'item_id' => $item->id,
            'cabang_id' => $item->cabang_id,
            'jumlah' => 1,
            'jenis' => 'penjualan',
            'jenis_pembayaran' => 'transfer',
            'nama_customer' => 'Customer Test',
            'jabatan' => 'Sales Manager',
            'harga_jual' => 120000,
            'discount' => 15,
            'total' => 102000,
            'status' => 'pending',
            'tanggal' => now()->toDateString(),
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->get(route('stockout.show', $stockOut))
            ->assertOk()
            ->assertSee('Jabatan')
            ->assertSee('Sales Manager')
            ->assertDontSee('Foto ID Card');
    }

    public function test_create_form_lists_all_three_discount_options(): void
    {
        [$user] = $this->createSaleContext();

        $this->actingAs($user)->get(route('stockout.create'))
            ->assertOk()
            ->assertSee('TAG Member')
            ->assertSee('Retail - KTP')
            ->assertSee('Retail - Non KTP')
            ->assertSee('name="jabatan"', false)
            ->assertDontSee('name="id_card"', false)
            ->assertSee('data-harga-jual="120000.00"', false)
            ->assertSee("'Discount ' + discount + '%'", false)
            ->assertSee("discountInput.value === 'retail_non_ktp'", false)
            ->assertSee('id="previewDiscountRow"', false);
    }

    public function test_create_form_makes_customer_contact_fields_readonly_for_retail_non_ktp(): void
    {
        [$user] = $this->createSaleContext();

        $response = $this->actingAs($user)
            ->withSession(['_old_input' => ['jenis' => 'penjualan', 'discount' => 'retail_non_ktp']])
            ->get(route('stockout.create'))
            ->assertOk();

        $content = $response->getContent();

        $this->assertSame(1, preg_match('/id="nikKtp"[^>]*readonly/', $content));
        $this->assertSame(1, preg_match('/id="nomorTelepon"[^>]*readonly/', $content));
        $this->assertSame(1, preg_match('/id="alamatCustomer"[^>]*readonly/', $content));
        $this->assertSame(1, preg_match('/id="nikKtpRequiredIndicator"[^>]*hidden/', $content));
        $this->assertSame(1, preg_match('/id="nomorTeleponRequiredIndicator"[^>]*hidden/', $content));
        $this->assertSame(1, preg_match('/id="alamatCustomerRequiredIndicator"[^>]*hidden/', $content));
        $this->assertStringContainsString('input.readOnly = isReadonlyContact;', $content);
    }

    public function test_edit_form_disables_nik_for_retail_non_ktp_sale(): void
    {
        [$user, $item] = $this->createSaleContext();
        $stockOut = StockOut::create([
            'item_id' => $item->id,
            'cabang_id' => $item->cabang_id,
            'jumlah' => 1,
            'jenis' => 'penjualan',
            'jenis_pembayaran' => 'transfer',
            'nama_customer' => 'Customer Test',
            'nomor_telepon' => '081234567890',
            'alamat_customer' => 'Alamat Test',
            'harga_jual' => 100000,
            'discount' => 0,
            'total' => 100000,
            'status' => 'approved',
            'tanggal' => now()->toDateString(),
            'user_id' => $user->id,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $this->actingAs($user)->get(route('stockout.edit', $stockOut))
            ->assertOk()
            ->assertSee('Retail - Non KTP')
            ->assertSee('value="120.000"', false)
            ->assertSee('class="price-input" value="120000.00"', false)
            ->assertSee('id="nikKtp" class="form-control" value="" minlength="16" maxlength="16" inputmode="numeric" pattern="[0-9]{16}" readonly', false)
            ->assertSee('id="nomorTelepon"', false)
            ->assertSee('id="alamatCustomer"', false);
    }

    public function test_edit_recalculates_sale_price_and_total_from_item_harga_jual(): void
    {
        [$user, $item] = $this->createSaleContext();
        $item->decrement('stok_items', 1);
        $stockOut = StockOut::create([
            'item_id' => $item->id,
            'cabang_id' => $item->cabang_id,
            'jumlah' => 1,
            'jenis' => 'penjualan',
            'jenis_pembayaran' => 'transfer',
            'nama_customer' => 'Customer Test',
            'nik_ktp' => null,
            'nomor_telepon' => '081234567890',
            'alamat_customer' => 'Alamat Test',
            'harga_jual' => 100000,
            'discount' => 0,
            'total' => 100000,
            'status' => 'approved',
            'tanggal' => now()->toDateString(),
            'user_id' => $user->id,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $this->actingAs($user)->put(route('stockout.update', $stockOut), [
            'item_id' => [$item->id],
            'jumlah' => [2],
        ])->assertRedirect(route('stockout.index'));

        $this->assertSame(120000.0, (float) $stockOut->fresh()->harga_jual);
        $this->assertSame(240000.0, (float) $stockOut->fresh()->total);
        $this->assertSame(8, $item->fresh()->stok_items);
    }

    public function test_show_uses_database_sale_price_and_recalculates_discounted_line_total(): void
    {
        [$user, $item] = $this->createSaleContext();
        $item->update(['harga_jual' => 200000]);
        $stockOut = StockOut::create([
            'item_id' => $item->id,
            'cabang_id' => $item->cabang_id,
            'jumlah' => 2,
            'jenis' => 'penjualan',
            'jenis_pembayaran' => 'transfer',
            'nama_customer' => 'Customer Test',
            'nomor_telepon' => '081234567890',
            'alamat_customer' => 'Alamat Test',
            'harga_jual' => 189000,
            'discount' => 10,
            'total' => 340200,
            'status' => 'approved',
            'tanggal' => now()->toDateString(),
            'user_id' => $user->id,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('stockout.show', $stockOut));

        $response->assertOk()
            ->assertSee('<th>Harga Jual</th>', false)
            ->assertSee('Rp 200.000')
            ->assertSee('Rp 400.000')
            ->assertSee('Rp 360.000')
            ->assertSee('Rp 40.000');

        $this->assertSame(1, substr_count($response->getContent(), '<th>Diskon</th>'));
        $this->assertMatchesRegularExpression('/<td>Rp 200\\.000<\/td>\s*<td>2\s*<\/td>\s*<td>Rp 400\\.000<\/td>/', $response->getContent());
    }

    /** @return array{0: User, 1: Item} */
    private function createSaleContext(): array
    {
        $cabang = Cabang::create([
            'kode_cabang' => 'C01',
            'nama_cabang' => 'Cabang C01',
        ]);
        $user = User::create([
            'name' => 'Admin HO',
            'username' => 'admin-'.uniqid(),
            'password' => 'password',
            'role' => 'admin_ho',
            'cabang_id' => null,
        ]);
        $item = Item::create([
            'kode_items' => 'ITEM-'.uniqid(),
            'nama_items' => 'Test Item',
            'kategori' => 'jacket',
            'harga_items' => 100000,
            'harga_jual' => 120000,
            'stok_items' => 10,
            'cabang_id' => $cabang->id,
        ]);

        return [$user, $item];
    }

    /** @return array<string, mixed> */
    private function salePayload(Item $item, string $discount): array
    {
        return [
            'item_id' => [$item->id],
            'jumlah' => [2],
            'jenis' => 'penjualan',
            'jenis_pembayaran' => 'transfer',
            'nama_customer' => 'Customer Test',
            'nomor_telepon' => '081234567890',
            'alamat_customer' => 'Alamat Test',
            'harga_jual' => [100000],
            'discount' => $discount,
            'tanggal' => now()->toDateString(),
        ];
    }
}
