<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Role;
use Modules\MPS\Models\Permission;

class RoleController extends Controller
{
    public function destroy(Role $role)
    {
        return response([
            'success' => $role->del(),
            'message' => __('record_deleted'),
            'error'   => __choice('delete_error', ['relations' => 'permissions']),
        ]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Role::whereIn('id', $request->ids)->get() as $role) {
            $role->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index()
    {
        return response()->json(Role::notSuper()->table(Role::$searchable));
    }

    public function setPermissions(Request $request, Role $role)
    {
        $permissions = $request->all();
        $permissions = collect($permissions)->flatten()->all();
        $role->syncPermissions($permissions);
        return response(['success' => true]);
    }

    public function show(Request $request, Role $role)
    {
        if ($request->permissions == 'yes') {
            $role->permissions     = $role->permissions()->pluck('name');
            $role->all_permissions = Permission::select('name')->get()->pluck('name');
        }
        return $role;
    }

    public function store(Request $request)
    {
        $form = $request->validate(['name' => 'required|string']);
        $role = Role::create(['name' => mb_strtolower($form['name'])]);
        return response(['success' => true, 'data' => $role]);
    }

    public function update(Request $request, Role $role)
    {
        $form = $request->validate(['name' => 'required|string']);
        $role->update(['name' => mb_strtolower($form['name'])]);
        return response(['success' => true, 'data' => $role->refresh()]);
    }
}
