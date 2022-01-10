<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\MPS\Http\Requests\UserRequest;

class UserController extends Controller
{
    public function destroy(User $user)
    {
        return response([
            'success' => $user->del(),
            'message' => __('record_deleted'),
            'error'   => __choice('delete_error', ['relations' => trans_choice('sale', 2) . ', ' . trans_choice('purchase', 2) . ', ' . trans_choice('income', 2) . ', ' . trans_choice('expense', 2) . ', ' . trans_choice('quotation', 2) . ', ' . trans_choice('return_order', 2) . ' ']),
        ]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (User::whereIn('id', $request->ids)->get() as $user) {
            $user->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function impersonate(Request $request, User $user)
    {
        $result = false;
        // if (!$user->hasRole('super') && $user->can_impersonate) {
        if ($request->hasValidSignature() && !$user->hasRole('super')) {
            // auth()->user()->setImpersonating($user->id);
            $user->setImpersonating();
            log_activity('Start impersonating as ' . $user->name, [
                'name'        => $user->name,
                'avatar'      => $user->avatar,
                'username'    => $user->username,
                'location_id' => $user->location_id,
            ], auth()->user());
            $result = true;
        }
        if ($request->wantsJson() || $request->ajax()) {
            return response(['success' => $result, 'user' => [
                'name'        => $user->name,
                'avatar'      => $user->avatar,
                'username'    => $user->username,
                'location_id' => $user->location_id,
            ]]);
        }
        return redirect()->to(module('route'));
    }

    public function index(Request $request)
    {
        $users = User::query()->with('location:id,name', 'locations:id,name', 'roles:id,name', 'meta');
        if ($request->all != 'yes') {
            $users->employee();
        }
        return response()->json($users->table(User::$searchable));
    }

    public function me(Request $request)
    {
        $user = User::findOrFail(auth()->id());

        $user->settings        = usermeta($user->id);
        $user->acting_user     = $user->actingUser();
        $user->all_permissions = $user->all_permissions;
        return load_media($user->load(['roles:id,name', 'locations:id,name']));
    }

    public function show(User $user)
    {
        $user->settings = usermeta($user->id);
        // $user->all_permissions = $user->all_permissions;
        return load_media($user->load(['roles:id,name', 'locations:id,name']));
    }

    public function stopImpersonate()
    {
        $user        = auth()->user();
        $acting_user = $user->actingUser();
        log_activity('Stop impersonating as ' . $acting_user['name'], [$acting_user], $user);
        $user->stopImpersonating();
        return response(['success' => true]);
    }

    public function store(UserRequest $request)
    {
        $data             = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user             = User::create($data)->syncRoles($data['roles']);

        $user->addFiles($data['files'] ?? []);
        $user->saveSettings($data['settings']);
        $user->locations()->sync($data['locations'] ?? []);
        return response(['success' => true, 'data' => $user]);
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();
        $user->update($data);
        $user->syncRoles($data['roles'] ?? []);
        $user->addFiles($data['files']);
        $user->saveSettings($data['settings']);
        $user->locations()->sync($data['locations'] ?? []);
        $user->refresh()->load('roles:name');
        $user->settings = usermeta($user->id);
        return response(['success' => true, 'data' => load_media($user)]);
    }
}
