<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
        $this->branch = Branch::firstOrFail();
    }

    public function test_booking_schedule_renders_timeline(): void
    {
        $customer = Customer::create([
            'customer_code' => 'CUST-SCH-01',
            'name' => 'Pelanggan Cantik',
            'branch_id' => $this->branch->id,
            'phone' => '081234567890',
        ]);

        $today = now()->toDateString();

        $booking = Booking::create([
            'booking_number' => 'BKG-SCH-001',
            'customer_id' => $customer->id,
            'branch_id' => $this->branch->id,
            'booking_date' => $today,
            'start_time' => '10:00',
            'end_time' => '11:30',
            'status' => 'CONFIRMED',
            'notes' => 'Treatment Facial Gold',
        ]);

        $response = $this->actingAs($this->admin)->get(route('bookings.schedule', ['date' => $today]));

        $response->assertOk();
        $response->assertSee('Jadwal & Kalender Reservasi Treatment');
        $response->assertSee('Pelanggan Cantik');
        $response->assertSee('BKG-SCH-001');
        $response->assertSee('10:00');
        $response->assertSee('CONFIRMED');
    }

    public function test_booking_schedule_filters_by_branch(): void
    {
        $response = $this->actingAs($this->admin)->get(route('bookings.schedule', [
            'date' => now()->toDateString(),
            'branch_id' => $this->branch->id,
        ]));

        $response->assertOk();
        $response->assertSee('Total Reservasi');
    }
}
