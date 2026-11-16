<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeInsurance;
use App\Models\User;
use Database\Seeders\DummyDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrBpjsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->seed(DummyDataSeeder::class);
        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
    }

    public function test_hr_bpjs_index_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->admin)->get(route('hr.bpjs.index'));

        $response->assertOk();
        $response->assertSee('Rekapitulasi BPJS Karyawan');
        $response->assertSee('BPJS Kesehatan');
        $response->assertSee('BPJS Ketenagakerjaan');
        $response->assertViewHas('employees');
        $response->assertViewHas('metrics');
    }

    public function test_hr_bpjs_filter_by_bpjs_status(): void
    {
        $emp = Employee::firstOrFail();

        // Ensure employee has both BPJS
        EmployeeInsurance::updateOrCreate(
            ['employee_id' => $emp->id, 'insurance_type' => 'BPJS_KES'],
            [
                'provider' => 'BPJS Kesehatan',
                'membership_number' => '0009988776',
                'status' => 'ACTIVE',
            ]
        );
        EmployeeInsurance::updateOrCreate(
            ['employee_id' => $emp->id, 'insurance_type' => 'BPJS_TK'],
            [
                'provider' => 'BPJS Ketenagakerjaan',
                'membership_number' => '2209988776',
                'status' => 'ACTIVE',
            ]
        );

        $response = $this->actingAs($this->admin)->get(route('hr.bpjs.index', [
            'bpjs_status' => 'BOTH',
        ]));

        $response->assertOk();
        $response->assertSee($emp->full_name);
        $response->assertSee('0009988776');
        $response->assertSee('2209988776');
    }

    public function test_hr_bpjs_update_saves_bpjs_kes_and_tk_data(): void
    {
        $emp = Employee::firstOrFail();

        $response = $this->actingAs($this->admin)->put(route('hr.bpjs.update', $emp), [
            'bpjs_kes_number' => '0001234567890',
            'bpjs_kes_class' => '1',
            'bpjs_kes_start_date' => '2026-01-01',
            'bpjs_kes_emp_contrib' => 50000,
            'bpjs_kes_comp_contrib' => 200000,
            'bpjs_kes_status' => 'ACTIVE',

            'bpjs_tk_number' => '2209876543210',
            'bpjs_tk_start_date' => '2026-01-01',
            'bpjs_tk_emp_contrib' => 80000,
            'bpjs_tk_comp_contrib' => 220000,
            'bpjs_tk_status' => 'ACTIVE',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('employee_insurances', [
            'employee_id' => $emp->id,
            'insurance_type' => 'BPJS_KES',
            'membership_number' => '0001234567890',
            'insurance_class' => '1',
            'employee_contribution' => 50000,
            'company_contribution' => 200000,
            'status' => 'ACTIVE',
        ]);

        $this->assertDatabaseHas('employee_insurances', [
            'employee_id' => $emp->id,
            'insurance_type' => 'BPJS_TK',
            'membership_number' => '2209876543210',
            'employee_contribution' => 80000,
            'company_contribution' => 220000,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_hr_bpjs_search_by_membership_number(): void
    {
        $emp = Employee::firstOrFail();
        $uniqueNum = '99988877711';

        EmployeeInsurance::updateOrCreate(
            ['employee_id' => $emp->id, 'insurance_type' => 'BPJS_KES'],
            [
                'provider' => 'BPJS Kesehatan',
                'membership_number' => $uniqueNum,
                'status' => 'ACTIVE',
            ]
        );

        $response = $this->actingAs($this->admin)->get(route('hr.bpjs.index', [
            'search' => $uniqueNum,
        ]));

        $response->assertOk();
        $response->assertSee($emp->full_name);
        $response->assertSee($uniqueNum);
    }

    public function test_hr_bpjs_export_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('hr.bpjs.export'));

        $response->assertOk();
        $this->assertStringStartsWith('text/csv', $response->headers->get('content-type'));
    }
}
