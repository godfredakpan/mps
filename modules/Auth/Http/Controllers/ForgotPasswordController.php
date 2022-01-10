<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        if ($request->ajax()) {
            return response()->json(['success' => false, 'errors' => ['email' => [trans($response)]], 'message' => trans($response)], 422);
        }
        return back()->withInput($request->only('email'))->withErrors(['email' => trans($response)]);
    }

    protected function sendResetLinkResponse(Request $request, $response)
    {
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => trans($response)]);
        }
        return back()->with('status', trans($response));
    }
}
