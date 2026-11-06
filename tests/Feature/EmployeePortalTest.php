<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeePortalTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Employee $employee;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->branch = Branch::firstOrFail();

        $this->employee = Employee::create([
            'employee_code' => 'EMP-ESS-01',
            'nik' => '320101990001',
            'full_name' => 'Dewi Anggraini',
            'branch_id' => $this->branch->id,
            'employment_status' => 'PERMANENT',
            'gender' => 'F',
            'status' => 'ACTIVE',
        ]);

        $this->user = User::factory()->create([
            'name' => 'Dewi Anggraini',
            'email' => 'dewi@dianmustika.test',
            'employee_id' => $this->employee->id,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_portal_profile_renders_for_linked_employee(): void
    {
        $response = $this->actingAs($this->user)->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('Dewi Anggraini');
        $response->assertSee('EMP-ESS-01');
        $response->assertSee('320101990001');
    }

    public function test_portal_attendance_renders(): void
    {
        $response = $this->actingAs($this->user)->get(route('portal.attendance'));

        $response->assertOk();
        $response->assertSee('Absensi & Pengajuan Lembur Saya');
        $response->assertSee('Form Pengajuan Lembur');
    }

    public function test_portal_employee_can_submit_overtime(): void
    {
        $response = $this->actingAs($this->user)->post(route('portal.overtime.submit'), [
            'overtime_date' => now()->toDateString(),
            'start_time' => '17:30',
            'end_time' => '19:30',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('overtime_records', [
            'employee_id' => $this->employee->id,
            'start_time' => '17:30',
            'end_time' => '19:30',
            'status' => 'PENDING',
        ]);
    }

    public function test_portal_payslips_renders(): void
    {
        $period = PayrollPeriod::create([
            'code' => 'PP-2026-11',
            'name' => 'November 2026',
            'period_start' => '2026-11-01',
            'period_end' => '2026-11-30',
            'status' => 'OPEN',
        ]);

        $payroll = Payroll::create([
            'payroll_number' => 'PAY-202611-002',
            'payroll_period_id' => $period->id,
            'employee_id' => $this->employee->id,
            'basic_salary' => 3000000,
            'total_allowances' => 0,
            'total_deductions' => 0,
            'net_salary' => 3000000,
            'status' => 'APPROVED',
        ]);

        $payslip = Payslip::create([
            'payroll_id' => $payroll->id,
            'employee_id' => $this->employee->id,
            'payslip_number' => 'SLP-ESS-001',
            'issue_date' => now()->toDateString(),
            'gross_salary' => 3000000,
            'total_deduction' => 0,
            'net_salary' => 3000000,
            'status' => 'PUBLISHED',
        ]);

        $response = $this->actingAs($this->user)->get(route('portal.payslips'));

        $response->assertOk();
        $response->assertSee('Slip Gaji Karyawan');
        $response->assertSee('SLP-ESS-001');
        $response->assertSee('Rp 3.000.000');
    }
}
