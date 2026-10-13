<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        return view('positions.index', ['positions' => Position::with('department')->latest()->paginate(15)]);
    }

    public function create()
    {
        return view('positions.create', ['departments' => Department::all()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'code' => ['required', 'unique:positions,code'],
            'name' => ['required', 'string'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
        $position = Position::create($data);
        AuditLog::record('create', $position, [], $data);

        return redirect()->route('positions.index')->with('success', 'Position dibuat.');
    }

    public function edit(Position $position)
    {
        return view('positions.edit', ['position' => $position, 'departments' => Department::all()]);
    }

    public function update(Request $request, Position $position)
    {
        $data = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'code' => ['required', 'unique:positions,code,'.$position->id],
            'name' => ['required', 'string'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
        $position->update($data);
        AuditLog::record('update', $position, [], $data);

        return redirect()->route('positions.index')->with('success', 'Position diperbarui.');
    }

    public function destroy(Position $position)
    {
        AuditLog::record('delete', $position, $position->toArray());
        $position->delete();

        return back()->with('success', 'Position dihapus.');
    }
}
