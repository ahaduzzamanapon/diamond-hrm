<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy(function($data) {
            return explode('_', $data->name)[1] ?? 'general';
        });
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array'
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        if($role->name === 'super-admin') {
            return redirect()->route('roles.index')->with('error', 'Super Admin role cannot be edited.');
        }

        $permissions = Permission::all()->groupBy(function($data) {
            return explode('_', $data->name)[1] ?? 'general';
        });
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        if($role->name === 'super-admin') {
            return redirect()->route('roles.index')->with('error', 'Super Admin role cannot be edited.');
        }

        $request->validate([
            'name' => 'required|unique:roles,name,'.$role->id,
            'permissions' => 'required|array'
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if(in_array($role->name, ['super-admin', 'hr-admin', 'branch-manager', 'hr', 'staff'])) {
            return redirect()->route('roles.index')->with('error', 'System reserved roles cannot be deleted.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
