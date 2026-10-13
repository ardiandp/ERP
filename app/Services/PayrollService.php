<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeCommission;
use App\Models\EmployeeSalaryComponent;
use App\Models\OvertimeRecord;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\PayrollAdjustment;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PayrollService
{
    public function generate(PayrollPeriod $period): array
    {
        if ($period->status === 'CLOSED') {
            throw new InvalidArgumentException('Periode payroll sudah ditutup.');
        }

        $employees = Employee::where('status', 'ACTIVE')->orderBy('id')->get();

        if ($employees->isEmpty()) {
            throw new InvalidArgumentException('Tidak ada karyawan aktif.');
        }

        return DB::transaction(function () use ($period, $employees) {
            $created = [];

            foreach ($employees as $employee) {
                $structure = $employee->salaryStructures()
                    ->where('status', 'ACTIVE')
                    ->where('effective_date', '<=', $period->period_end)
                    ->orderByDesc('effective_date')
                    ->first();

                if (! $structure) {
                    continue;
                }

                $exists = Payroll::where('payroll_period_id', $period->id)
                    ->where('employee_id', $employee->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $payroll = Payroll::create([
                    'payroll_period_id' => $period->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $structure->basic_salary,
                    'status' => 'DRAFT',
                    'generated_at' => now(),
                ]);

                $this->buildItems($payroll, $employee, $period, (float) $structure->basic_salary);
                $this->recalculate($payroll);

                $created[] = $payroll->fresh();
            }

            return $created;
        });
    }

    protected function buildItems(Payroll $payroll, Employee $employee, PayrollPeriod $period, float $basic): void
    {
        $sort = 0;

        PayrollItem::create([
            'payroll_id' => $payroll->id,
            'component_name' => 'Basic Salary',
            'type' => 'EARNING',
            'amount' => $basic,
            'sort_order' => $sort++,
        ]);

        $components = EmployeeSalaryComponent::with('salaryComponent')
            ->where('employee_id', $employee->id)
            ->where('status', 'ACTIVE')
            ->where('effective_date', '<=', $period->period_end)
            ->where(fn ($query) => $query->whereNull('end_date')->orWhere('end_date', '>=', $period->period_start))
            ->get();

        foreach ($components as $component) {
            $type = $component->salaryComponent?->type ?? 'EARNING';
            $amount = $component->calculation_type === 'PERCENTAGE'
                ? round($basic * ((float) $component->amount) / 100, 2)
                : (float) $component->amount;

            PayrollItem::create([
                'payroll_id' => $payroll->id,
                'salary_component_id' => $component->salary_component_id,
                'component_name' => $component->salaryComponent?->name ?? 'Component',
                'type' => $type,
                'amount' => $amount,
                'sort_order' => $sort++,
            ]);
        }

        $overtimeMinutes = (int) OvertimeRecord::where('employee_id', $employee->id)
            ->where('status', 'APPROVED')
            ->whereBetween('overtime_date', [$period->period_start, $period->period_end])
            ->sum('duration_minutes');

        if ($overtimeMinutes > 0) {
            $hourly = $basic / 173;
            $amount = round(($overtimeMinutes / 60) * $hourly, 2);

            PayrollItem::create([
                'payroll_id' => $payroll->id,
                'component_name' => 'Overtime',
                'type' => 'EARNING',
                'amount' => $amount,
                'notes' => $overtimeMinutes.' minutes',
                'sort_order' => $sort++,
            ]);
        }

        $commissions = EmployeeCommission::where('employee_id', $employee->id)
            ->whereNull('payroll_id')
            ->where('status', '!=', 'PAID')
            ->get();

        foreach ($commissions as $commission) {
            PayrollItem::create([
                'payroll_id' => $payroll->id,
                'component_name' => 'Commission',
                'type' => 'EARNING',
                'amount' => (float) $commission->commission_amount,
                'source_type' => EmployeeCommission::class,
                'source_id' => $commission->id,
                'sort_order' => $sort++,
            ]);

            $commission->update(['payroll_id' => $payroll->id]);
        }
    }

    public function adjust(Payroll $payroll, array $data): PayrollAdjustment
    {
        if ($payroll->status === 'APPROVED' || $payroll->status === 'PAID') {
            throw new InvalidArgumentException('Payroll yang sudah disetujui tidak dapat diubah.');
        }

        return DB::transaction(function () use ($payroll, $data) {
            $adjustment = PayrollAdjustment::create([
                'payroll_id' => $payroll->id,
                'type' => $data['type'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'reason' => $data['reason'] ?? null,
                'created_by' => auth()->id(),
            ]);

            PayrollItem::create([
                'payroll_id' => $payroll->id,
                'component_name' => $data['description'],
                'type' => $data['type'],
                'amount' => $data['amount'],
                'source_type' => PayrollAdjustment::class,
                'source_id' => $adjustment->id,
                'notes' => 'Adjustment',
                'sort_order' => 100,
            ]);

            $this->recalculate($payroll);

            return $adjustment;
        });
    }

    public function recalculate(Payroll $payroll): Payroll
    {
        $earnings = (float) $payroll->items()->where('type', 'EARNING')->sum('amount');
        $deductions = (float) $payroll->items()->where('type', 'DEDUCTION')->sum('amount');

        $payroll->update([
            'total_earnings' => $earnings,
            'total_deductions' => $deductions,
            'net_salary' => $earnings - $deductions,
        ]);

        return $payroll;
    }

    public function approve(Payroll $payroll): Payroll
    {
        if ($payroll->status === 'APPROVED' || $payroll->status === 'PAID') {
            throw new InvalidArgumentException('Payroll sudah disetujui.');
        }

        $payroll->update([
            'status' => 'APPROVED',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return $payroll;
    }

    public function generatePayslip(Payroll $payroll, ?int $templateId = null): Payslip
    {
        if ($payroll->status !== 'APPROVED') {
            throw new InvalidArgumentException('Payroll harus disetujui sebelum membuat slip.');
        }

        if ($payroll->payslip()->exists()) {
            throw new InvalidArgumentException('Slip gaji sudah dibuat.');
        }

        return DB::transaction(function () use ($payroll, $templateId) {
            $payslip = Payslip::create([
                'payroll_id' => $payroll->id,
                'employee_id' => $payroll->employee_id,
                'template_id' => $templateId,
                'payslip_number' => DocumentNumber::generate('PSP', Payslip::class, 'payslip_number', now()->toDateString()),
                'issue_date' => now()->toDateString(),
                'gross_salary' => $payroll->total_earnings,
                'total_deduction' => $payroll->total_deductions,
                'net_salary' => $payroll->net_salary,
                'status' => 'DRAFT',
                'verification_code' => strtoupper(substr(md5($payroll->id.now()->timestamp), 0, 12)),
            ]);

            foreach ($payroll->items()->orderBy('sort_order')->get() as $item) {
                $payslip->items()->create([
                    'component_name' => $item->component_name,
                    'type' => $item->type,
                    'amount' => $item->amount,
                    'notes' => $item->notes,
                    'sort_order' => $item->sort_order,
                ]);
            }

            return $payslip->fresh();
        });
    }

    public function pay(Payroll $payroll, array $data): Payroll
    {
        if ($payroll->status !== 'APPROVED') {
            throw new InvalidArgumentException('Hanya payroll disetujui yang dapat dibayar.');
        }

        if ($payroll->status === 'PAID') {
            throw new InvalidArgumentException('Payroll sudah dibayar.');
        }

        return DB::transaction(function () use ($payroll, $data) {
            Payment::create([
                'payment_number' => DocumentNumber::generate('PAY', Payment::class, 'payment_number', now()->toDateString()),
                'transaction_type' => Payroll::class,
                'transaction_id' => $payroll->id,
                'payment_method_id' => $data['payment_method_id'],
                'financial_account_id' => $data['financial_account_id'] ?? null,
                'amount' => $payroll->net_salary,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'status' => 'POSTED',
                'notes' => 'Payroll payment',
                'created_by' => auth()->id(),
            ]);

            $payroll->update(['status' => 'PAID']);

            $commissionIds = $payroll->items()
                ->where('source_type', EmployeeCommission::class)
                ->pluck('source_id');
            EmployeeCommission::whereIn('id', $commissionIds)->update(['status' => 'PAID']);

            return $payroll;
        });
    }

    public function closePeriod(PayrollPeriod $period): PayrollPeriod
    {
        $unapproved = $period->payrolls()->whereNotIn('status', ['APPROVED', 'PAID'])->count();

        if ($unapproved > 0) {
            throw new InvalidArgumentException('Masih ada payroll yang belum disetujui.');
        }

        $period->update(['status' => 'CLOSED']);

        return $period;
    }
}
