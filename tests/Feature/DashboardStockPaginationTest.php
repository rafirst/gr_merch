<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStockPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_stock_summary_shows_fifteen_grouped_items_per_page(): void
    {
        $firstBranch = $this->createBranch('C01');
        $secondBranch = $this->createBranch('C02');
        $admin = $this->createAdmin();

        for ($number = 1; $number <= 15; $number++) {
            $this->createItem($firstBranch, $number);
        }

        $this->createItem($firstBranch, 16, 16);
        $this->createItem($secondBranch, 16, 9);

        $firstPage = $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Showing 1 to 15 of 16 results')
            ->assertSee('stock_page=2')
            ->assertDontSee('Stock Item 16');

        $this->assertSame(15, substr_count((string) $firstPage->getContent(), 'class="stock-check-row"'));

        $secondPage = $this->get(route('dashboard', ['stock_page' => 2]))
            ->assertOk()
            ->assertSee('Showing 16 to 16 of 16 results')
            ->assertSee('Stock Item 16')
            ->assertSee('<td class="text-right stock-value">25</td>', false);

        $this->assertSame(1, substr_count((string) $secondPage->getContent(), 'class="stock-check-row"'));
    }

    public function test_dashboard_stock_search_matches_items_across_the_grouped_inventory(): void
    {
        $branch = $this->createBranch('C01');
        $admin = $this->createAdmin();
        $this->createItem($branch, 1);
        $this->createItem($branch, 16);

        $response = $this->actingAs($admin)->get(route('dashboard', ['search' => 'CODE-16']))
            ->assertOk()
            ->assertSee('value="CODE-16"', false)
            ->assertSee('Stock Item 16')
            ->assertSee('Showing 1 to 1 of 1 results');

        $this->assertSame(1, substr_count((string) $response->getContent(), 'class="stock-check-row"'));
    }

    private function createBranch(string $code): Cabang
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

    private function createItem(Cabang $branch, int $number, int $stock = 0): Item
    {
        return Item::create([
            'kode_items' => sprintf('CODE-%02d', $number),
            'nama_items' => sprintf('Stock Item %02d', $number),
            'kategori' => 'jacket',
            'harga_items' => 100000,
            'stok_items' => $stock,
            'harga_jual' => 120000,
            'cabang_id' => $branch->id,
        ]);
    }
}
