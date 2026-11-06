<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
    }

    public function test_settings_index_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('settings.index'));

        $response->assertOk();
        $response->assertSee('Pengaturan Sistem (Settings)');
        $response->assertSee('Nama Aplikasi');
        $response->assertSee('Header Struk Pembayaran');
    }

    public function test_settings_can_be_updated(): void
    {
        $payload = [
            'app_name' => 'ERP Dian Mustika Pro',
            'company_legal_name' => 'PT Dian Mustika Sukses',
            'company_phone' => '021-99887766',
            'company_email' => 'admin@dianmustika.com',
            'company_address' => 'Jl. Boulevard No. 12',
            'pos_receipt_header' => 'Dian Mustika Beauty & Spa',
            'pos_receipt_footer' => 'Terima kasih dan sampai jumpa kembali.',
            'pos_default_tax' => 11,
            'inventory_low_stock_threshold' => 10,
            'attendance_work_start' => '08:30',
            'attendance_grace_period_minutes' => 20,
        ];

        $response = $this->actingAs($this->admin)->post(route('settings.update'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('ERP Dian Mustika Pro', Setting::getValue('app.name'));
        $this->assertEquals('PT Dian Mustika Sukses', Setting::getValue('company.legal_name'));
        $this->assertEquals('10', Setting::getValue('inventory.low_stock_threshold'));
    }
}
