<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Cabang;
use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\TransferBarang;
use App\Models\TransferBarangItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleReadOnlyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_branch_data_without_mutation_controls(): void
    {
        $admin = $this->createUser('admin', null);
        $source = $this->createCabang('SRC');
        $destination = $this->createCabang('DST');
        $sourceItem = $this->createItem($source, 'ITEM-SRC');
        $destinationItem = $this->createItem($destination, 'ITEM-DST');
        $stockIn = $this->createStockIn($sourceItem, $source, $admin);
        $this->createStockIn($destinationItem, $destination, $admin);
        $stockOut = StockOut::create([
            'item_id' => $sourceItem->id,
            'cabang_id' => $source->id,
            'jumlah' => 1,
            'jenis' => 'penjualan',
            'status' => 'approved',
            'tanggal' => '2026-10-30',
            'user_id' => $admin->id,
        ]);
        $transfer = TransferBarang::create([
            'from_cabang_id' => $source->id,
            'to_cabang_id' => $destination->id,
            'created_by' => $admin->id,
            'status' => TransferBarang::STATUS_PROSES,
        ]);
        TransferBarangItem::create([
            'transfer_barang_id' => $transfer->id,
            'source_item_id' => $sourceItem->id,
            'jumlah_dikirim' => 1,
        ]);

        $this->actingAs($admin);

        $this->get(route('dashboard'))->assertOk()
            ->assertDontSee('Approval Items');

        $this->get(route('items.index'))->assertOk()
            ->assertSee($sourceItem->nama_items)
            ->assertSee($destinationItem->nama_items)
            ->assertSee($source->nama_cabang)
            ->assertSee($destination->nama_cabang)
            ->assertSee(route('items.export.excel'), false)
            ->assertDontSee(route('items.create'), false)
            ->assertDontSee(route('items.edit', $sourceItem), false);

        $this->get(route('items.show', $destinationItem))->assertOk();

        $this->get(route('stockin.index'))->assertOk()
            ->assertSee($sourceItem->nama_items)
            ->assertSee($destinationItem->nama_items)
            ->assertSee(route('stockin.export.excel'), false)
            ->assertDontSee(route('stockin.create'), false)
            ->assertDontSee(route('stockin.edit', $stockIn), false);
        $this->get(route('stockin.show', $stockIn))->assertOk()
            ->assertDontSee(route('stockin.edit', $stockIn), false);

        $this->get(route('stockout.index'))->assertOk()
            ->assertSee(route('stockout.show', $stockOut), false)
            ->assertDontSee(route('stockout.create'), false)
            ->assertDontSee(route('stockout.edit', $stockOut), false)
            ->assertDontSee('paymentModal-');
        $this->get(route('stockout.show', $stockOut))->assertOk();
        $this->get(route('stockout.invoice', $stockOut))->assertOk();

        $this->get(route('transfers.index'))->assertOk()
            ->assertSee($transfer->fromCabang->nama_cabang)
            ->assertSee($transfer->toCabang->nama_cabang)
            ->assertSee(route('transfers.show', $transfer), false)
            ->assertDontSee(route('transfers.create'), false)
            ->assertDontSee(route('transfers.edit', $transfer), false);
        $this->get(route('transfers.show', $transfer))->assertOk();

        $this->get(route('history.index'))->assertOk()
            ->assertSee(route('history.export.excel'), false);

        $sidebar = $this->get(route('dashboard'))->getContent();
        $this->assertStringNotContainsString('Manajemen User', $sidebar);
        $this->assertStringNotContainsString('Data Cabang', $sidebar);
        $this->assertStringNotContainsString('Approval Items', $sidebar);

        foreach (['items.export.excel', 'stockin.export.excel', 'history.export.excel'] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_admin_cannot_access_mutation_or_administrator_only_routes(): void
    {
        $admin = $this->createUser('admin', null);
        $branch = $this->createCabang('SRC');
        $otherBranch = $this->createCabang('DST');
        $item = $this->createItem($branch, 'ITEM-SRC');
        $stockIn = $this->createStockIn($item, $branch, $admin);
        $stockOut = StockOut::create([
            'item_id' => $item->id,
            'cabang_id' => $branch->id,
            'jumlah' => 1,
            'jenis' => 'penjualan',
            'status' => 'pending',
            'tanggal' => '2026-10-30',
            'user_id' => $admin->id,
        ]);
        $transfer = TransferBarang::create([
            'from_cabang_id' => $branch->id,
            'to_cabang_id' => $otherBranch->id,
            'created_by' => $admin->id,
            'status' => TransferBarang::STATUS_PROSES,
        ]);

        $this->actingAs($admin);

        foreach ([
            ['items.create'],
            ['items.edit', $item],
            ['items.import.form'],
            ['items.import.template'],
            ['stockin.create'],
            ['stockin.edit', $stockIn],
            ['stockin.import.form'],
            ['stockin.import.template'],
            ['stockout.create'],
            ['stockout.edit', $stockOut],
            ['transfers.create'],
            ['transfers.edit', $transfer],
            ['approval.index'],
            ['approval.show.stockout', $stockOut],
            ['cabang.index'],
            ['cabang.create'],
            ['cabang.show', $branch],
            ['cabang.edit', $branch],
            ['users.index'],
            ['users.create'],
            ['users.show', $admin],
            ['users.edit', $admin],
        ] as $route) {
            $response = $this->get(route($route[0], $route[1] ?? []));
            $this->assertSame(403, $response->status(), 'Admin must be blocked from '.$route[0]);
        }

        $this->withoutMiddleware(VerifyCsrfToken::class);

        foreach ([
            ['POST', route('items.store')],
            ['PUT', route('items.update', $item)],
            ['DELETE', route('items.destroy', $item)],
            ['POST', route('items.import')],
            ['POST', route('stockin.store')],
            ['PUT', route('stockin.update', $stockIn)],
            ['DELETE', route('stockin.destroy', $stockIn)],
            ['POST', route('stockin.import')],
            ['POST', route('stockout.store')],
            ['PUT', route('stockout.update', $stockOut)],
            ['POST', route('stockout.bukti-pembayaran.update', $stockOut)],
            ['POST', route('transfers.store')],
            ['PUT', route('transfers.update', $transfer)],
            ['DELETE', route('transfers.destroy', $transfer)],
            ['POST', route('transfers.receive', $transfer)],
            ['POST', route('users.store')],
            ['PUT', route('users.update', $admin)],
            ['DELETE', route('users.destroy', $admin)],
            ['POST', route('cabang.store')],
            ['PUT', route('cabang.update', $branch)],
            ['DELETE', route('cabang.destroy', $branch)],
            ['POST', route('approval.approve', $stockOut)],
            ['POST', route('approval.reject', $stockOut)],
        ] as [$method, $url]) {
            $response = $this->call($method, $url);
            $this->assertSame(403, $response->status(), 'Admin must be blocked from '.$method.' '.$url);
        }
    }

    public function test_administrator_can_create_an_admin_account_without_a_branch(): void
    {
        $administrator = $this->createUser('admin_ho', null);

        $this->withoutMiddleware(VerifyCsrfToken::class)
            ->actingAs($administrator)->post(route('users.store'), [
                'name' => 'Read Only Admin',
                'username' => 'readonly-admin',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'admin',
                'cabang_id' => '',
            ])->assertRedirect(route('users.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'username' => 'readonly-admin',
            'role' => 'admin',
            'cabang_id' => null,
        ]);
    }

    public function test_admin_role_keeps_the_selected_branch_when_created_and_updated(): void
    {
        $administrator = $this->createUser('admin_ho', null);
        $firstBranch = $this->createCabang('ADM');
        $secondBranch = $this->createCabang('ALT');

        $this->withoutMiddleware(VerifyCsrfToken::class)
            ->actingAs($administrator)->post(route('users.store'), [
                'name' => 'Branch Admin',
                'username' => 'branch-admin',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'admin',
                'cabang_id' => $firstBranch->id,
            ])->assertRedirect(route('users.index'))
            ->assertSessionHasNoErrors();

        $admin = User::where('username', 'branch-admin')->firstOrFail();
        $this->assertSame($firstBranch->id, $admin->cabang_id);

        $this->put(route('users.update', $admin), [
            'name' => $admin->name,
            'username' => $admin->username,
            'role' => 'admin',
            'cabang_id' => $secondBranch->id,
        ])->assertRedirect(route('users.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame($secondBranch->id, $admin->fresh()->cabang_id);
    }

    private function createUser(string $role, ?int $branchId): User
    {
        return User::create([
            'name' => ucfirst($role),
            'username' => $role.'-'.uniqid(),
            'password' => 'password',
            'role' => $role,
            'cabang_id' => $branchId,
        ]);
    }

    private function createCabang(string $suffix): Cabang
    {
        return Cabang::create([
            'kode_cabang' => 'TAG-'.$suffix,
            'nama_cabang' => 'TAG Cabang '.$suffix,
        ]);
    }

    private function createItem(Cabang $branch, string $code): Item
    {
        return Item::create([
            'kode_items' => $code,
            'nama_items' => 'Nama '.$code,
            'kategori' => 'jacket',
            'harga_items' => 100000,
            'stok_items' => 5,
            'harga_jual' => 120000,
            'cabang_id' => $branch->id,
        ]);
    }

    private function createStockIn(Item $item, Cabang $branch, User $user): StockIn
    {
        return StockIn::create([
            'item_id' => $item->id,
            'cabang_id' => $branch->id,
            'jumlah' => 2,
            'tanggal' => '2026-10-30',
            'sumber' => 'Input manual',
            'user_id' => $user->id,
        ]);
    }
}
