<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\BankTransaction;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Branch;
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
use App\Models\ExpenseCategory;
use App\Models\FinancialAccount;
use App\Models\InventoryStock;
use App\Models\MediaAttachment;
use App\Models\MediaFile;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PayrollPeriod;
use App\Models\PayslipTemplate;
use App\Models\Position;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\SalaryComponent;
use App\Models\SalesTransaction;
use App\Models\Service;
use App\Models\Shift;
use App\Models\Supplier;
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
 * Seed realistic, fully-related dummy data for local development.
 *
 * Run standalone with: php artisan db:seed --class=DummyDataSeeder
 * It is called automatically by DatabaseSeeder only in the "local" environment.
 */
class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Customer::where('customer_code', 'CUST-D001')->exists()) {
            $this->command?->warn('Dummy data sudah ada. Seeder dilewati agar tidak duplikat.');

            return;
        }

        $admin = User::where('email', 'admin@dianmustika.test')->first();

        if (! $admin) {
            $this->command?->error('Admin user belum tersedia. Jalankan DatabaseSeeder lebih dulu.');

            return;
        }

        Auth::login($admin);

        DB::transaction(function () {
            [$branch1, $branch2, $wh1, $wh2] = $this->branches();
            $products = $this->products();
            $this->initialStock($wh1, $wh2, $products);
            $services = Service::where('status', 'ACTIVE')->get();
            $customers = $this->customers();
            $employees = $this->employees($branch1, $branch2);
            $this->attendanceAndOvertime($employees);
            $this->bookings($customers, $services, $employees, $branch1);
            $this->sales($customers, $products, $services, $employees, $branch1, $branch2);
            $this->salesReturns();
            $this->purchasing($branch1, $wh1);
            $this->transfersAndOpnames($wh1, $wh2, $products);
            $this->finance($branch1);
            $this->bankReconciliation();
            $this->payroll();
            $this->assets($branch1, $branch2);
            $this->media();
            $this->digitalAccounts();
            app(AlertService::class)->scanAll();
        });

        $this->command?->info('Dummy data berhasil dibuat.');
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
            ['company_id' => $company->id, 'name' => 'Cabang Selatan', 'branch_type' => 'BRANCH', 'status' => 'ACTIVE'],
        );

        $wh1 = Warehouse::where('branch_id', $branch1->id)->orderBy('id')->firstOrFail();

        $wh2 = Warehouse::updateOrCreate(
            ['code' => 'WH-DM-CABANG'],
            ['branch_id' => $branch2->id, 'name' => 'Gudang Cabang Selatan', 'warehouse_type' => 'MAIN', 'status' => 'ACTIVE'],
        );

        return [$branch1, $branch2, $wh1, $wh2];
    }

    protected function products(): Collection
    {
        $category = ProductCategory::where('code', 'RETAIL')->first() ?? ProductCategory::firstOrFail();
        $unit = Unit::where('code', 'PCS')->first() ?? Unit::firstOrFail();

        $rows = [
            ['PRD-004', 'Toner Wajah', 30000, 55000],
            ['PRD-005', 'Masker Clay', 40000, 75000],
            ['PRD-006', 'Body Lotion', 35000, 65000],
            ['PRD-007', 'Hair Tonic', 45000, 85000],
            ['PRD-008', 'Retinol Serum', 95000, 165000],
        ];

        foreach ($rows as [$sku, $name, $purchase, $sell]) {
            Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $category->id,
                    'unit_id' => $unit->id,
                    'name' => $name,
                    'purchase_price' => $purchase,
                    'default_sell_price' => $sell,
                    'minimum_stock' => 5,
                    'status' => 'ACTIVE',
                ],
            );
        }

        return Product::where('status', 'ACTIVE')->orderBy('id')->get();
    }

    protected function initialStock(Warehouse $wh1, Warehouse $wh2, Collection $products): void
    {
        $inventory = app(InventoryService::class);

        foreach ($products as $product) {
            $inventory->adjust($wh1->id, $product->id, 100, [
                'movement_type' => 'INITIAL',
                'notes' => 'Saldo awal (dummy)',
            ]);

            $inventory->adjust($wh2->id, $product->id, 25, [
                'movement_type' => 'INITIAL',
                'notes' => 'Saldo awal (dummy)',
            ]);
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
                    'customer_type' => $index % 5 === 0 ? 'MEMBER' : 'REGULAR',
                    'source' => 'WALK_IN',
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

        $this->createUser('kasir@dianmustika.test', 'Kasir Pusat', $employees[0], $staffRole);
        $this->createUser('terapis@dianmustika.test', 'Dewi Anggraini', $employees[1], $staffRole);
        $this->createUser('manager.cabang@dianmustika.test', 'Fajar Nugroho', $employees[7], $managerRole);

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
        $overtimeDays = 0;

        foreach (range(0, 11) as $offset) {
            $date = now()->subDays($offset);

            if ($date->isWeekend()) {
                continue;
            }

            foreach ($employees as $index => $employee) {
                $isAbsent = ($index + $offset) % 11 === 0;

                $attendance->record([
                    'employee_id' => $employee->id,
                    'branch_id' => $employee->branch_id,
                    'attendance_date' => $date->toDateString(),
                    'check_in' => $isAbsent ? null : '08:0'.($index % 9),
                    'check_out' => $isAbsent ? null : '16:1'.($index % 9),
                    'status' => $isAbsent ? 'ABSENT' : 'PRESENT',
                    'source' => 'MANUAL',
                ]);
            }
        }

        foreach ($employees->take(4) as $index => $employee) {
            $date = now()->subDays(3 + $index);

            if ($date->isWeekend()) {
                $date = $date->subDays(2);
            }

            $record = $attendance->recordOvertime([
                'employee_id' => $employee->id,
                'overtime_date' => $date->toDateString(),
                'start_time' => '16:00',
                'end_time' => '18:00',
            ]);

            $attendance->approveOvertime($record);
            $overtimeDays++;
        }
    }

    protected function bookings(Collection $customers, Collection $services, Collection $employees, Branch $branch): void
    {
        if ($services->isEmpty()) {
            return;
        }

        $statuses = ['BOOKED', 'BOOKED', 'COMPLETED', 'CANCELLED'];

        foreach (range(1, 12) as $i) {
            $customer = $customers->random();
            $date = now()->addDays($i - 6);
            $start = $date->copy()->setTime(10 + ($i % 6), 0);

            $booking = Booking::updateOrCreate(
                ['booking_number' => sprintf('BKG-D%03d', $i)],
                [
                    'customer_id' => $customer->id,
                    'branch_id' => $branch->id,
                    'booking_date' => $date->toDateString(),
                    'start_time' => $start->format('H:i:s'),
                    'end_time' => $start->copy()->addHour()->format('H:i:s'),
                    'status' => $statuses[$i % count($statuses)],
                    'notes' => 'Booking dummy #'.$i,
                ],
            );

            $service = $services->random();

            BookingItem::updateOrCreate(
                ['booking_id' => $booking->id, 'service_id' => $service->id],
                [
                    'employee_id' => $employees->random()->id,
                    'scheduled_start' => $start,
                    'scheduled_end' => $start->copy()->addHour(),
                    'status' => $booking->status,
                ],
            );
        }
    }

    protected function sales(Collection $customers, Collection $products, Collection $services, Collection $employees, Branch $branch1, Branch $branch2): void
    {
        $sales = app(SalesService::class);
        $payments = app(PaymentService::class);
        $pricing = app(PricingService::class);

        $methods = PaymentMethod::all();
        $kas = FinancialAccount::where('account_code', 'CASH-01')->first();
        $bca = FinancialAccount::where('account_code', 'BANK-BCA')->first();
        $cashierIds = User::whereIn('email', ['admin@dianmustika.test', 'kasir@dianmustika.test'])->pluck('id')->all();

        if ($products->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 30; $i++) {
            $branch = $i % 5 === 0 ? $branch2 : $branch1;
            $date = now()->subDays(random_int(0, 29));
            $method = $methods->random();
            $account = $method->method_type === 'CASH' ? $kas : $bca;

            $items = [];
            $lineCount = random_int(1, 3);

            for ($k = 0; $k < $lineCount; $k++) {
                if (random_int(0, 1) === 1 || $services->isEmpty()) {
                    $product = $products->random();
                    $items[] = [
                        'item_type' => 'PRODUCT',
                        'product_id' => $product->id,
                        'quantity' => random_int(1, 2),
                        'unit_price' => (float) $product->default_sell_price,
                    ];
                } else {
                    $service = $services->random();
                    $items[] = [
                        'item_type' => 'SERVICE',
                        'service_id' => $service->id,
                        'employee_id' => $employees->random()->id,
                        'quantity' => 1,
                        'unit_price' => $pricing->servicePrice($service),
                    ];
                }
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
                'reason' => 'Produk tidak cocok',
            ]);
        }
    }

    protected function purchasing(Branch $branch, Warehouse $warehouse): void
    {
        $purchaseOrders = app(PurchaseOrderService::class);
        $goodsReceipts = app(GoodsReceiptService::class);

        $suppliers = Supplier::orderBy('id')->get();
        $products = Product::orderBy('id')->take(6)->get();

        if ($suppliers->count() < 2 || $products->count() < 3) {
            return;
        }

        $po1 = $purchaseOrders->create([
            'supplier_id' => $suppliers[0]->id,
            'branch_id' => $branch->id,
            'order_date' => now()->subDays(20)->toDateString(),
            'expected_date' => now()->subDays(15)->toDateString(),
            'notes' => 'Restock bulanan (dummy)',
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

        $po2 = $purchaseOrders->create([
            'supplier_id' => $suppliers[1]->id,
            'branch_id' => $branch->id,
            'order_date' => now()->subDays(8)->toDateString(),
            'expected_date' => now()->subDays(2)->toDateString(),
            'notes' => 'Penerimaan sebagian (dummy)',
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
            ['product_id' => $products[2]->id, 'quantity' => 4, 'unit_cost' => (float) $products[2]->purchase_price],
        ]);

        $purchaseOrders->create([
            'supplier_id' => $suppliers[1]->id,
            'branch_id' => $branch->id,
            'order_date' => now()->subDay()->toDateString(),
            'expected_date' => now()->addDays(7)->toDateString(),
            'notes' => 'PO draft menunggu persetujuan (dummy)',
        ], [
            ['product_id' => $products[3]->id, 'quantity' => 12, 'unit_price' => (float) $products[3]->purchase_price],
        ]);
    }

    protected function transfersAndOpnames(Warehouse $wh1, Warehouse $wh2, Collection $products): void
    {
        $transfers = app(StockTransferService::class);
        $opnames = app(StockOpnameService::class);

        $transfer = $transfers->create([
            'from_warehouse_id' => $wh1->id,
            'to_warehouse_id' => $wh2->id,
            'transfer_date' => now()->subDays(10)->toDateString(),
            'notes' => 'Restock cabang (dummy)',
        ], [
            ['product_id' => $products[0]->id, 'quantity' => 5],
            ['product_id' => $products[1]->id, 'quantity' => 5],
        ]);

        $transfers->approve($transfer);

        $systemQty = (float) InventoryStock::where('warehouse_id', $wh1->id)
            ->where('product_id', $products[0]->id)
            ->value('quantity');

        $opname = $opnames->create([
            'warehouse_id' => $wh1->id,
            'opname_date' => now()->subDays(5)->toDateString(),
            'notes' => 'Stock opname rutin (dummy)',
        ], [
            ['product_id' => $products[0]->id, 'physical_quantity' => $systemQty + 1],
            ['product_id' => $products[1]->id, 'physical_quantity' => null],
        ]);

        $opnames->approve($opname);
    }

    protected function finance(Branch $branch): void
    {
        $expenses = app(ExpenseService::class);
        $bills = app(BillService::class);

        $categories = ExpenseCategory::orderBy('id')->get();
        $kas = FinancialAccount::where('account_code', 'CASH-01')->first();

        $defs = [
            ['OPERASIONAL', 750000, 12],
            ['UTILITAS', 1200000, 9],
            ['PERAWATAN', 500000, 7],
            ['LAINNYA', 250000, 5],
            ['OPERASIONAL', 640000, 3],
            ['UTILITAS', 980000, 1],
        ];

        foreach ($defs as $index => [$code, $amount, $daysAgo]) {
            $category = $categories->firstWhere('code', $code) ?? $categories->first();

            if (! $category) {
                continue;
            }

            $expense = $expenses->create([
                'branch_id' => $branch->id,
                'expense_category_id' => $category->id,
                'amount' => $amount,
                'expense_date' => now()->subDays($daysAgo)->toDateString(),
                'financial_account_id' => $kas?->id,
                'description' => 'Pengeluaran dummy '.$category->name,
            ]);

            if ($index < 5) {
                $expenses->approve($expense);
            }
        }

        $previous = now()->subMonthNoOverflow();

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

    protected function bankReconciliation(): void
    {
        $bca = FinancialAccount::where('account_code', 'BANK-BCA')->first();

        if (! $bca) {
            return;
        }

        $rows = [
            ['DUMMY-BT-1', 'CREDIT', 2500000, 5],
            ['DUMMY-BT-2', 'DEBIT', 750000, 4],
            ['DUMMY-BT-3', 'CREDIT', 1850000, 3],
            ['DUMMY-BT-4', 'DEBIT', 320000, 2],
        ];

        foreach ($rows as [$reference, $type, $amount, $daysAgo]) {
            BankTransaction::updateOrCreate(
                ['external_reference' => $reference],
                [
                    'financial_account_id' => $bca->id,
                    'transaction_date' => now()->subDays($daysAgo)->toDateString(),
                    'transaction_type' => $type,
                    'amount' => $amount,
                    'description' => 'Mutasi bank dummy '.$reference,
                    'reconciliation_status' => 'UNRECONCILED',
                ],
            );
        }

        $payment = Payment::whereHas('paymentMethod', fn ($q) => $q->where('code', 'TRANSFER'))
            ->orderByDesc('id')
            ->first();

        if (! $payment) {
            return;
        }

        $transaction = BankTransaction::updateOrCreate(
            ['external_reference' => 'DUMMY-BT-RECON'],
            [
                'financial_account_id' => $bca->id,
                'transaction_date' => $payment->payment_date,
                'transaction_type' => 'CREDIT',
                'amount' => $payment->amount,
                'description' => 'Pembayaran pelanggan (dummy)',
                'reconciliation_status' => 'UNRECONCILED',
            ],
        );

        if ($transaction->reconciliation_status !== 'RECONCILED') {
            app(BankReconciliationService::class)->match($transaction, ['payment_id' => $payment->id]);
        }
    }

    protected function payroll(): void
    {
        $previous = now()->subMonthNoOverflow();

        $period = PayrollPeriod::updateOrCreate(
            ['code' => 'PAY-'.$previous->format('Ym')],
            [
                'name' => 'Payroll '.$previous->translatedFormat('F Y'),
                'period_start' => $previous->copy()->startOfMonth()->toDateString(),
                'period_end' => $previous->copy()->endOfMonth()->toDateString(),
                'status' => 'OPEN',
            ],
        );

        $payroll = app(PayrollService::class);
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

    protected function assets(Branch $branch1, Branch $branch2): void
    {
        $assets = app(AssetService::class);

        $defs = [
            ['ELEC', 'AC Split 2PK', 6500000, 'Ruang Perawatan 1'],
            ['FURN', 'Kursi Pijat Elektrik', 4200000, 'Ruang Massage 1'],
            ['ELEC', 'Komputer Kasir', 8500000, 'Front Office'],
            ['EQPT', 'Sterilizer Alat', 2800000, 'Ruang Steril'],
            ['FURN', 'Lemari Kabinet', 1900000, 'Gudang'],
        ];

        foreach ($defs as $index => [$categoryCode, $name, $price, $location]) {
            $category = AssetCategory::where('code', $categoryCode)->first();

            if (! $category) {
                continue;
            }

            $assets->create([
                'asset_code' => sprintf('AST-D%03d', $index + 1),
                'asset_category_id' => $category->id,
                'name' => $name,
                'purchase_date' => now()->subMonths(10 - $index)->startOfMonth()->toDateString(),
                'purchase_price' => $price,
                'branch_id' => $branch1->id,
                'location' => $location,
                'condition' => $index === 4 ? 'FAIR' : 'GOOD',
                'status' => 'ACTIVE',
            ]);
        }

        $asset = Asset::orderBy('id')->first();

        if ($asset) {
            $movement = $assets->move($asset, [
                'to_branch_id' => $branch2->id,
                'to_location' => 'Cabang Selatan',
                'movement_date' => now()->subDays(4)->toDateString(),
                'reason' => 'Relokasi aset (dummy)',
            ]);

            $assets->approveMovement($movement);
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
                'original_name' => 'berita-acara-aset.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 20480,
                'storage_disk' => 'local',
                'storage_path' => 'media/dummy/berita-acara-aset.pdf',
                'uploaded_by' => $admin?->id,
                'uploaded_at' => now(),
                'checksum' => md5('dummy-asset'),
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
            ['DOMAIN', 'dianmustika.com', now()->addDays(20)],
            ['HOSTING', 'Hosting Utama', now()->addDays(120)],
            ['EMAIL', 'Google Workspace', now()->addDays(200)],
            ['SOCIAL', 'Instagram @dianmustika', now()->addDays(300)],
            ['SUBS', 'Aplikasi Kasir', now()->addDays(15)],
            ['SOCIAL', 'Facebook Page', now()->addDays(365)],
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
                ],
            );
        }
    }
}
