<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current'  => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!(Hash::check($data['current'], $request->user()->password))) {
            return response(['success' => false, 'lang_key' => 'current_password_error'], 422);
        }

        if (strcmp($data['current'], $data['password']) == 0) {
            return response(['success' => false, 'lang_key' => 'same_password_error', 'errors' => ['password' => ['same_password_error']]], 422);
        }

        if (demo()) {
            return response(['success' => false, 'lang_key' => 'disabled_in_demo'], 422);
        }

        $request->user()->fill(['password' => Hash::make($data['password'])])->save();
        return response(['success' => true]);
    }

    public function index()
    {
        $user = $this->transform(user());
        return response()->json($user);
    }

    public function transform($user, $success = false)
    {
        if ($user) {
            $user = [
                'name'     => $user->name,
                'phone'    => $user->phone,
                'email'    => $user->email,
                'avatar'   => $user->avatar,
                'username' => $user->username,
                'settings' => usermeta($user->id, null, true),
            ];
            if ($success) {
                $user['success'] = true;
            }
        }
        return $user;
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required',
            'settings' => 'nullable|array',
            'phone'    => 'required|unique:users,phone,' . auth()->id(),
            'avatar'   => 'nullable|image|mimes:jpeg,jpg,png|dimensions:ratio=1|dimensions:max_width=1000,max_height=1000',
        ], [
            'avatar.dimensions' => __('avatar_error_text'),
        ]);
        $user = user();
        if ($request->has('avatar')) {
            $avatar_path    = $request->avatar->store('/avatars', 'public');
            $data['avatar'] = Storage::disk('public')->url($avatar_path);
        } else {
            unset($data['avatar']);
        }
        $user->update($data);
        $user->saveSettings($data['settings'] ?? [], true);
        return response()->json($this->transform($user, true));
    }
}
