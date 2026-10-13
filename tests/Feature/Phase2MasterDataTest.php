<?php

namespace Tests\Feature;

use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_master_data_pages_render(): void
    {
        $admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();

        $routes = [
            'companies.index',
            'branches.index',
            'departments.index',
            'positions.index',
            'employees.index',
            'customers.index',
            'users.index',
            'roles.index',
        ];

        foreach ($routes as $name) {
            $this->actingAs($admin)->get(route($name))->assertOk();
        }

        $entities = ['product-categories', 'brands', 'units', 'products', 'service-categories', 'services', 'service-packages', 'suppliers', 'banks', 'financial-accounts', 'payment-methods'];

        foreach ($entities as $entity) {
            $this->actingAs($admin)->get(route('masterdata.index', $entity))->assertOk();
            $this->actingAs($admin)->get(route('masterdata.create', $entity))->assertOk();
        }
    }

    public function test_can_create_product_via_master_crud(): void
    {
        $admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();

        $category = ProductCategory::firstOrFail();
        $unit = Unit::firstOrFail();

        $response = $this->actingAs($admin)->post(route('masterdata.store', 'products'), [
            'sku' => 'TEST-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Produk Test',
            'status' => 'ACTIVE',
        ]);

        $response->assertRedirect(route('masterdata.index', 'products'));
        $this->assertDatabaseHas('products', ['sku' => 'TEST-001', 'name' => 'Produk Test']);
    }
}
