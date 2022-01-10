<?php

use Illuminate\Support\Facades\Cache;

// Module route
if (!function_exists('module_data')) {
    function module_data($name, $key = null)
    {
        $module = Cache::get($name, function () use ($name) {
            $module = json_decode(file_get_contents(module_path($name) . '/module.json'));
            Cache::forever($name, $module);
            return $module;
        });
        return $key ? $module->$key : $module;
    }
}

// App Config
if (!function_exists('app_config')) {
    function app_config($key = null)
    {
        if ($key) {
            return optional(\App\Setting::where('tec_key', $key)->first())->tec_value;
        }

        return \App\Setting::all()->mapWithKeys(function ($item) {
            return [$item['tec_key'] => $item['tec_value']];
        });
    }
}

// Log Activity
if (!function_exists('log_activity')) {
    function log_activity($activity, $properties = null, $model = null)
    {
        return activity()->performedOn($model)->withProperties($properties)->log($activity);
    }
}

// Get Country and Sate
if (!function_exists('getCS')) {
    function getCS($country, $state)
    {
        $country = get_country($country);
        return ['country' => $country, 'state' => get_state($country, $state)];
    }
}

// Get Country by Code
if (!function_exists('get_country')) {
    function get_country($code)
    {
        return \Geographer::getCountries()->findOne(['isoCode' => $code]);
    }
}

// Get Countries
if (!function_exists('get_countries')) {
    function get_countries()
    {
        return collect(\Geographer::getCountries()->useShortNames()->sortBy('name')->toArray())
            ->transform(fn ($item) => (['value' => $item['isoCode'], 'label' => $item['name']]));
    }
}

// Get Country states
if (!function_exists('get_states')) {
    function get_states($country)
    {
        return collect(\Geographer::findOneByCode($country)->getStates()->sortBy('name')->toArray())
            ->transform(fn ($item) => (['value' => $item['isoCode'], 'label' => $item['name']]));
    }
}

// Get State by Code
if (!function_exists('get_state')) {
    function get_state($country, $code)
    {
        return $country->getStates()->findOne(['code' => $code]);
    }
}

// Auto logout from other devices
if (!function_exists('single_device_login')) {
    function single_device_login()
    {
        return !demo();
    }
}

// Is Demo Enabled
if (!function_exists('demo')) {
    function demo()
    {
        return env('DEMO', false);
    }
}

// Is safe email
if (!function_exists('safe_email')) {
    function safe_email($email)
    {
        $contains = \Illuminate\Support\Str::contains($email, '@example.');
        return $email && !$contains;
    }
}

// Site load data
if (!function_exists('site_data')) {
    function site_data()
    {
        $user = auth()->check() ? auth()->user() : false;
        return [
            'user' => $user ? [
                'name'        => $user->name,
                'email'       => $user->email,
                'phone'       => $user->phone,
                'username'    => $user->username,
                'roles'       => $user->roles->pluck('name'),
                'customer_id' => $user->customer_id,
                'vendor_id'   => $user->vendor_id,
            ] : null,
            'settings' => [
                'demo'    => demo(),
                'baseURL' => url('/'),
                'data'    => app_config(),
                'app'     => ['name' => config('app.name')],
            ],
            'token' => csrf_token(),
        ];
    }
}

// Get translation
if (!function_exists('__choice')) {
    function __choice($key, array $replace = [], $number = null)
    {
        return trans_choice($key, $number, $replace);
    }
}

// Get translation
if (!function_exists('generate_barcode')) {
    function generate_barcode($code, $symbology = 'TYPE_CODE_128', int $widthFactor = 2, int $height = 30, string $foregroundColor = 'black', $type = 'png')
    {
        if ($type == 'png') {
            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            // $generator = new \Picqer\Barcode\BarcodeGeneratorHTML();
            $foregroundColor = $foregroundColor == 'black' ? [0, 0, 0] : [255, 255, 255];
            return '<img style="max-width:220px;display:inline-block;" src="data:image/png;base64,' . base64_encode($generator->getBarcode($code, $generator::TYPE_CODE_128, $widthFactor, $height, $foregroundColor)) . '">';
        }
        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        return $generator->getBarcode($code, $generator::TYPE_CODE_128, $widthFactor, $height, $foregroundColor);
    }
}
