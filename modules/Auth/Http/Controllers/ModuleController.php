<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ModuleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function disable(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->hasRole('super')) {
            $validated = $request->validate(['name' => 'required|string']);
            (Module::find($validated['name']))->disable();
            return redirect()->to('/modules')->with('success', __('Module has been disabled'));
        }
        return redirect()->to('/');
    }

    public function enable(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->hasRole('super')) {
            $validated = $request->validate(['name' => 'required|string']);
            (Module::find($validated['name']))->enable();
            return redirect()->to('/modules')->with('success', __('Module has been enable'));
        }
        return redirect()->to('/');
    }

    public function index()
    {
        $user = auth()->user();
        if ($user && $user->hasRole('super')) {
            $modules = [];
            $keys    = json_decode(Storage::disk('local')->get('keys.json'), true);
            $comp    = json_decode(file_get_contents(base_path('composer.json')), true);
            foreach (Module::all() as $module) {
                $name = $module->getName();
                if (!in_array($name, ['Auth', 'MPS'])) {
                    $modules[] = [
                        'name'        => $name,
                        'isEnabled'   => $module->isEnabled(),
                        'description' => $module->getDescription(),
                        'installed'   => isset($keys[$module->getLowerName()]) && file_exists($module->getPath()),
                    ];
                }
            }
            return view('auth::modules.index', ['mods' => $modules, 'version' => $comp['version']]);
        }
        return redirect()->to('/');
    }

    public function install(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->hasRole('super')) {
            $v = $request->validate(['name' => 'required|string', 'key' => 'required|uuid']);
            $e = Artisan::call("mps:install {$v['name']} {$v['key']}");
            return response()->json(['exitCode' => $e, 'output' => Artisan::output()]);
        }
        return redirect()->to('/');
    }
}
