<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase8DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
    }

    public function test_dashboard_renders_with_metrics(): void
    {
        $this->actingAs($this->admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Omzet Hari Ini')
            ->assertSee('Branch Performance');
    }

    public function test_branch_dashboard_renders(): void
    {
        $branch = Branch::firstOrFail();

        $this->actingAs($this->admin)->get(route('dashboard', ['branch_id' => $branch->id]))
            ->assertOk()
            ->assertSee('Branch Dashboard')
            ->assertSee($branch->name);
    }
}
