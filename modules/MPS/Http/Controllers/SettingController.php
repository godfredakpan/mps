<?php

namespace Modules\MPS\Http\Controllers;

use App\Helpers\Env;
use Illuminate\Http\Request;
use Modules\MPS\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super']);
    }

    public function index(Request $request)
    {
        $accounts   = \Modules\MPS\Models\Account::all(['id as value', 'name as label']);
        $customers  = \Modules\MPS\Models\Customer::all(['id as value', 'name as label']);
        $categories = \Modules\MPS\Models\Category::all(['id as value', 'name as label']);
        $countries  = collect(\Geographer::getCountries()->useShortNames()
        ->sortBy('name')->toArray())->transform(
            fn ($item, $key) => ['value' => $item['isoCode'], 'label' => $item['name']]
        );

        $mail_settings = [
            'MAIL_FROM_ADDRESS'  => env('MAIL_FROM_ADDRESS'),
            'MAIL_FROM_NAME'     => env('MAIL_FROM_NAME'),
            'MAIL_MAILER'        => env('MAIL_MAILER'),
            'MAIL_HOST'          => env('MAIL_HOST'),
            'MAIL_PORT'          => env('MAIL_PORT'),
            'MAIL_USERNAME'      => env('MAIL_USERNAME'),
            'MAIL_PASSWORD'      => env('MAIL_PASSWORD'),
            'MAIL_ENCRYPTION'    => env('MAIL_ENCRYPTION'),
            'MAILGUN_DOMAIN'     => env('MAILGUN_DOMAIN'),
            'MAILGUN_SECRET'     => env('MAILGUN_SECRET'),
            'MAILGUN_ENDPOINT'   => env('MAILGUN_ENDPOINT'),
            'SPARKPOST_SECRET'   => env('SPARKPOST_SECRET'),
            'SPARKPOST_ENDPOINT' => env('SPARKPOST_ENDPOINT'),
        ];

        $payment_settings = [
            'CURRENCY_CODE'             => env('CURRENCY_CODE'),
            'PAYPAL_ENABLED'            => env('PAYPAL_ENABLED'),
            'PAYPAL_USERNAME'           => env('PAYPAL_USERNAME'),
            'PAYPAL_PASSWORD'           => env('PAYPAL_PASSWORD'),
            'PAYPAL_SIGNATURE'          => env('PAYPAL_SIGNATURE'),
            'CARD_GATEWAY'              => env('CARD_GATEWAY'),
            'PAYPAL_CLIENT_ID'          => env('PAYPAL_CLIENT_ID'),
            'PAYPAL_SECRET'             => env('PAYPAL_SECRET'),
            'STRIPE_KEY'                => env('STRIPE_KEY'),
            'STRIPE_SECRET'             => env('STRIPE_SECRET'),
            'AUTHORIZE_LOGIN'           => env('AUTHORIZE_LOGIN'),
            'AUTHORIZE_TRANSACTION_KEY' => env('AUTHORIZE_TRANSACTION_KEY'),
        ];

        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);

        return response()->json([
            'accounts'         => $accounts,
            'customers'        => $customers,
            'countries'        => $countries,
            'timezones'        => $timezones,
            'categories'       => $categories,
            'mail_settings'    => $mail_settings,
            'payment_settings' => $payment_settings,
            'settings'         => mps_config(null, true),
        ]);
    }

    public function label()
    {
        return response()->json([
            'json_string'  => mps_config('json_string'),
            'label_width'  => mps_config('label_width'),
            'label_height' => mps_config('label_height'),
        ]);
    }

    public function logo(Request $request)
    {
        $request->validate(['logo' => 'nullable|mimes:jpg,jpeg,png,svg|max:300']);
        if (demo()) {
            return response()->json(['success' => false, 'lang_key' => 'disabled_in_demo'], 422);
        }
        if ($request->has('logo')) {
            $name = 'logo.' . $request->logo->extension();
            $path = $request->logo->storeAs('images', $name, 'public');
            $logo = Storage::disk('public')->url($path);
            Setting::updateOrCreate(['mps_key' => 'default_logo'], ['mps_value' => $logo]);
            Cache::flush();
            return response()->json(['success' => true, 'logo' => $logo]);
        }
        return response()->json(['success' => false]);
    }

    public function saveBarcode(Request $request)
    {
        if (demo()) {
            return response()->json(['success' => false, 'lang_key' => 'disabled_in_demo'], 422);
        }

        $scale_barcode = $request->only(['scale_barcode']);
        DB::transaction(function () use ($scale_barcode) {
            Setting::updateOrCreate(['mps_key' => 'scale_barcode'], ['mps_value' => json_encode($scale_barcode['scale_barcode'])]);
        });
        Cache::flush();
        return response()->json($scale_barcode);
    }

    public function saveLabel(Request $request)
    {
        if (demo()) {
            return response()->json(['success' => false, 'lang_key' => 'disabled_in_demo'], 422);
        }

        $label_settings = $request->only(['json_string', 'svg_string', 'label_width', 'label_height']);
        DB::transaction(function () use ($label_settings) {
            Setting::updateOrCreate(['mps_key' => 'svg_string'], ['mps_value' => $label_settings['svg_string']]);
            Setting::updateOrCreate(['mps_key' => 'json_string'], ['mps_value' => $label_settings['json_string']]);
            Setting::updateOrCreate(['mps_key' => 'label_width'], ['mps_value' => $label_settings['label_width']]);
            Setting::updateOrCreate(['mps_key' => 'label_height'], ['mps_value' => $label_settings['label_height']]);
        });
        Cache::flush();
        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $name     = mps_config('name', true);
        $timezone = mps_config('timezone', true);
        $settings = $request->except(['_token', '_method']);

        $settings['loyalty']          = json_encode($settings['loyalty'] ?? '');
        $settings['auto_update_time'] = json_encode($settings['auto_update_time'] ?? [
            'time' => ['03:00', '22:00'],
            'day'  => ['mondays', 'tuesdays', 'wednesdays', 'thursdays', 'fridays', 'saturdays', 'sundays'][mt_rand(0, 6)],
        ]);
        $notAllowed = demo() ? ['name', 'short_name', 'company', 'rows', 'quick_cash', 'json_string', 'svg_string'] : [];
        DB::transaction(function () use ($settings, $notAllowed) {
            foreach ($settings as $key => $value) {
                if (!in_array($key, $notAllowed)) {
                    if ($key == 'user_locale' && !$value) {
                        $value = '';
                    }
                    Setting::updateOrCreate(['mps_key' => $key], ['mps_value' => $value]);
                }
            }
        });
        Cache::flush();
        $settings['loyalty']          = json_decode($settings['loyalty']);
        $settings['auto_update_time'] = json_decode($settings['auto_update_time']);
        if (!demo() && ($name != $settings['name'] || $timezone != $settings['timezone'])) {
            Env::update(['APP_NAME' => $settings['name'], 'APP_TIMEZONE' => $settings['timezone']], true);
        }
        return response()->json($settings);
    }

    public function update(Request $request)
    {
        if (demo()) {
            return response()->json(['success' => false, 'lang_key' => 'disabled_in_demo'], 422);
        }

        $data = $request->except(['_token', '_method']);
        if (Env::update($data, true)) {
            return response()->json($data);
        }
        return response()->json(['success' => false, 'lang_key' => 'failed_error_text'], 422);
    }
}
