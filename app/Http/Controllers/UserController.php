<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'employee.branch'])->latest('id')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $employees = Employee::orderBy('full_name')->get();

        return view('users.create', compact('roles', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'employee_id' => $validated['employee_id'] ?? null,
            'status' => $validated['status'],
        ]);

        if (! empty($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        AuditLog::record('create', $user, [], [
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status,
        ]);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $employees = Employee::orderBy('full_name')->get();

        return view('users.edit', compact('user', 'roles', 'employees'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);

        $oldData = $user->only(['name', 'email', 'employee_id', 'status']);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_id' => $validated['employee_id'] ?? null,
            'status' => $validated['status'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $user->update($updateData);
        $user->roles()->sync($validated['roles'] ?? []);

        AuditLog::record('update', $user, $oldData, $updateData);

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function toggleStatus(User $user)
    {
        $oldStatus = $user->status;
        $newStatus = $user->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $user->update(['status' => $newStatus]);

        AuditLog::record('update', $user, ['status' => $oldStatus], ['status' => $newStatus]);

        return back()->with('success', "Status pengguna {$user->name} berhasil diubah menjadi {$newStatus}.");
    }
}
