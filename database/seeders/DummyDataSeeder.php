<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMovement;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\Bill;
use App\Models\BillTemplate;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Department;
use App\Models\DigitalAccount;
use App\Models\DigitalAccountCategory;
use App\Models\Employee;
use App\Models\EmployeeFamilyMember;
use App\Models\EmployeeInsurance;
use App\Models\EmployeeSalaryComponent;
use App\Models\EmployeeSalaryStructure;
use App\Models\EmployeeShift;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FinancialAccount;
use App\Models\InventoryStock;
use App\Models\MediaAttachment;
use App\Models\MediaFile;
use App\Models\OvertimeRecord;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Payroll;
use App\Models\PayrollAdjustment;
use App\Models\PayrollPeriod;
use App\Models\PayslipTemplate;
use App\Models\Position;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPrice;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\SalaryComponent;
use App\Models\SalesReturn;
use App\Models\SalesTransaction;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\Shift;
use App\Models\StockOpname;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\SupplierBankAccount;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AlertService;
use App\Services\AssetService;
use App\Services\AttendanceService;
use App\Services\BankReconciliationService;
use App\Services\BillService;
use App\Services\ExpenseService;
use App\Services\GoodsReceiptService;
use App\Services\InventoryService;
use App\Services\PaymentService;
use App\Services\PayrollService;
use App\Services\PricingService;
use App\Services\PurchaseOrderService;
use App\Services\SalesReturnService;
use App\Services\SalesService;
use App\Services\StockOpnameService;
use App\Services\StockTransferService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seed realistic, fully-related dummy data for local development and live demonstrations.
 *
 * Run standalone with: php artisan db:seed --class=DummyDataSeeder
 * It is called automatically by DatabaseSeeder in local environment.
 */
class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@dianmustika.test')->first();

        if (! $admin) {
            $this->command?->error('Admin user belum tersedia. Jalankan DatabaseSeeder lebih dulu.');

            return;
        }

        Auth::login($admin);

        DB::transaction(function () {
            [$branch1, $branch2, $wh1, $wh2] = $this->branches();
            $this->supplierBankAccounts();
            $products = $this->products();
            $this->initialStock($wh1, $wh2, $products);
            $services = Service::where('status', 'ACTIVE')->get();
            $packages = ServicePackage::where('status', 'ACTIVE')->get();
            $customers = $this->customers();
            $employees = $this->employees($branch1, $branch2);
            $this->attendanceAndOvertime($employees);
            $this->bookings($customers, $services, $packages, $employees, $branch1, $branch2);
            $this->sales($customers, $products, $services, $packages, $employees, $branch1, $branch2);
            $this->salesReturns();
            $this->purchasing($branch1, $wh1, $products);
            $this->transfersAndOpnames($wh1, $wh2, $products);
            $this->finance($branch1);
            $this->bankReconciliation();
            $this->payroll($employees);
            $this->assets($branch1, $branch2);
            $this->media();
            $this->digitalAccounts();
            $this->auditLogs();
            app(AlertService::class)->scanAll();
        });

        $this->command?->info('Dummy data berhasil diperbarui dan diselaraskan secara komprehensif.');
    }

    /**
     * @return array{0: Branch, 1: Branch, 2: Warehouse, 3: Warehouse}
     */
    protected function branches(): array
    {
        $company = Company::where('code', 'DM')->firstOrFail();
        $branch1 = Branch::where('code', 'DM-PUSAT')->firstOrFail();

        $branch2 = Branch::updateOrCreate(
            ['code' => 'DM-CABANG'],
            [
                'company_id' => $company->id,
                'name' => 'Cabang Selatan',
                'branch_type' => 'BRANCH',
                'phone' => '021-7890123',
                'email' => 'cabang.selatan@dianmustika.test',
                'address' => 'Jl. RS Fatmawati No. 88, Cilandak, Jakarta Selatan',
                'status' => 'ACTIVE',
            ],
        );

        $wh1 = Warehouse::where('branch_id', $branch1->id)->orderBy('id')->firstOrFail();

        $wh2 = Warehouse::updateOrCreate(
            ['code' => 'WH-DM-CABANG'],
            [
                'branch_id' => $branch2->id,
                'name' => 'Gudang Cabang Selatan',
                'warehouse_type' => 'MAIN',
                'address' => 'Jl. RS Fatmawati No. 88, Cilandak, Jakarta Selatan',
                'status' => 'ACTIVE',
            ],
        );

        return [$branch1, $branch2, $wh1, $wh2];
    }

    protected function supplierBankAccounts(): void
    {
        $bca = Bank::where('code', 'BCA')->first();
        $bni = Bank::where('code', 'BNI')->first();

        $sup1 = Supplier::where('supplier_code', 'SUP-001')->first();
        if ($sup1 && $bca) {
            SupplierBankAccount::updateOrCreate(
                ['supplier_id' => $sup1->id, 'account_number' => '8830129840'],
                [
                    'bank_id' => $bca->id,
                    'account_name' => 'PT Kosmetik Nusantara',
                    'branch_name' => 'KCP Pulogadung',
                    'is_primary' => true,
                    'status' => 'ACTIVE',
                ]
            );
        }

        $sup2 = Supplier::where('supplier_code', 'SUP-002')->first();
        if ($sup2 && $bni) {
            SupplierBankAccount::updateOrCreate(
                ['supplier_id' => $sup2->id, 'account_number' => '0192837465'],
                [
                    'bank_id' => $bni->id,
                    'account_name' => 'CV Aroma Spa Supply',
                    'branch_name' => 'KCP Fatmawati',
                    'is_primary' => true,
                    'status' => 'ACTIVE',
                ]
            );
        }
    }

    protected function products(): Collection
    {
        $category = ProductCategory::where('code', 'RETAIL')->first() ?? ProductCategory::firstOrFail();
        $unit = Unit::where('code', 'PCS')->first() ?? Unit::firstOrFail();
        $brandBotanic = Brand::where('code', 'DM-BOTANIC')->first();
        $brandSkin = Brand::where('code', 'DM-SKIN')->first();
        $brandWardah = Brand::where('code', 'WARDAH')->first();
        $brandSensatia = Brand::where('code', 'SENSATIA')->first();

        $rows = [
            ['PRD-001', 'Facial Wash Herbal 100ml', 25000, 45000, $brandSkin?->id],
            ['PRD-002', 'Sunscreen Glow SPF 50', 55000, 90000, $brandSkin?->id],
            ['PRD-003', 'Vitamin C Serum 20ml', 80000, 140000, $brandBotanic?->id],
            ['PRD-004', 'Minyak Massage Herbal 250ml', 40000, 75000, $brandBotanic?->id],
            ['PRD-005', 'Lulur Bengkoang Tradisional', 30000, 55000, $brandBotanic?->id],
            ['PRD-006', 'Toner Wajah Chamomile 150ml', 32000, 60000, $brandWardah?->id],
            ['PRD-007', 'Masker Clay Tea Tree 100g', 42000, 78000, $brandWardah?->id],
            ['PRD-008', 'Body Lotion Jasmine 200ml', 38000, 70000, $brandSensatia?->id],
            ['PRD-009', 'Hair Tonic Ginseng 100ml', 45000, 85000, $brandBotanic?->id],
            ['PRD-010', 'Retinol Night Serum 30ml', 95000, 165000, $brandSkin?->id],
        ];

        foreach ($rows as [$sku, $name, $purchase, $sell, $brandId]) {
            $product = Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $category->id,
                    'brand_id' => $brandId,
                    'unit_id' => $unit->id,
                    'name' => $name,
                    'purchase_price' => $purchase,
                    'default_sell_price' => $sell,
                    'minimum_stock' => 5,
                    'status' => 'ACTIVE',
                ],
            );

            ProductPrice::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'price_type' => 'DEFAULT',
                    'effective_date' => now()->startOfYear()->toDateString(),
                ],
                [
                    'price' => $sell,
                ]
            );
        }

        return Product::where('status', 'ACTIVE')->orderBy('id')->get();
    }

    protected function initialStock(Warehouse $wh1, Warehouse $wh2, Collection $products): void
    {
        $inventory = app(InventoryService::class);

        foreach ($products as $product) {
            $stock1 = InventoryStock::where('warehouse_id', $wh1->id)->where('product_id', $product->id)->first();
            if (! $stock1 || $stock1->quantity <= 0) {
                $inventory->adjust($wh1->id, $product->id, 100, [
                    'movement_type' => 'INITIAL',
                    'notes' => 'Saldo awal (dummy pusat)',
                ]);
            }

            $stock2 = InventoryStock::where('warehouse_id', $wh2->id)->where('product_id', $product->id)->first();
            if (! $stock2 || $stock2->quantity <= 0) {
                $inventory->adjust($wh2->id, $product->id, 30, [
                    'movement_type' => 'INITIAL',
                    'notes' => 'Saldo awal (dummy cabang)',
                ]);
            }
        }
    }

    protected function customers(): Collection
    {
        $names = [
            'Andi Wijaya', 'Bella Safira', 'Citra Dewi', 'Dian Puspita', 'Eko Prasetyo',
            'Fitri Handayani', 'Galih Ramadhan', 'Hana Permata', 'Indra Gunawan', 'Julia Rahmawati',
            'Krisna Bayu', 'Lestari Ningsih', 'Mega Utami', 'Nanda Pratama', 'Oscar Manullang',
            'Putri Amelia', 'Qori Anwar', 'Rudi Hartono', 'Sinta Bella', 'Taufik Hidayat',
        ];

        foreach ($names as $index => $name) {
            Customer::updateOrCreate(
                ['customer_code' => sprintf('CUST-D%03d', $index + 1)],
                [
                    'name' => $name,
                    'gender' => $index % 2 === 0 ? 'MALE' : 'FEMALE',
                    'phone' => '0812'.str_pad((string) (1000000 + $index), 7, '0', STR_PAD_LEFT),
                    'email' => 'customer'.($index + 1).'@example.test',
                    'address' => 'Jl. Merdeka No.'.($index + 1).', Jakarta',
                    'customer_type' => $index % 5 === 0 ? 'MEMBER' : ($index % 7 === 0 ? 'VIP' : 'REGULAR'),
                    'source' => $index % 3 === 0 ? 'INSTAGRAM' : 'WALK_IN',
                    'status' => 'ACTIVE',
                ],
            );
        }

        return Customer::orderBy('id')->get();
    }

    protected function employees(Branch $branch1, Branch $branch2): Collection
    {
        $ops = Department::where('code', 'OPS')->first();
        $fin = Department::where('code', 'FIN')->first();
        $hr = Department::where('code', 'HR')->first();
        $therapist = Position::where('code', 'THERAPIST')->first();
        $cashier = Position::where('code', 'CASHIER')->first();
        $accountant = Position::where('code', 'ACCOUNTANT')->first();
        $hrStaff = Position::where('code', 'HR-STAFF')->first();

        $defs = [
            ['EMP-D001', 'Rina Kartika', $branch1, $ops, $cashier, 'FEMALE'],
            ['EMP-D002', 'Dewi Anggraini', $branch1, $ops, $therapist, 'FEMALE'],
            ['EMP-D003', 'Siti Nurhaliza', $branch1, $ops, $therapist, 'FEMALE'],
            ['EMP-D004', 'Putri Ayu', $branch1, $ops, $therapist, 'FEMALE'],
            ['EMP-D005', 'Budi Santoso', $branch1, $ops, $therapist, 'MALE'],
            ['EMP-D006', 'Agus Pratama', $branch1, $fin, $accountant, 'MALE'],
            ['EMP-D007', 'Maya Sari', $branch1, $hr, $hrStaff, 'FEMALE'],
            ['EMP-D008', 'Fajar Nugroho', $branch2, $ops, $cashier, 'MALE'],
            ['EMP-D009', 'Lina Marlina', $branch2, $ops, $therapist, 'FEMALE'],
            ['EMP-D010', 'Rizky Ramadhan', $branch2, $ops, $therapist, 'MALE'],
            ['EMP-D011', 'Intan Permata', $branch2, $ops, $therapist, 'FEMALE'],
            ['EMP-D012', 'Bayu Setiawan', $branch2, $fin, $accountant, 'MALE'],
        ];

        $allowance = SalaryComponent::where('code', 'ALLOWANCE')->first();
        $shiftPagi = Shift::where('code', 'PAGI')->first();
        $shiftSiang = Shift::where('code', 'SIANG')->first();
        $effective = now()->subMonthNoOverflow()->startOfMonth()->toDateString();

        $employees = collect();

        foreach ($defs as $index => [$code, $name, $branch, $department, $position, $gender]) {
            $employee = Employee::updateOrCreate(
                ['employee_code' => $code],
                [
                    'nik' => '3171'.str_pad((string) (2000000000 + $index), 10, '0', STR_PAD_LEFT),
                    'full_name' => $name,
                    'birth_place' => 'Jakarta',
                    'birth_date' => now()->subYears(25 + $index)->startOfYear()->toDateString(),
                    'gender' => $gender,
                    'phone' => '0813'.str_pad((string) (5000000 + $index), 7, '0', STR_PAD_LEFT),
                    'email' => strtolower(str_replace(' ', '.', $code)).'@dianmustika.test',
                    'address' => 'Jl. Melati No.'.($index + 1).', Jakarta',
                    'marital_status' => $index % 3 === 0 ? 'MARRIED' : 'SINGLE',
                    'employment_status' => 'ACTIVE',
                    'join_date' => now()->subMonths(6 + $index)->startOfMonth()->toDateString(),
                    'branch_id' => $branch->id,
                    'department_id' => $department?->id,
                    'position_id' => $position?->id,
                    'status' => 'ACTIVE',
                ],
            );

            EmployeeSalaryStructure::updateOrCreate(
                ['employee_id' => $employee->id, 'effective_date' => $effective],
                ['basic_salary' => 3500000 + ($index * 250000), 'status' => 'ACTIVE'],
            );

            if ($allowance) {
                EmployeeSalaryComponent::updateOrCreate(
                    ['employee_id' => $employee->id, 'salary_component_id' => $allowance->id, 'effective_date' => $effective],
                    ['amount' => 300000, 'calculation_type' => 'FIXED', 'status' => 'ACTIVE'],
                );
            }

            $shift = $index % 2 === 0 ? $shiftPagi : $shiftSiang;

            if ($shift) {
                EmployeeShift::updateOrCreate(
                    ['employee_id' => $employee->id, 'effective_date' => $effective],
                    ['shift_id' => $shift->id],
                );
            }

            if ($index % 2 === 0) {
                EmployeeFamilyMember::updateOrCreate(
                    ['employee_id' => $employee->id, 'relationship' => 'SPOUSE'],
                    ['name' => 'Pasangan '.$name, 'is_dependent' => true],
                );
            }

            if ($index % 3 === 0) {
                EmployeeInsurance::updateOrCreate(
                    ['employee_id' => $employee->id, 'insurance_type' => 'BPJS_KES'],
                    [
                        'provider' => 'BPJS Kesehatan',
                        'membership_number' => '000'.str_pad((string) ($index + 1), 7, '0', STR_PAD_LEFT),
                        'insurance_class' => '2',
                        'start_date' => now()->subMonths(6)->startOfMonth()->toDateString(),
                        'employee_contribution' => 50000,
                        'company_contribution' => 100000,
                        'status' => 'ACTIVE',
                    ],
                );
            }

            $employees->push($employee);
        }

        $staffRole = Role::where('name', 'staff')->first();
        $managerRole = Role::where('name', 'branch-manager')->first();

        $this->createUser('kasir@dianmustika.test', 'Rina Kartika (Kasir Pusat)', $employees[0], $staffRole);
        $this->createUser('terapis@dianmustika.test', 'Dewi Anggraini (Terapis Senior)', $employees[1], $staffRole);
        $this->createUser('finance@dianmustika.test', 'Agus Pratama (Finance)', $employees[5], $staffRole);
        $this->createUser('hrd@dianmustika.test', 'Maya Sari (HRD Staff)', $employees[6], $staffRole);
        $this->createUser('manager.cabang@dianmustika.test', 'Fajar Nugroho (Manager Cabang)', $employees[7], $managerRole);

        return $employees;
    }

    protected function createUser(string $email, string $name, Employee $employee, ?Role $role): void
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'employee_id' => $employee->id,
                'status' => 'ACTIVE',
            ],
        );

        if ($role) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }
    }

    protected function attendanceAndOvertime(Collection $employees): void
    {
        $attendance = app(AttendanceService::class);

        foreach (range(0, 13) as $offset) {
            $date = now()->subDays($offset);

            if ($date->isSunday()) {
                continue;
            }

            foreach ($employees as $index => $employee) {
                $isAbsent = ($index + $offset) % 13 === 0;

                Attendance::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'attendance_date' => $date->toDateString(),
                    ],
                    [
                        'branch_id' => $employee->branch_id,
                        'check_in' => $isAbsent ? null : '08:'.str_pad((string) (45 + ($index % 15)), 2, '0', STR_PAD_LEFT),
                        'check_out' => $isAbsent ? null : '17:'.str_pad((string) (10 + ($index % 20)), 2, '0', STR_PAD_LEFT),
                        'status' => $isAbsent ? 'ABSENT' : (($index % 5 === 0) ? 'LATE' : 'PRESENT'),
                        'source' => 'MANUAL',
                    ]
                );
            }
        }

        // Approved overtime for past days
        foreach ($employees->take(4) as $index => $employee) {
            $date = now()->subDays(3 + $index);
            if ($date->isSunday()) {
                $date = $date->subDay();
            }

            $record = OvertimeRecord::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'overtime_date' => $date->toDateString(),
                ],
                [
                    'start_time' => '17:00:00',
                    'end_time' => '19:00:00',
                    'duration_minutes' => 120,
                    'status' => 'APPROVED',
                    'approved_by' => Auth::id(),
                ]
            );
        }

        // 2 PENDING overtime records waiting for approval in Pending Approvals Center
        if ($employees->count() >= 3) {
            OvertimeRecord::updateOrCreate(
                [
                    'employee_id' => $employees[1]->id, // Dewi Anggraini
                    'overtime_date' => now()->toDateString(),
                ],
                [
                    'start_time' => '17:00:00',
                    'end_time' => '19:30:00',
                    'duration_minutes' => 150,
                    'status' => 'PENDING',
                    'approved_by' => null,
                ]
            );

            OvertimeRecord::updateOrCreate(
                [
                    'employee_id' => $employees[2]->id, // Siti Nurhaliza
                    'overtime_date' => now()->subDay()->toDateString(),
                ],
                [
                    'start_time' => '17:00:00',
                    'end_time' => '19:00:00',
                    'duration_minutes' => 120,
                    'status' => 'PENDING',
                    'approved_by' => null,
                ]
            );
        }
    }

    protected function bookings(Collection $customers, Collection $services, Collection $packages, Collection $employees, Branch $branch1, Branch $branch2): void
    {
        if ($services->isEmpty()) {
            return;
        }

        $therapists = $employees->filter(fn ($e) => $e->position?->code === 'THERAPIST')->values();
        if ($therapists->isEmpty()) {
            $therapists = $employees;
        }

        // 1. Seed bookings for TODAY to populate /bookings/schedule live calendar
        $today = now()->toDateString();
        $todaySchedules = [
            ['BKG-TODAY-01', 0, '09:00:00', '10:00:00', 'COMPLETED', $services[0] ?? null, null, 0],
            ['BKG-TODAY-02', 1, '10:30:00', '12:00:00', 'IN_PROGRESS', null, $packages[0] ?? null, 1],
            ['BKG-TODAY-03', 2, '13:00:00', '14:30:00', 'CONFIRMED', $services[1] ?? null, null, 2],
            ['BKG-TODAY-04', 3, '14:30:00', '16:00:00', 'CONFIRMED', null, $packages[1] ?? null, 0],
            ['BKG-TODAY-05', 4, '16:30:00', '17:30:00', 'BOOKED', $services[2] ?? null, null, 3],
            ['BKG-TODAY-06', 5, '18:00:00', '19:30:00', 'BOOKED', $services[3] ?? null, null, 1],
        ];

        foreach ($todaySchedules as [$code, $custIdx, $start, $end, $status, $service, $package, $therapistIdx]) {
            $customer = $customers[$custIdx % $customers->count()];
            $therapist = $therapists[$therapistIdx % $therapists->count()];

            $booking = Booking::updateOrCreate(
                ['booking_number' => $code],
                [
                    'customer_id' => $customer->id,
                    'branch_id' => $branch1->id,
                    'booking_date' => $today,
                    'start_time' => $start,
                    'end_time' => $end,
                    'status' => $status,
                    'notes' => 'Reservasi jadwal hari ini: '.($package ? $package->name : $service?->name),
                    'created_by' => Auth::id(),
                ]
            );

            BookingItem::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'service_id' => $service?->id,
                    'package_id' => $package?->id,
                    'employee_id' => $therapist->id,
                    'scheduled_start' => $today.' '.$start,
                    'scheduled_end' => $today.' '.$end,
                    'status' => $status,
                    'notes' => 'Terapis bertugas: '.$therapist->full_name,
                ]
            );
        }

        // 2. Past and future bookings
        $statuses = ['BOOKED', 'CONFIRMED', 'COMPLETED', 'CANCELLED'];

        foreach (range(1, 10) as $i) {
            $customer = $customers[$i % $customers->count()];
            $therapist = $therapists[$i % $therapists->count()];
            $date = now()->addDays($i - 5);
            $start = $date->copy()->setTime(10 + ($i % 6), 0);
            $service = $services[$i % $services->count()];

            $booking = Booking::updateOrCreate(
                ['booking_number' => sprintf('BKG-D%03d', $i)],
                [
                    'customer_id' => $customer->id,
                    'branch_id' => $i % 4 === 0 ? $branch2->id : $branch1->id,
                    'booking_date' => $date->toDateString(),
                    'start_time' => $start->format('H:i:s'),
                    'end_time' => $start->copy()->addHour()->format('H:i:s'),
                    'status' => $statuses[$i % count($statuses)],
                    'notes' => 'Booking demo #'.$i,
                    'created_by' => Auth::id(),
                ],
            );

            BookingItem::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'service_id' => $service->id,
                    'package_id' => null,
                    'employee_id' => $therapist->id,
                    'scheduled_start' => $start,
                    'scheduled_end' => $start->copy()->addHour(),
                    'status' => $booking->status,
                    'notes' => 'Layanan perawatan: '.$service->name,
                ],
            );
        }
    }

    protected function sales(Collection $customers, Collection $products, Collection $services, Collection $packages, Collection $employees, Branch $branch1, Branch $branch2): void
    {
        if (SalesTransaction::count() >= 25) {
            return;
        }

        $sales = app(SalesService::class);
        $payments = app(PaymentService::class);
        $pricing = app(PricingService::class);

        $methods = PaymentMethod::all();
        $kas = FinancialAccount::where('account_code', 'CASH-01')->first();
        $bca = FinancialAccount::where('account_code', 'BANK-BCA')->first();
        $cashierIds = User::whereIn('email', ['admin@dianmustika.test', 'kasir@dianmustika.test'])->pluck('id')->all();

        if (empty($cashierIds)) {
            $cashierIds = [Auth::id()];
        }

        for ($i = 0; $i < 25; $i++) {
            $branch = $i % 5 === 0 ? $branch2 : $branch1;
            $date = now()->subDays(random_int(0, 28));
            $method = $methods->random();
            $account = $method->method_type === 'CASH' ? $kas : $bca;

            $items = [];
            $lineCount = random_int(1, 3);

            for ($k = 0; $k < $lineCount; $k++) {
                $choice = random_int(1, 3);

                if ($choice === 1 && $products->isNotEmpty()) {
                    $product = $products->random();
                    $items[] = [
                        'item_type' => 'PRODUCT',
                        'product_id' => $product->id,
                        'quantity' => random_int(1, 2),
                        'unit_price' => (float) $product->default_sell_price,
                    ];
                } elseif ($choice === 2 && $services->isNotEmpty()) {
                    $service = $services->random();
                    $items[] = [
                        'item_type' => 'SERVICE',
                        'service_id' => $service->id,
                        'employee_id' => $employees->random()->id,
                        'quantity' => 1,
                        'unit_price' => $pricing->servicePrice($service),
                    ];
                } elseif ($packages->isNotEmpty()) {
                    $package = $packages->random();
                    $items[] = [
                        'item_type' => 'PACKAGE',
                        'package_id' => $package->id,
                        'employee_id' => $employees->random()->id,
                        'quantity' => 1,
                        'unit_price' => (float) $package->price,
                    ];
                }
            }

            if (empty($items)) {
                continue;
            }

            $transaction = $sales->create([
                'branch_id' => $branch->id,
                'customer_id' => $customers->random()->id,
                'cashier_id' => $cashierIds[array_rand($cashierIds)],
                'transaction_date' => $date->toDateString(),
            ], $items);

            $payments->record($transaction, [
                'payment_method_id' => $method->id,
                'financial_account_id' => $account?->id,
                'amount' => $transaction->grand_total,
                'payment_date' => $date->toDateString(),
            ]);
        }
    }

    protected function salesReturns(): void
    {
        if (SalesReturn::count() > 0) {
            return;
        }

        $returns = app(SalesReturnService::class);

        $sales = SalesTransaction::whereHas('items', fn ($q) => $q->where('item_type', 'PRODUCT'))
            ->inRandomOrder()
            ->take(2)
            ->get();

        foreach ($sales as $sale) {
            $item = $sale->items()->where('item_type', 'PRODUCT')->first();

            if (! $item) {
                continue;
            }

            $returns->create($sale, [
                ['sales_transaction_item_id' => $item->id, 'quantity' => 1],
            ], [
                'return_date' => now()->subDays(2)->toDateString(),
                'reason' => 'Produk tidak cocok dengan jenis kulit',
            ]);
        }
    }

    protected function purchasing(Branch $branch, Warehouse $warehouse, Collection $products): void
    {
        $purchaseOrders = app(PurchaseOrderService::class);
        $goodsReceipts = app(GoodsReceiptService::class);

        $suppliers = Supplier::orderBy('id')->get();

        if ($suppliers->count() < 2 || $products->count() < 4) {
            return;
        }

        // PO 1: Approved & Goods Received
        if (! PurchaseOrder::where('notes', 'like', '%PO-DEMO-01%')->exists()) {
            $po1 = $purchaseOrders->create([
                'supplier_id' => $suppliers[0]->id,
                'branch_id' => $branch->id,
                'order_date' => now()->subDays(20)->toDateString(),
                'expected_date' => now()->subDays(15)->toDateString(),
                'notes' => 'PO-DEMO-01: Restock bulanan produk herbal skincare',
            ], [
                ['product_id' => $products[0]->id, 'quantity' => 20, 'unit_price' => (float) $products[0]->purchase_price],
                ['product_id' => $products[1]->id, 'quantity' => 15, 'unit_price' => (float) $products[1]->purchase_price],
            ]);

            $purchaseOrders->approve($po1);

            $goodsReceipts->create([
                'purchase_order_id' => $po1->id,
                'supplier_id' => $suppliers[0]->id,
                'branch_id' => $branch->id,
                'warehouse_id' => $warehouse->id,
                'receipt_date' => now()->subDays(18)->toDateString(),
            ], [
                ['product_id' => $products[0]->id, 'quantity' => 20, 'unit_cost' => (float) $products[0]->purchase_price],
                ['product_id' => $products[1]->id, 'quantity' => 15, 'unit_cost' => (float) $products[1]->purchase_price],
            ]);
        }

        // PO 2: Approved & Partially Received
        if (! PurchaseOrder::where('notes', 'like', '%PO-DEMO-02%')->exists()) {
            $po2 = $purchaseOrders->create([
                'supplier_id' => $suppliers[1]->id,
                'branch_id' => $branch->id,
                'order_date' => now()->subDays(8)->toDateString(),
                'expected_date' => now()->subDays(2)->toDateString(),
                'notes' => 'PO-DEMO-02: Penerimaan parsial essential oils',
            ], [
                ['product_id' => $products[2]->id, 'quantity' => 10, 'unit_price' => (float) $products[2]->purchase_price],
            ]);

            $purchaseOrders->approve($po2);

            $goodsReceipts->create([
                'purchase_order_id' => $po2->id,
                'supplier_id' => $suppliers[1]->id,
                'branch_id' => $branch->id,
                'warehouse_id' => $warehouse->id,
                'receipt_date' => now()->subDays(2)->toDateString(),
            ], [
                ['product_id' => $products[2]->id, 'quantity' => 5, 'unit_cost' => (float) $products[2]->purchase_price],
            ]);
        }

        // PO 3 & PO 4: DRAFT for Pending Approvals Center
        if (! PurchaseOrder::where('status', 'DRAFT')->exists()) {
            $purchaseOrders->create([
                'supplier_id' => $suppliers[0]->id,
                'branch_id' => $branch->id,
                'order_date' => now()->subDay()->toDateString(),
                'expected_date' => now()->addDays(5)->toDateString(),
                'notes' => 'Restock serum vitamin C dan facial wash (Menunggu Persetujuan)',
            ], [
                ['product_id' => $products[0]->id, 'quantity' => 25, 'unit_price' => (float) $products[0]->purchase_price],
                ['product_id' => $products[2]->id, 'quantity' => 15, 'unit_price' => (float) $products[2]->purchase_price],
            ]);

            $purchaseOrders->create([
                'supplier_id' => $suppliers[1]->id,
                'branch_id' => $branch->id,
                'order_date' => now()->toDateString(),
                'expected_date' => now()->addDays(7)->toDateString(),
                'notes' => 'Pengadaan minyak pijat herbal 250ml (Menunggu Persetujuan)',
            ], [
                ['product_id' => $products[3]->id, 'quantity' => 30, 'unit_price' => (float) $products[3]->purchase_price],
            ]);
        }
    }

    protected function transfersAndOpnames(Warehouse $wh1, Warehouse $wh2, Collection $products): void
    {
        $transfers = app(StockTransferService::class);
        $opnames = app(StockOpnameService::class);

        // 1. Completed Transfer & Opname
        if (! StockTransfer::where('status', 'COMPLETED')->exists()) {
            $transfer = $transfers->create([
                'from_warehouse_id' => $wh1->id,
                'to_warehouse_id' => $wh2->id,
                'transfer_date' => now()->subDays(10)->toDateString(),
                'notes' => 'Restock cabang selatan rutin (dummy)',
            ], [
                ['product_id' => $products[0]->id, 'quantity' => 5],
                ['product_id' => $products[1]->id, 'quantity' => 5],
            ]);

            $transfers->approve($transfer);
        }

        if (! StockOpname::where('status', 'COMPLETED')->exists()) {
            $systemQty = (float) InventoryStock::where('warehouse_id', $wh1->id)
                ->where('product_id', $products[0]->id)
                ->value('quantity');

            $opname = $opnames->create([
                'warehouse_id' => $wh1->id,
                'opname_date' => now()->subDays(5)->toDateString(),
                'notes' => 'Stock opname rutin bulanan (dummy)',
            ], [
                ['product_id' => $products[0]->id, 'physical_quantity' => $systemQty + 1],
                ['product_id' => $products[1]->id, 'physical_quantity' => null],
            ]);

            $opnames->approve($opname);
        }

        // 2. DRAFT Transfer & Opname for Pending Approvals Center
        if (! StockTransfer::where('status', 'DRAFT')->exists()) {
            $transfers->create([
                'from_warehouse_id' => $wh1->id,
                'to_warehouse_id' => $wh2->id,
                'transfer_date' => now()->toDateString(),
                'notes' => 'Permintaan stok tambahan cabang fatmawati (Menunggu Persetujuan)',
            ], [
                ['product_id' => $products[2]->id, 'quantity' => 6],
                ['product_id' => $products[3]->id, 'quantity' => 8],
            ]);
        }

        if (! StockOpname::where('status', 'DRAFT')->exists()) {
            $opnames->create([
                'warehouse_id' => $wh2->id,
                'opname_date' => now()->toDateString(),
                'notes' => 'Hasil stock opname triwulan Cabang Selatan (Menunggu Persetujuan)',
            ], [
                ['product_id' => $products[0]->id, 'physical_quantity' => 28],
                ['product_id' => $products[1]->id, 'physical_quantity' => 29],
            ]);
        }
    }

    protected function finance(Branch $branch): void
    {
        $expenses = app(ExpenseService::class);
        $bills = app(BillService::class);

        $categories = ExpenseCategory::orderBy('id')->get();
        $kas = FinancialAccount::where('account_code', 'CASH-01')->first();

        // Approved routine expenses
        $defs = [
            ['OPERASIONAL', 750000, 12, true, 'Pembelian linen dan perlengkapan spa harian'],
            ['UTILITAS', 1200000, 9, true, 'Pembayaran tagihan listrik PLN ruang perawatan'],
            ['PERAWATAN', 500000, 7, true, 'Servis berkala mesin diffuser aromaterapi'],
            ['LAINNYA', 250000, 5, true, 'Penggantian lampu hias lobby spa'],
            ['OPERASIONAL', 640000, 2, false, 'Pembelian disinfektan & laundry handuk spa (Menunggu Persetujuan)'],
            ['PERAWATAN', 980000, 1, false, 'Perbaikan unit AC ruang perawatan VIP 1 (Menunggu Persetujuan)'],
        ];

        foreach ($defs as [$code, $amount, $daysAgo, $shouldApprove, $desc]) {
            $category = $categories->firstWhere('code', $code) ?? $categories->first();

            if (! $category) {
                continue;
            }

            if (! Expense::where('description', $desc)->exists()) {
                $expense = $expenses->create([
                    'branch_id' => $branch->id,
                    'expense_category_id' => $category->id,
                    'amount' => $amount,
                    'expense_date' => now()->subDays($daysAgo)->toDateString(),
                    'financial_account_id' => $kas?->id,
                    'description' => $desc,
                ]);

                if ($shouldApprove) {
                    $expenses->approve($expense);
                }
            }
        }

        // Generate routine bills
        $previous = now()->subMonthNoOverflow();

        if (Bill::count() === 0) {
            $previousBills = $bills->generate([
                'period_start' => $previous->copy()->startOfMonth()->toDateString(),
                'period_end' => $previous->copy()->endOfMonth()->toDateString(),
                'due_date' => $previous->copy()->startOfMonth()->addDays(14)->toDateString(),
            ]);

            if (! empty($previousBills)) {
                $bills->markPaid($previousBills[0]);
            }

            $bills->generate([
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->endOfMonth()->toDateString(),
                'due_date' => now()->startOfMonth()->addDays(14)->toDateString(),
            ]);
        }
    }

    protected function bankReconciliation(): void
    {
        $bca = FinancialAccount::where('account_code', 'BANK-BCA')->first();

        if (! $bca) {
            return;
        }

        $rows = [
            ['DUMMY-BT-1', 'CREDIT', 2500000, 5, 'Pembayaran transaksi spa pelanggan'],
            ['DUMMY-BT-2', 'DEBIT', 750000, 4, 'Pembayaran tagihan Biznet Internet'],
            ['DUMMY-BT-3', 'CREDIT', 1850000, 3, 'Transfer paket wedding spa'],
            ['DUMMY-BT-4', 'DEBIT', 320000, 2, 'Biaya administrasi bank'],
        ];

        foreach ($rows as [$reference, $type, $amount, $daysAgo, $desc]) {
            BankTransaction::updateOrCreate(
                ['external_reference' => $reference],
                [
                    'financial_account_id' => $bca->id,
                    'transaction_date' => now()->subDays($daysAgo)->toDateString(),
                    'transaction_type' => $type,
                    'amount' => $amount,
                    'description' => $desc,
                    'reconciliation_status' => 'UNRECONCILED',
                ],
            );
        }

        $payment = Payment::whereHas('paymentMethod', fn ($q) => $q->where('code', 'TRANSFER'))
            ->orderByDesc('id')
            ->first();

        if ($payment) {
            $transaction = BankTransaction::updateOrCreate(
                ['external_reference' => 'DUMMY-BT-RECON'],
                [
                    'financial_account_id' => $bca->id,
                    'transaction_date' => $payment->payment_date,
                    'transaction_type' => 'CREDIT',
                    'amount' => $payment->amount,
                    'description' => 'Pembayaran pelanggan '.$payment->payment_number,
                    'reconciliation_status' => 'UNRECONCILED',
                ],
            );

            if ($transaction->reconciliation_status !== 'RECONCILED') {
                app(BankReconciliationService::class)->match($transaction, ['payment_id' => $payment->id]);
            }
        }
    }

    protected function payroll(Collection $employees): void
    {
        $payroll = app(PayrollService::class);

        // 1. Past Month: Closed Payroll with Generated Payslips
        $previous = now()->subMonthNoOverflow();
        $prevCode = 'PAY-'.$previous->format('Ym');

        $period = PayrollPeriod::updateOrCreate(
            ['code' => $prevCode],
            [
                'name' => 'Payroll '.$previous->translatedFormat('F Y'),
                'period_start' => $previous->copy()->startOfMonth()->toDateString(),
                'period_end' => $previous->copy()->endOfMonth()->toDateString(),
                'status' => 'OPEN',
            ],
        );

        if ($period->payrolls()->count() === 0) {
            $records = $payroll->generate($period);

            $template = PayslipTemplate::where('code', 'STANDARD')->first();
            $method = PaymentMethod::where('code', 'CASH')->first();
            $kas = FinancialAccount::where('account_code', 'CASH-01')->first();

            foreach ($records as $record) {
                $payroll->approve($record);
                $payroll->generatePayslip($record->fresh(), $template?->id);
                $payroll->pay($record->fresh(), [
                    'payment_method_id' => $method?->id,
                    'financial_account_id' => $kas?->id,
                    'payment_date' => now()->subDays(3)->toDateString(),
                ]);
            }

            $payroll->closePeriod($period->fresh());
        }

        // 2. Current Month: DRAFT Payroll for Pending Approvals Center
        $current = now();
        $currCode = 'PAY-'.$current->format('Ym');

        $currentPeriod = PayrollPeriod::updateOrCreate(
            ['code' => $currCode],
            [
                'name' => 'Payroll '.$current->translatedFormat('F Y'),
                'period_start' => $current->copy()->startOfMonth()->toDateString(),
                'period_end' => $current->copy()->endOfMonth()->toDateString(),
                'status' => 'OPEN',
            ],
        );

        if ($currentPeriod->payrolls()->count() === 0) {
            $currentRecords = $payroll->generate($currentPeriod);

            // Add a bonus adjustment to first employee's payroll
            if (! empty($currentRecords)) {
                PayrollAdjustment::create([
                    'payroll_id' => $currentRecords[0]->id,
                    'type' => 'EARNING',
                    'description' => 'Bonus Prestasi Layanan Spa',
                    'amount' => 500000,
                    'reason' => 'Pencapaian target kepuasan pelanggan bintang 5',
                    'created_by' => Auth::id(),
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);
            }
        }
    }

    protected function assets(Branch $branch1, Branch $branch2): void
    {
        $assets = app(AssetService::class);

        $defs = [
            ['ELEC', 'AC Split Daikin 2PK Inverter', 7200000, 'Ruang Perawatan VIP 1'],
            ['FURN', 'Kursi Pijat Elektrik Luxury Reflexology', 5500000, 'Ruang Reflexology'],
            ['ELEC', 'Komputer Kasir All-in-One POS', 8500000, 'Front Office Kasir'],
            ['EQPT', 'Sterilizer Alat Spa UV & Steam', 3200000, 'Ruang Steril'],
            ['FURN', 'Lemari Display Produk Spa Kayu Jati', 2400000, 'Lobby Utama'],
        ];

        foreach ($defs as $index => [$categoryCode, $name, $price, $location]) {
            $category = AssetCategory::where('code', $categoryCode)->first();

            if (! $category) {
                continue;
            }

            Asset::updateOrCreate(
                ['asset_code' => sprintf('AST-D%03d', $index + 1)],
                [
                    'asset_category_id' => $category->id,
                    'name' => $name,
                    'purchase_date' => now()->subMonths(10 - $index)->startOfMonth()->toDateString(),
                    'purchase_price' => $price,
                    'branch_id' => $branch1->id,
                    'location' => $location,
                    'condition' => $index === 4 ? 'FAIR' : 'GOOD',
                    'status' => 'ACTIVE',
                ]
            );
        }

        // 1. Approved asset movement
        $asset1 = Asset::where('asset_code', 'AST-D001')->first();
        if ($asset1 && AssetMovement::where('asset_id', $asset1->id)->count() === 0) {
            $movement1 = $assets->move($asset1, [
                'to_branch_id' => $branch2->id,
                'to_location' => 'Ruang Treatment Cabang Selatan',
                'movement_date' => now()->subDays(10)->toDateString(),
                'reason' => 'Relokasi aset fasilitas cabang baru',
            ]);

            $assets->approveMovement($movement1);
        }

        // 2. Pending approval asset movement for Pending Approvals Center
        $asset2 = Asset::where('asset_code', 'AST-D004')->first();
        if ($asset2 && AssetMovement::where('asset_id', $asset2->id)->count() === 0) {
            $assets->move($asset2, [
                'to_branch_id' => $branch2->id,
                'to_location' => 'Ruang Steril Cabang Selatan',
                'movement_date' => now()->toDateString(),
                'reason' => 'Peminjaman unit sterilizer cadangan (Menunggu Persetujuan)',
            ]);
            // Do NOT approve movement2, so approved_by remains null
        }
    }

    protected function media(): void
    {
        $admin = Auth::user();
        $asset = Asset::orderBy('id')->first();

        if (! $asset) {
            return;
        }

        $file = MediaFile::updateOrCreate(
            ['file_name' => 'dummy-asset-'.$asset->id.'.pdf'],
            [
                'original_name' => 'berita-acara-serah-terima-aset.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 24500,
                'storage_disk' => 'local',
                'storage_path' => 'media/dummy/berita-acara-serah-terima-aset.pdf',
                'uploaded_by' => $admin?->id,
                'uploaded_at' => now(),
                'checksum' => md5('dummy-asset-doc'),
            ],
        );

        MediaAttachment::updateOrCreate(
            [
                'media_file_id' => $file->id,
                'attachable_type' => Asset::class,
                'attachable_id' => $asset->id,
            ],
            ['collection_name' => 'documents'],
        );
    }

    protected function digitalAccounts(): void
    {
        $admin = Auth::user();

        $defs = [
            ['DOMAIN', 'dianmustika.com', now()->addDays(45)],
            ['HOSTING', 'Cloud VPS Production', now()->addDays(120)],
            ['EMAIL', 'Google Workspace Enterprise', now()->addDays(210)],
            ['SOCIAL', 'Instagram @dianmustika.official', now()->addDays(365)],
            ['SUBS', 'Aplikasi Kasir POS Pro Moka', now()->addDays(15)],
            ['SOCIAL', 'TikTok @dianmustika.spa', now()->addDays(365)],
        ];

        foreach ($defs as [$categoryCode, $name, $renewal]) {
            $category = DigitalAccountCategory::where('code', $categoryCode)->first();

            if (! $category) {
                continue;
            }

            DigitalAccount::updateOrCreate(
                ['name' => $name],
                [
                    'category_id' => $category->id,
                    'username' => strtolower(str_replace(' ', '', $name)),
                    'email' => 'admin@dianmustika.test',
                    'url' => 'https://'.strtolower(explode(' ', $name)[0]).'.dianmustika.test',
                    'owner_type' => User::class,
                    'owner_id' => $admin?->id,
                    'renewal_date' => $renewal->toDateString(),
                    'status' => 'ACTIVE',
                    'notes' => 'Akun operasional digital resmi Dian Mustika',
                ],
            );
        }
    }

    protected function auditLogs(): void
    {
        if (AuditLog::count() >= 25) {
            return;
        }

        $admin = User::where('email', 'admin@dianmustika.test')->first();
        $kasir = User::where('email', 'kasir@dianmustika.test')->first();
        $manager = User::where('email', 'manager.cabang@dianmustika.test')->first();

        $logs = [
            ['LOGIN', null, [], [], $admin?->id, now()->subDays(4)->setTime(8, 30)],
            ['CREATE', PurchaseOrder::class, [], ['po_number' => 'PO-202611-0001', 'total' => 1500000], $admin?->id, now()->subDays(4)->setTime(9, 15)],
            ['APPROVE', PurchaseOrder::class, ['status' => 'DRAFT'], ['status' => 'APPROVED'], $admin?->id, now()->subDays(4)->setTime(9, 30)],
            ['LOGIN', null, [], [], $kasir?->id, now()->subDays(3)->setTime(8, 45)],
            ['CREATE', SalesTransaction::class, [], ['invoice_number' => 'INV-202611-0001', 'total' => 320000], $kasir?->id, now()->subDays(3)->setTime(10, 20)],
            ['CREATE', Booking::class, [], ['booking_number' => 'BKG-TODAY-01', 'customer' => 'Citra Dewi'], $kasir?->id, now()->subDays(2)->setTime(11, 0)],
            ['UPDATE', Booking::class, ['status' => 'CONFIRMED'], ['status' => 'COMPLETED'], $kasir?->id, now()->subDays(2)->setTime(12, 30)],
            ['CREATE', Expense::class, [], ['expense_number' => 'EXP-202611-0001', 'amount' => 750000], $admin?->id, now()->subDays(2)->setTime(14, 0)],
            ['APPROVE', Expense::class, ['status' => 'DRAFT'], ['status' => 'APPROVED'], $admin?->id, now()->subDays(2)->setTime(14, 30)],
            ['LOGIN', null, [], [], $manager?->id, now()->subDay()->setTime(9, 0)],
            ['CREATE', StockTransfer::class, [], ['from' => 'Gudang Pusat', 'to' => 'Gudang Cabang'], $manager?->id, now()->subDay()->setTime(10, 15)],
            ['APPROVE', OvertimeRecord::class, ['status' => 'PENDING'], ['status' => 'APPROVED'], $admin?->id, now()->subDay()->setTime(16, 0)],
            ['LOGIN', null, [], [], $admin?->id, now()->setTime(8, 15)],
            ['APPROVE', AssetMovement::class, ['approved_by' => null], ['approved_by' => $admin?->id], $admin?->id, now()->setTime(8, 45)],
            ['CREATE', StockOpname::class, [], ['opname_number' => 'SOP-202611-0001', 'warehouse' => 'Gudang Utama'], $admin?->id, now()->setTime(9, 30)],
        ];

        foreach ($logs as [$action, $type, $old, $new, $userId, $timestamp]) {
            AuditLog::create([
                'user_id' => $userId,
                'action' => $action,
                'auditable_type' => $type,
                'auditable_id' => 1,
                'old_values' => $old ?: null,
                'new_values' => $new ?: null,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
                'created_at' => $timestamp,
            ]);
        }
    }
}
