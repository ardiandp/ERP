<?php

namespace Tests\Feature;

use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\LowStockAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase8NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
    }

    public function test_alert_scan_notifies_owner_about_low_stock(): void
    {
        $warehouse = Warehouse::firstOrFail();
        $product = Product::firstOrFail();
        $product->update(['minimum_stock' => 10]);

        InventoryStock::updateOrCreate(
            ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
            ['quantity' => 0],
        );

        $this->artisan('alerts:scan')->assertSuccessful();

        $this->assertDatabaseHas('notifications', [
            'type' => LowStockAlert::class,
            'notifiable_id' => $this->admin->id,
        ]);

        $count = $this->admin->notifications()->count();

        $this->artisan('alerts:scan')->assertSuccessful();

        $this->assertSame($count, $this->admin->notifications()->count());
    }

    public function test_notifications_page_lists_and_marks_read(): void
    {
        $this->admin->notify(new LowStockAlert(3));

        $this->actingAs($this->admin)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('produk memiliki stok menipis');

        $this->actingAs($this->admin)->post(route('notifications.readAll'))->assertRedirect();

        $this->assertSame(0, $this->admin->fresh()->unreadNotifications->count());
    }
}
