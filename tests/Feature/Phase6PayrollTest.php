<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\OvertimeRecord;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\SalaryComponent;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase6PayrollTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
    }

    public function test_phase6_pages_render(): void
    {
        $this->actingAs($this->admin)->get(route('shifts.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('shifts.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('attendances.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('attendances.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('overtimes.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('overtimes.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('salary-structures.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('salary-structures.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('payroll-periods.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('payroll-periods.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('payrolls.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('payslips.index'))->assertOk();
    }

    public function test_shift_is_created_and_assigned(): void
    {
        $employee = $this->makeEmployee();

        $this->actingAs($this->admin)->post(route('shifts.store'), [
            'code' => 'TEST-PAGI',
            'name' => 'Shift Test',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'break_minutes' => 60,
            'status' => 'ACTIVE',
        ])->assertRedirect();

        $shift = Shift::where('code', 'TEST-PAGI')->firstOrFail();

        $this->actingAs($this->admin)->post(route('shifts.assign'), [
            'employee_id' => $employee->id,
            'shift_id' => $shift->id,
            'effective_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('employee_shifts', [
            'employee_id' => $employee->id,
            'shift_id' => $shift->id,
        ]);
    }

    public function test_attendance_and_overtime_are_recorded_and_approved(): void
    {
        $employee = $this->makeEmployee();
        $branch = Branch::firstOrFail();

        $this->actingAs($this->admin)->post(route('attendances.store'), [
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'attendance_date' => now()->toDateString(),
            'check_in' => '08:00',
            'check_out' => '17:00',
            'status' => 'PRESENT',
        ])->assertRedirect();

        $this->assertDatabaseHas('attendances', ['employee_id' => $employee->id]);

        $this->actingAs($this->admin)->post(route('overtimes.store'), [
            'employee_id' => $employee->id,
            'overtime_date' => now()->toDateString(),
            'start_time' => '18:00',
            'end_time' => '20:00',
        ])->assertRedirect();

        $overtime = OvertimeRecord::firstOrFail();
        $this->assertSame(120, $overtime->duration_minutes);
        $this->assertSame('PENDING', $overtime->status);

        $this->actingAs($this->admin)->patch(route('overtimes.approve', $overtime))->assertRedirect();

        $this->assertSame('APPROVED', $overtime->fresh()->status);
    }

    public function test_payroll_flow_from_structure_to_payment(): void
    {
        $employee = $this->makeEmployee();
        $allowance = SalaryComponent::where('code', 'ALLOWANCE')->firstOrFail();

        $this->actingAs($this->admin)->post(route('salary-structures.store'), [
            'employee_id' => $employee->id,
            'effective_date' => now()->toDateString(),
            'basic_salary' => 5000000,
            'components' => [
                ['salary_component_id' => $allowance->id, 'amount' => 500000, 'calculation_type' => 'FIXED'],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('employee_salary_structures', ['employee_id' => $employee->id, 'basic_salary' => 5000000]);

        $this->actingAs($this->admin)->post(route('payroll-periods.store'), [
            'code' => 'PP-'.now()->format('Ym'),
            'name' => 'Payroll '.now()->format('F Y'),
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
        ])->assertRedirect();

        $period = PayrollPeriod::firstOrFail();

        $this->actingAs($this->admin)->post(route('payrolls.generate', $period))->assertRedirect();

        $payroll = Payroll::firstOrFail();
        $this->assertEquals(5500000.0, (float) $payroll->total_earnings);
        $this->assertEquals(5500000.0, (float) $payroll->net_salary);
        $this->assertSame('DRAFT', $payroll->status);

        $this->actingAs($this->admin)->patch(route('payrolls.approve', $payroll))->assertRedirect();
        $this->assertSame('APPROVED', $payroll->fresh()->status);

        $this->actingAs($this->admin)->post(route('payrolls.payslip', $payroll))->assertRedirect();
        $this->assertDatabaseHas('payslips', ['payroll_id' => $payroll->id]);
        $this->assertSame(1, Payslip::where('payroll_id', $payroll->id)->count());

        $method = PaymentMethod::firstOrFail();

        $this->actingAs($this->admin)->post(route('payrolls.pay', $payroll), [
            'payment_method_id' => $method->id,
        ])->assertRedirect();

        $this->assertSame('PAID', $payroll->fresh()->status);
        $this->assertDatabaseHas('payments', [
            'transaction_type' => Payroll::class,
            'transaction_id' => $payroll->id,
        ]);
        $this->assertSame(1, Payment::count());
    }

    protected function makeEmployee(): Employee
    {
        return Employee::create([
            'employee_code' => 'EMP-'.uniqid(),
            'full_name' => 'Karyawan Test',
            'branch_id' => Branch::firstOrFail()->id,
            'status' => 'ACTIVE',
        ]);
    }
}
