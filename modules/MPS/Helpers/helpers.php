<?php

// MPS Config
if (!function_exists('mps_config')) {
    function mps_config($key = null, $noCache = null)
    {
        if ($noCache) {
            if ($key) {
                $value = optional(\Modules\MPS\Models\Setting::where('mps_key', $key)->first())->mps_value;
                return $key == 'scale_barcode' || $key == 'loyalty' || $key == 'auto_update_time' ? json_decode($value ?? '') : $value;
            }
            return \Modules\MPS\Models\Setting::all()->mapWithKeys(
                fn ($item) => [
                    $item['mps_key'] => $item['mps_key'] == 'json_string'
                    ? null
                    : (
                        $item['mps_key'] == 'scale_barcode' || $item['mps_key'] == 'loyalty' || $item['mps_key'] == 'auto_update_time'
                        ? json_decode($item['mps_value'] ?? '')
                        : $item['mps_value']
                    ),
                ]
            );
        }

        if ($key) {
            return cache()->rememberForever(
                'mps_' . $key,
                function () use ($key) {
                    $value = optional(\Modules\MPS\Models\Setting::where('mps_key', $key)->first())->mps_value;
                    return $key == 'scale_barcode' || $key == 'loyalty' || $key == 'auto_update_time' ? json_decode($value ?? '') : $value;
                }
            );
        }
        return cache()->rememberForever(
            'mps_settings',
            fn ()          => \Modules\MPS\Models\Setting::all()->mapWithKeys(
                fn ($item) => [
                    $item['mps_key'] => $item['mps_key'] == 'json_string'
                    ? null
                    : (
                        $item['mps_key'] == 'scale_barcode' || $item['mps_key'] == 'loyalty' || $item['mps_key'] == 'auto_update_time'
                        ? json_decode($item['mps_value'] ?? '')
                        : $item['mps_value']
                    ),
                ]
            )
        );
    }
}

// User Settings
if (!function_exists('usermeta')) {
    function usermeta($user_id, $key = null, $profile = false)
    {
        if ($key) {
            return optional(\Modules\MPS\Models\UserMeta::ofUser($user_id)->where('meta_key', $key)->first())->meta_value;
        }
        return \Modules\MPS\Models\UserMeta::ofUser($user_id)->whereIn('meta_key', allowed_usermeta($profile))->get()->mapWithKeys(
            fn ($item) => [$item['meta_key'] => $item['meta_value']]
        );
    }
}

// User Settings
if (!function_exists('allowed_usermeta')) {
    function allowed_usermeta($profile = false)
    {
        if (!$profile && auth()->user()->hasRole('super')) {
            return [
                'clock_in', 'hire_date', 'first_login', 'locations', 'location_id', 'max_discount',
                'number', 'salary', 'hourly_rate', 'commission_rate', 'require_password', 'commission_method',
                'theme', 'address', 'sidebar', 'language', 'timezone', 'birth_date', 'fixed_layout', 'play_sound',
            ];
        }
        return ['theme', 'address', 'collapsed', 'language', 'timezone', 'birth_date', 'fixed_layout', 'play_sound'];
    }
}

// Module file
if (!function_exists('module')) {
    function module($key = null)
    {
        $mps = cache()->rememberForever(
            'mps',
            fn () => json_decode(file_get_contents(module_path('mps') . '/module.json'))
        );
        return $key ? $mps->$key : $mps;
    }
}

// Get user
if (!function_exists('user')) {
    function user($user_id = null)
    {
        if ($user_id) {
            return cache()->rememberForever($user_id, fn () => \Modules\MPS\Models\User::find($user_id));
        }
        return auth()->guest() ? null : \Modules\MPS\Models\User::find(auth()->id());
    }
}

// Get location
if (!function_exists('location')) {
    function location()
    {
        $location_id = session('location_id', null);
        return $location_id ? cache()->rememberForever(
            $location_id,
            fn () => \Modules\MPS\Models\Location::whereId($location_id)->first()
        ) : null;
    }
}

// Get locations
if (!function_exists('locations')) {
    function locations($select = null)
    {
        return cache()->rememberForever(
            'mps_locations',
            fn () => \Modules\MPS\Models\Location::all($select)
        );
    }
}

// Generate Reference Number
if (!function_exists('generate_reference')) {
    function generate_reference($model)
    {
        $format = mps_config('reference');
        if ($format == 'yearly') {
            return (usermeta($model->user_id ?: auth()->id(), 'number') ?? 'SC') . '/' . now()->format('Y/') . sequence_number($model);
        } elseif ($format == 'monthly') {
            return (usermeta($model->user_id ?: auth()->id(), 'number') ?? 'SC') . '/' . now()->format('Y/m/') . sequence_number($model);
        } elseif ($format == 'sequence') {
            return (usermeta($model->user_id ?: auth()->id(), 'number') ?? 'SC') . '/' . sequence_number($model);
        }
        return (usermeta($model->user_id ?: auth()->id(), 'number') ?? 'SC') . '/' . \Illuminate\Support\Str::random(12);
    }
}

// Get Sequence Number
if (!function_exists('sequence_number')) {
    function sequence_number($model)
    {
        $c       = \Illuminate\Support\Str::snake(class_basename($model));
        $seq     = \Modules\MPS\Models\Sequence::first();
        $seq->$c = $seq->$c + 1;
        $seq->save();
        return $seq->$c;
    }
}

// Convert unit quantity to base quantity
if (!function_exists('convert_to_base_quantity')) {
    function convert_to_base_quantity($quantity, $unit)
    {
        // $unit = \Modules\MPS\Models\Unit::find($unit_id);
        // $unit = \Modules\MPS\Models\Unit::with('baseUnit')->find($unit_id);
        $base_quantity = $quantity;
        if ($unit && $unit->operator) {
            switch ($unit->operator) {
                case '*':
                    $base_quantity = $quantity * $unit->operation_value;
                    break;
                case '/':
                    $base_quantity = $quantity / $unit->operation_value;
                    break;
                case '+':
                    $base_quantity = $quantity + $unit->operation_value;
                    break;
                case '-':
                    $base_quantity = $quantity - $unit->operation_value;
                    break;
                default:
                    $base_quantity = $quantity;
            }
        }
        return $base_quantity;
    }
}
// Calculate base costing from unit
if (!function_exists('calculate_base_costing')) {
    function calculate_base_costing($value, $unit)
    {
        // $unit = \Modules\MPS\Models\Unit::find($unit_id);
        // $unit = \Modules\MPS\Models\Unit::with('baseUnit')->find($unit_id);
        $base_value = $value;
        if ($unit && $unit->operator) {
            switch ($unit->operator) {
                case '*':
                    $base_value = $value * $unit->operation_value;
                    break;
                case '/':
                    $base_value = $value / $unit->operation_value;
                    break;
                case '+':
                    $base_value = $value + $unit->operation_value;
                    break;
                case '-':
                    $base_value = $value - $unit->operation_value;
                    break;
                default:
                    $base_value = $value;
            }
        }
        return $base_value;
    }
}

// Get user register record
if (!function_exists('register_record')) {
    function register_record($basic = false)
    {
        $register_record = optional(\Modules\MPS\Models\RegisterRecord::mine()->notClosed())->first();

        if ($register_record && $basic) {
            return [
                'id'           => $register_record->id,
                'created_at'   => $register_record->created_at->toDateTimeString(),
                'cash_in_hand' => $register_record->cash_in_hand,
                'location_id'  => $register_record->location_id,
                'register_id'  => $register_record->register_id,
                'user_id'      => $register_record->user_id,
            ];
        }

        return $register_record;
    }
}

// Get extra attributes for model
if (!function_exists('extra_attributes')) {
    function extra_attributes($model)
    {
        return cache()->rememberForever(
            'extra_attributes',
            fn () => \Modules\MPS\Models\Field::where('entities', 'like', "%{$model}%")->orderBy('order')
                        ->get(['id', 'name', 'slug', 'options', 'required', 'type'])
        );
    }
}

// Format Decimal
if (!function_exists('formatDecimal')) {
    function formatDecimal($number, $decimals = 4, $ds = '.', $ts = '')
    {
        return number_format($number, $decimals, $ds, $ts);
    }
}

// Format Number
if (!function_exists('formatNumber')) {
    function formatNumber($number, $decimals = null, $ds = '.', $ts = ',')
    {
        $decimals ??= mps_config('decimals');
        return number_format($number, $decimals, $ds, $ts);
    }
}

// Format Quantity
if (!function_exists('formatQuantity')) {
    function formatQuantity($number, $decimals = 4, $ds = '.', $ts = '')
    {
        $decimals = mps_config('quantity_decimals') ?? $decimals;
        return number_format($number, $decimals, $ds, $ts);
    }
}

// Get php date format string
if (!function_exists('php_date_formate')) {
    function php_date_formate()
    {
        return cache()->rememberForever(
            'php_date_formate',
            fn () => (string) str_replace(['YYYY', 'MM', 'DD'], ['Y', 'm', 'd'], mps_config('dateformat'))
        );
    }
}

// Is default customer
if (!function_exists('default_customer')) {
    function default_customer($id)
    {
        return cache()->rememberForever(
            'default_customer',
            fn () => mps_config('default_customer') == $id
        );
        // return mps_config('default_customer') == $id;
    }
}

// Is Demo Enabled
if (!function_exists('stock')) {
    function stock()
    {
        return cache()->rememberForever(
            'stock',
            fn () => mps_config('stock')
        );
        // return mps_config('stock');
    }
}

// Parse scale barcode
if (!function_exists('parse_scale_barcode')) {
    function parse_scale_barcode($barcode, $scale_barcode)
    {
        $price  = 0;
        $weight = 0;
        if ($scale_barcode['type'] == 'price') {
            try {
                $price = substr($barcode, $scale_barcode['price_start'] - 1, $scale_barcode['price_digits']);
                $price = $scale_barcode['price_divide_by'] ? $price / $scale_barcode['price_divide_by'] : $price;
            } catch (\Exception $e) {
                $price = 0;
            }
        } else {
            try {
                $weight = substr($barcode, $scale_barcode['weight_start'] - 1, $scale_barcode['weight_digits']);
                $weight = $scale_barcode['weight_divide_by'] ? $weight / $scale_barcode['weight_divide_by'] : $weight;
            } catch (\Exception $e) {
                $weight = 0;
            }
        }
        $item_code = substr($barcode, $scale_barcode['item_code_start'] - 1, $scale_barcode['item_code_digits']);

        return ['item_code' => $item_code, 'price' => $price, 'weight' => $weight];
    }
}

// Transform collection
if (!function_exists('transform_select')) {
    function transform_select(Illuminate\Support\Collection $collection, array $transform, array $relations = [])
    {
        return $collection->map(function ($item) use ($transform, $relations) {
            $nItem = [];
            $item = $item->toArray();
            foreach ($transform as $key => $value) {
                $nItem[$value] = is_integer($key) ? $item[$value] : $item[$key];
            }
            if (!empty($relations)) {
                foreach ($relations as $relation) {
                    $relation = \Illuminate\Support\Str::snake($relation);
                    $nItem[$relation] = $item[$relation];
                }
            }
            return $nItem;
        });
    }
}

// Calculate Discount
if (!function_exists('calculateDiscount')) {
    function calculateDiscount($discount = 0, $amount = 0)
    {
        if ($discount && $amount) {
            if (\Illuminate\Support\Str::contains($discount, '%')) {
                $dv       = explode('%', $discount);
                $discount = number_format((($amount * (float) $dv[0]) / 100), 2, '.', '');
            }
        }
        return $discount;
    }
}

// Calculate Tax
if (!function_exists('calculateTax')) {
    function calculateTax($tax = 0, $amount = 0)
    {
        if ($tax && $amount) {
            if (\Illuminate\Support\Str::contains($tax, '%')) {
                $tv  = explode('%', $tax);
                $tax = number_format((($amount * (float) $tv[0]) / 100), 2, '.', '');
            }
        }
        return $tax;
    }
}

// Site load data
if (!function_exists('mps_data')) {
    function mps_data()
    {
        $locale = mps_config('language', true);

        $user = auth()->check() ? \Modules\MPS\Models\User::where('id', auth()->id())->first() : false;

        $languages = collect(\Illuminate\Support\Facades\File::glob(module_path('MPS', 'Resources/lang/*.json')))->transform(function ($item) {
            return \Illuminate\Support\Str::before(basename($item), '.');
        })->all();

        $modules = collect(\Nwidart\Modules\Facades\Module::collections()->toArray())->transform(function ($item) {
            return ['name' => $item['name'], 'alias' => $item['alias'], 'route' => $item['route']];
        });

        return [
            'languages'     => $languages,
            'user_language' => session('language', $locale),

            'user' => $user ? [
                'name'        => $user->name,
                'email'       => $user->email,
                'phone'       => $user->phone,
                'avatar'      => $user->avatar,
                'username'    => $user->username,
                'customer_id' => $user->customer_id,
                'vendor_id'   => $user->vendor_id,
                'location_id' => $user->location_id,
                'locations'   => $user->locations,
                'settings'    => usermeta($user->id, null, true),
                'roles'       => $user->roles->map(
                    fn ($r)   => ['id' => $r->id, 'name' => $r->name]
                ),
                'acting_user'     => $user->actingUser(),
                'all_permissions' => $user->all_permissions,
            ] : null,
            'settings' => [
                'demo'    => demo(),
                'stock'   => stock(),
                'baseURL' => url('/'),
                'modules' => $modules,
                'data'    => mps_config(),
                'app'     => ['name' => config('app.name')],
                'payment' => [
                    'public_key' => env('STRIPE_KEY'),
                    'gateway'    => env('CARD_GATEWAY'),
                    'moduleURL'  => url(module('route')),
                    'paypal'     => env('PAYPAL_ENABLED'),
                ],
            ],
            'token'     => csrf_token(),
            'register'  => register_record(true),
            'location'  => session('location_id', null),
            'locations' => locations(['id as value', 'name as label', 'color', 'logo']),
        ];
    }
}

// Load media for model
if (!function_exists('load_media')) {
    function load_media($model)
    {
        $files = [];
        foreach ($model->media as $mediaItem) {
            $files[] = [
                'id'        => $mediaItem->id,
                'type'      => $mediaItem->mime_type,
                'file_name' => $mediaItem->file_name,
                'size'      => $mediaItem->human_readable_size,
                'name'      => $mediaItem->getCustomProperty('name'),
                'path'      => $mediaItem->diskname == 'public' ? $mediaItem->getPath() : '',
                'url'       => $mediaItem->diskname == 'public' ? $mediaItem->getFullUrl() : '',
            ];
        }
        unset($model->media);
        $model->media = $files;
        return $model;
    }
}

// SKU
if (!function_exists('sku')) {
    function sku()
    {
        return (string) \Ulid\Ulid::generate(true);
    }
}

// // Generate SKU
// if (!function_exists('generateSKU')) {
//     function generateSKU($number, $update = 0)
//     {
//         $sku = intval(mps_config('sku'));
//         if ($update) {
//             \Modules\MPS\Models\Setting::create(['mps_key' => 'sku', 'mps_value' => $sku + $update]);
//         }
//         return generateEAN($sku);
//     }
// }

// // Generate EAN13 from number (length should be 9)
// if (!function_exists('generateEAN')) {
//     function generateEAN($number)
//     {
//         $code       = '260' . str_pad($number, 9, '0', STR_PAD_LEFT);
//         $sum        = 0;
//         $weightflag = true;
//         for ($i = strlen($code) - 1; $i >= 0; $i--) {
//             $sum += (int)$code[$i] * ($weightflag ? 3 : 1);
//             $weightflag = !$weightflag;
//         }
//         $code .= (10 - ($sum % 10)) % 10;
//         return $code;
//     }
// }
