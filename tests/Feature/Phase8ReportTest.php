<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\SalesTransaction;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase8ReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
    }

    public function test_all_report_types_render(): void
    {
        foreach (array_keys(app(ReportService::class)->types()) as $type) {
            $this->actingAs($this->admin)
                ->get(route('reports.index', ['type' => $type]))
                ->assertOk();
        }
    }

    public function test_sales_report_reflects_transactions(): void
    {
        SalesTransaction::create([
            'invoice_number' => 'INV-TEST-001',
            'branch_id' => Branch::firstOrFail()->id,
            'transaction_date' => now()->toDateString(),
            'subtotal' => 100000,
            'grand_total' => 100000,
            'status' => 'COMPLETED',
        ]);

        $this->actingAs($this->admin)
            ->get(route('reports.index', ['type' => 'sales_daily']))
            ->assertOk()
            ->assertSee('100.000');
    }

    public function test_report_export_returns_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.export', ['type' => 'sales_daily']));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Tanggal', $response->streamedContent());
    }
}
