<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_items_but_only_sees_read_only_controls(): void
    {
        $cabang = $this->createCabang();
        $staff = $this->createUser('staff_cabang', $cabang->id);
        $item = $this->createItem($cabang);

        $this->actingAs($staff)->get(route('items.index'))
            ->assertOk()
            ->assertSee($item->nama_items)
            ->assertSee(route('items.show', $item))
            ->assertDontSee(route('items.create'))
            ->assertDontSee(route('items.edit', $item))
            ->assertDontSee('items-delete-form')
            ->assertDontSee('fa-edit')
            ->assertDontSee(route('items.import.form'));

        $this->get(route('items.show', $item))->assertOk();
    }

    public function test_staff_cannot_access_item_crud_or_import_endpoints(): void
    {
        $cabang = $this->createCabang();
        $staff = $this->createUser('staff_cabang', $cabang->id);
        $item = $this->createItem($cabang);

        $this->actingAs($staff)->get(route('items.create'))->assertForbidden();
        $this->get(route('items.edit', $item))->assertForbidden();
        $this->post(route('items.store'), [])->assertForbidden();
        $this->put(route('items.update', $item), [])->assertForbidden();
        $this->delete(route('items.destroy', $item))->assertForbidden();
        $this->get(route('items.import.form'))->assertForbidden();
        $this->get(route('items.import.template'))->assertForbidden();
        $this->post(route('items.import'), [])->assertForbidden();

        $this->assertDatabaseHas('items', ['id' => $item->id, 'nama_items' => $item->nama_items]);
    }

    public function test_admin_can_access_item_crud_pages(): void
    {
        $cabang = $this->createCabang();
        $admin = $this->createUser('admin_ho', null);
        $item = $this->createItem($cabang);

        $this->actingAs($admin)->get(route('items.create'))->assertOk();
        $this->get(route('items.edit', $item))->assertOk();
        $this->get(route('items.import.form'))->assertOk();
    }

    private function createCabang(): Cabang
    {
        return Cabang::create([
            'kode_cabang' => 'TAG-01',
            'nama_cabang' => 'TAG Head Office',
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

    private function createItem(Cabang $cabang): Item
    {
        return Item::create([
            'kode_items' => 'ITEM-001',
            'nama_items' => 'Test Item',
            'kategori' => 'jacket',
            'harga_items' => 100000,
            'stok_items' => 0,
            'harga_jual' => 120000,
            'cabang_id' => $cabang->id,
        ]);
    }
}
