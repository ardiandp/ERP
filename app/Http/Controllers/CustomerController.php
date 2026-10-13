<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return view('customers.index', ['customers' => Customer::latest()->paginate(15)]);
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_code' => ['required', 'unique:customers,customer_code'],
            'name' => ['required', 'string'],
            'gender' => ['nullable', 'in:M,F'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],
            'customer_type' => ['required', 'string'],
            'source' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
        $customer = Customer::create($data);
        AuditLog::record('create', $customer, [], $data);

        return redirect()->route('customers.index')->with('success', 'Customer dibuat.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'customer_code' => ['required', 'unique:customers,customer_code,'.$customer->id],
            'name' => ['required', 'string'],
            'gender' => ['nullable', 'in:M,F'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],
            'customer_type' => ['required', 'string'],
            'source' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
        $customer->update($data);
        AuditLog::record('update', $customer, [], $data);

        return redirect()->route('customers.index')->with('success', 'Customer diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        AuditLog::record('delete', $customer, $customer->toArray());
        $customer->delete();

        return back()->with('success', 'Customer dihapus.');
    }
}
