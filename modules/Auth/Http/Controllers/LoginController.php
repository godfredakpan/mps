<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        if (!$user->active) {
            $this->guard()->logout();
            $request->session()->invalidate();
            return $request->ajax() ? response()->json(['success' => false, 'lang_key' => 'user_not_active'], 422) : redirect('/');
        }
        $user->logActivity('User has been logged in');
        if (single_device_login()) {
            Auth::logoutOtherDevices($request->get('password'));
        }
        if ($request->ajax()) {
            return response()->json(['success' => true], 200);
        }
    }

    protected function credentials(Request $request)
    {
        $field = is_numeric($request->username) ? 'phone' : (filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username');
        return [
            $field     => $request->get('username'),
            'password' => $request->get('password'),
        ];
    }

    public function decayMinutes()
    {
        return property_exists($this, 'decayMinutes') ? $this->decayMinutes : 5;
    }

    public function logout(Request $request)
    {
        if ($user = $request->user()) {
            $user->logActivity('User has been logging out');
            $user->disableLogging();
            $this->guard()->logout();
            $request->session()->flush();
        }
        if (request()->ajax()) {
            return response(['success' => true], 200);
        }
        return redirect('/');
    }

    public function maxAttempts()
    {
        return property_exists($this, 'maxAttempts') ? $this->maxAttempts : 3;
    }

    public function username()
    {
        return 'username';
    }
}
