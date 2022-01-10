<?php

namespace Tecdiary\Installer\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Tecdiary\Installer\Install;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Tecdiary\Installer\Http\Requests\UserRequest;

class InstallController extends Controller
{
    public function __construct()
    {
        if (env('APP_INSTALLED', false) == true) {
            return redirect('/');
        }
    }

    public function account(UserRequest $request)
    {
        Install::createUser($request->validated());
        return response()->json(['success' => true, 'message' => 'User account has been created.'], 200);
    }

    public function demo(Request $request)
    {
        if (!$request->done) {
            return response()->json(['success' => false, 'message' => 'Unable to create demo data!'], 422);
        }
        Install::createDemoData();
        return response()->json(['success' => true, 'message' => 'Demo data has been created.'], 200);
    }

    public function finalize(Request $request)
    {
        if (!$request->done) {
            return response()->json(['success' => false, 'message' => 'Installation can not be finalized!'], 422);
        }
        Install::finalize();
        return response()->json(['success' => true, 'message' => 'Installation has been finalized.'], 200);
    }

    public function index()
    {
        return view('installer::index');
    }

    public function license(Request $request)
    {
        $v = $request->validate([
            'code'     => 'required',
            'username' => 'required',
        ]);

        $v['ip']          = $request->ip();
        $v['license_key'] = $v['code'];
        return Install::registerLicense($request, $v);
    }

    public function save(Request $request)
    {
        $v = $request->validate([
            'license'         => 'required',
            'dbhost'          => 'required',
            'dbname'          => 'required',
            'dbuser'          => 'required',
            'dbpass'          => 'nullable',
            'dbport'          => 'required|numeric',
            'dbsocket'        => 'nullable',
            'installation_id' => 'required',
        ]);

        $res = Install::createTables($request, $v);

        if (!$res || $res['success'] != true) {
            $error = null;
            if (isset($res['0']) && !empty($res['0'])) {
                $error = Str::before($res['0'], '(SQL:');
            }
            return response()->json(['success' => false, 'message' => $error ?? $res['message'] ?? 'Could not connect to the database! Please make sure the details are correct.'], 422);
        }

        return response()->json(['success' => true, 'message' => $res['message'] ? $res['message'] : 'Database tables are created.'], 200);
    }

    public function show()
    {
        $requirements = Install::requirements();

        if (!empty($requirements)) {
            return response()->json(['success' => false, 'message' => 'Please check the following server requirement', 'errors' => $requirements]);
        }

        if (!File::exists(base_path('.env'))) {
            Install::createEnv();
        }

        return response()->json(['success' => true, 'message' => 'All server requirements are fine. Please proceed to next step!']);
    }
}
