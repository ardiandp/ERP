<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DigitalAccount;
use App\Models\DigitalAccountCategory;
use Illuminate\Http\Request;

class DigitalAccountController extends Controller
{
    public function index()
    {
        return view('digital-accounts.index', [
            'accounts' => DigitalAccount::with('category')->latest('id')->paginate(15),
        ]);
    }

    public function create()
    {
        return view('digital-accounts.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $account = DigitalAccount::create($data);
        AuditLog::record('create', $account, [], $data);

        return redirect()->route('digital-accounts.index')->with('success', 'Akun digital dibuat.');
    }

    public function edit(DigitalAccount $digital_account)
    {
        return view('digital-accounts.edit', array_merge(['account' => $digital_account], $this->formData()));
    }

    public function update(Request $request, DigitalAccount $digital_account)
    {
        $data = $this->validated($request, $digital_account);
        $digital_account->update($data);
        AuditLog::record('update', $digital_account, [], $data);

        return redirect()->route('digital-accounts.index')->with('success', 'Akun digital diperbarui.');
    }

    public function destroy(DigitalAccount $digital_account)
    {
        AuditLog::record('delete', $digital_account, $digital_account->toArray());
        $digital_account->delete();

        return redirect()->route('digital-accounts.index')->with('success', 'Akun digital dihapus.');
    }

    protected function validated(Request $request, ?DigitalAccount $account = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:digital_account_categories,id'],
            'name' => ['required', 'string'],
            'username' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'url' => ['nullable', 'string'],
            'renewal_date' => ['nullable', 'date'],
            'status' => ['required', 'in:ACTIVE,INACTIVE,EXPIRED'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    protected function formData(): array
    {
        return [
            'categories' => DigitalAccountCategory::orderBy('name')->get(),
        ];
    }
}
