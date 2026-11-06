<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
    }

    public function test_audit_logs_index_renders_with_filters(): void
    {
        AuditLog::record('login', $this->admin, [], ['status' => 'success']);

        $response = $this->actingAs($this->admin)->get(route('audit-logs.index'));

        $response->assertOk();
        $response->assertSee('Audit Log Aktivitas Sistem');
        $response->assertSee('Semua Aksi');
        $response->assertSee('LOGIN');
    }

    public function test_audit_logs_show_json(): void
    {
        $log = AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'update',
            'auditable_type' => User::class,
            'auditable_id' => $this->admin->id,
            'old_values' => ['name' => 'Old Admin'],
            'new_values' => ['name' => 'New Admin'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $response = $this->actingAs($this->admin)->get(route('audit-logs.show', $log));

        $response->assertOk();
        $response->assertJson([
            'id' => $log->id,
            'action' => 'update',
            'auditable_type' => 'User',
            'old_values' => ['name' => 'Old Admin'],
            'new_values' => ['name' => 'New Admin'],
        ]);
    }
}
