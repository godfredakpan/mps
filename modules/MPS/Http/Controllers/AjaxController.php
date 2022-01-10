<?php

namespace Modules\MPS\Http\Controllers;

use Geographer;
use Illuminate\Http\Request;
use Modules\MPS\Models\User;
use Modules\MPS\Models\Register;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Modules\MPS\Events\RegisterRecordEvent;

class AjaxController extends Controller
{
    public function countries()
    {
        return collect(Geographer::getCountries()->useShortNames()->sortBy('name')->toArray())
        ->transform(function ($item, $key) {
            return ['value' => $item['isoCode'], 'label' => $item['name']];
        });
    }

    public function data()
    {
        return mps_data();
    }

    public function file($file)
    {
        return response()->file(storage_path('app/payments/') . $file);
    }

    public function impersonateUrl(Request $request, User $user)
    {
        if ($user->active && $user->can_impersonate) {
            return response()->json([
                'success' => true,
                'url'     => URL::signedRoute('impersonate.start', ['user' => $user->username]),
            ]);
        }
        return response()->json(['success' => false, 'message' => __('user_cannot_impersonate'), ], 422);
    }

    public function locale($locale)
    {
        $languages = collect(\Illuminate\Support\Facades\File::glob(module_path('MPS', 'Resources/lang/*.json')))->transform(function ($item) {
            return \Illuminate\Support\Str::before(basename($item), '.');
        })->all();

        if (!in_array($locale, $languages)) {
            abort(400);
        }
        app()->setLocale($locale);
        session(['language' => $locale]);
        return response()->json(['success' => true]);
    }

    public function location(Request $request)
    {
        $location = \Modules\MPS\Models\Location::findOrFail($request->location_id);
        session(['location_id' => $location->id]);
        $orders = \Modules\MPS\Models\Order::where('location_id', $location->id)->mine()->get();
        if ($orders->isNotEmpty()) {
            $orders->transform(function ($item) {
                $item->orderId = $item->id;
                unset($item->id);
                return $item;
            })->keyBy('oId');
        }
        return response()->json(['success' => !!$location, 'data' => $location->load('registers'), 'orders' => $orders]);
    }

    public function manifest()
    {
        return [
            'short_name'       => app_config('short_name'),
            'name'             => config('app.name'),
            'background_color' => '#3273dc',
            'orientation'      => 'portrait-primary',
            'theme_color'      => '#3273dc',
            'start_url'        => url('/') . '?app=true',
            'display'          => 'standalone',
            'icons'            => [
                ['src' => 'icon-48.png', 'type' => 'image/png', 'sizes' => '48x48'],
                ['src' => 'icon-76.png', 'type' => 'image/png', 'sizes' => '76x76'],
                ['src' => 'icon-96.png', 'type' => 'image/png', 'sizes' => '96x96'],
                ['src' => 'icon-120.png', 'type' => 'image/png', 'sizes' => '120x120'],
                ['src' => 'icon-144.png', 'type' => 'image/png', 'sizes' => '144x144'],
                ['src' => 'icon-152.png', 'type' => 'image/png', 'sizes' => '152x152'],
                ['src' => 'icon-192.png', 'type' => 'image/png', 'sizes' => '192x192'],
                ['src' => 'icon-512.png', 'type' => 'image/png', 'sizes' => '512x512'],
            ],
        ];
    }

    public function register(Request $request, Register $register)
    {
        if ($opened = register_record(true)) {
            if ($opened->location_id != session('location_id')) {
                return response()->json(['success' => false, 'lang_key' => 'wrong_location'], 422);
            }
            return response()->json(['success' => false, 'lang_key' => 'register_already_opened'], 422);
        }
        $data        = $request->validate(['cash_in_hand' => 'required|numeric']);
        $location_id = session('location_id', null);
        if ($location_id) {
            $user = user();
            session(['register_id' => $register->id]);
            $register->update(['opened' => 1]);
            $record = $user->registerRecords()->create([
                'location_id'  => $location_id,
                'register_id'  => $register->id,
                'cash_in_hand' => $data['cash_in_hand'],
            ]);
            event(new RegisterRecordEvent($record, $user));
            return response()->json(['success' => !!$record, 'data' => $record]);
        }
        return response()->json(['success' => false, 'lang_key' => 'select_location'], 422);
    }

    public function states(Request $request)
    {
        return collect(Geographer::findOneByCode($request->country)->getStates()->sortBy('name')->toArray())
        ->transform(function ($item, $key) {
            return ['value' => $item['isoCode'], 'label' => $item['name']];
        });
    }

    public function token(Request $request)
    {
        return response()->json(['token' => csrf_token()]);
    }
}
