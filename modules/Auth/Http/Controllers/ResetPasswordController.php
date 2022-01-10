<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        if ($request->ajax()) {
            return response()->json(['success' => false, 'errors' => ['email' => [trans($response)]], 'message' => trans($response)], 422);
        }
        return redirect()->back()->withInput($request->only('email'))->withErrors(['email' => trans($response)]);
    }

    protected function sendResetResponse(Request $request, $response)
    {
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => trans($response)]);
        }
        return redirect($this->redirectPath())->with('status', trans($response));
    }
}
