<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleApiController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $roles = Role::all();
        $permissions = Permission::all();
        return $this->responseSuccess(compact('roles', 'permissions'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|unique:roles,name']);
        $role = Role::create(['name' => $request->name]);
        return $this->responseSuccess($role, 'Role Created successfully.');
    }

    public function assignPermissions(Request $request): JsonResponse
    {
        $role = Role::select()->where('name', $request->role)->get()->first();
        $role->syncPermissions($request->permissions);
        return $this->responseSuccess($role, 'Permissions updated!');
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::find($id);
        $role->delete();
        return $this->responseSuccess(message: 'Role deleted!');
    }
}
