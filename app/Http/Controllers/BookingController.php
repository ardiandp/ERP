<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::with('customer', 'branch')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('bookings.index', ['bookings' => $bookings]);
    }

    public function create()
    {
        return view('bookings.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['booking_number'] = DocumentNumber::generate('BKG', Booking::class, 'booking_number', $data['booking_date'] ?? null);
        $data['created_by'] = auth()->id();

        $booking = Booking::create($data);

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking dibuat.');
    }

    public function show(Booking $booking)
    {
        $booking->load('customer', 'branch', 'items.service', 'items.package', 'items.employee');

        return view('bookings.show', array_merge(['booking' => $booking], $this->formData()));
    }

    public function edit(Booking $booking)
    {
        return view('bookings.edit', array_merge(['booking' => $booking], $this->formData()));
    }

    public function update(Request $request, Booking $booking)
    {
        $booking->update($this->validated($request));

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking diperbarui.');
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return redirect()->route('bookings.index')->with('success', 'Booking dihapus.');
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['required', 'in:BOOKED,CONFIRMED,IN_PROGRESS,COMPLETED,CANCELLED'],
        ]);

        $booking->update($data);

        return back()->with('success', 'Status booking diperbarui.');
    }

    public function storeItem(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'service_id' => ['nullable', 'exists:services,id'],
            'package_id' => ['nullable', 'exists:service_packages,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'scheduled_start' => ['nullable', 'date'],
            'scheduled_end' => ['nullable', 'date', 'after_or_equal:scheduled_start'],
            'notes' => ['nullable', 'string'],
        ]);

        if (empty($data['service_id']) && empty($data['package_id'])) {
            return back()->withErrors(['service_id' => 'Pilih layanan atau paket.']);
        }

        $booking->items()->create($data);

        return back()->with('success', 'Item booking ditambahkan.');
    }

    public function destroyItem(Booking $booking, BookingItem $item)
    {
        abort_unless($item->booking_id === $booking->id, 404);
        $item->delete();

        return back()->with('success', 'Item booking dihapus.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'booking_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after_or_equal:start_time'],
            'status' => ['required', 'in:BOOKED,CONFIRMED,IN_PROGRESS,COMPLETED,CANCELLED'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    protected function formData(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(),
            'branches' => Branch::where('status', 'ACTIVE')->orderBy('name')->get(),
            'services' => Service::where('status', 'ACTIVE')->orderBy('name')->get(),
            'packages' => ServicePackage::where('status', 'ACTIVE')->orderBy('name')->get(),
            'employees' => Employee::orderBy('full_name')->get(),
        ];
    }
}
