<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        return view('roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles,name']);
        Role::create(['name' => $request->name]);

        return back()->with('success', 'Role created successfully!');
    }

    public function assignPermissions(Request $request, Role $role)
    {
        $role->syncPermissions($request->permissions);
        return back()->with('success', 'Permissions updated!');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return back()->with('success', 'Role deleted!');
    }
}
