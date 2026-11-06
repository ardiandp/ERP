<?php

namespace App\Http\Controllers;

use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->withCount('users', 'permissions')->get();

        return view('roles.index', compact('roles'));
    }
}
