<?php

namespace Modules\MPS\Database\Seeders;

use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Support\Str;
use Modules\MPS\Models\Item;
use Illuminate\Database\Seeder;
use Modules\MPS\Models\Setting;

class MyDatabaseSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create();

        echo "Creating MPS Settings\n";
        factory(Setting::class)->create(['mps_key' => 'name', 'mps_value' => 'Modern POS Solution']);
        factory(Setting::class)->create(['mps_key' => 'short_name', 'mps_value' => 'MPS']);
        factory(Setting::class)->create(['mps_key' => 'default_logo', 'mps_value' => url('storage/images/logo.png')]);
        factory(Setting::class)->create(['mps_key' => 'timezone', 'mps_value' => 'Asia/Kuala_Lumpur']);
        factory(Setting::class)->create(['mps_key' => 'company', 'mps_value' => 'Tecdiary']);
        factory(Setting::class)->create(['mps_key' => 'email', 'mps_value' => 'noreply@tecdiary.com']);
        factory(Setting::class)->create(['mps_key' => 'phone', 'mps_value' => '0101234567']);
        factory(Setting::class)->create(['mps_key' => 'address', 'mps_value' => 'Business Address']);
        factory(Setting::class)->create(['mps_key' => 'state', 'mps_value' => 'IN-DL']);
        factory(Setting::class)->create(['mps_key' => 'country', 'mps_value' => 'IN']);
        factory(Setting::class)->create(['mps_key' => 'theme', 'mps_value' => 'dark']);
        factory(Setting::class)->create(['mps_key' => 'fixed_layout', 'mps_value' => '1']);
        factory(Setting::class)->create(['mps_key' => 'hide_id', 'mps_value' => '1']);
        factory(Setting::class)->create(['mps_key' => 'rows', 'mps_value' => '10']);
        factory(Setting::class)->create(['mps_key' => 'language', 'mps_value' => 'en']);
        factory(Setting::class)->create(['mps_key' => 'dateformat', 'mps_value' => 'DD/MM/YYYY']);
        factory(Setting::class)->create(['mps_key' => 'stock', 'mps_value' => '1']);
        factory(Setting::class)->create(['mps_key' => 'decimals', 'mps_value' => '2']);
        factory(Setting::class)->create(['mps_key' => 'quantity_decimals', 'mps_value' => '2']);
        factory(Setting::class)->create(['mps_key' => 'max_discount', 'mps_value' => '20']);
        factory(Setting::class)->create(['mps_key' => 'quick_cash', 'mps_value' => '10|50|100|500|1000']);
        factory(Setting::class)->create(['mps_key' => 'confirmation', 'mps_value' => 'modal']);
        factory(Setting::class)->create(['mps_key' => 'show_tax', 'mps_value' => '0']);
        factory(Setting::class)->create(['mps_key' => 'show_image', 'mps_value' => '1']);
        factory(Setting::class)->create(['mps_key' => 'show_tax_summary', 'mps_value' => '1']);
        factory(Setting::class)->create(['mps_key' => 'show_discount', 'mps_value' => '0']);
        factory(Setting::class)->create(['mps_key' => 'dimension_unit', 'mps_value' => 'cm']);
        factory(Setting::class)->create(['mps_key' => 'weight_unit', 'mps_value' => 'kg']);
        factory(Setting::class)->create(['mps_key' => 'loader', 'mps_value' => 'circle']);
        factory(Setting::class)->create(['mps_key' => 'restaurant', 'mps_value' => '1']);
        factory(Setting::class)->create(['mps_key' => 'reference', 'mps_value' => 'monthly']);
        factory(Setting::class)->create(['mps_key' => 'label_width', 'mps_value' => '300']);
        factory(Setting::class)->create(['mps_key' => 'label_height', 'mps_value' => '150']);
        factory(Setting::class)->create(['mps_key' => 'auto_open_order', 'mps_value' => '1']);
        factory(Setting::class)->create(['mps_key' => 'json_string', 'mps_value' => '{"version":"4.2.0","objects":[{"type":"group","version":"4.2.0","originX":"left","originY":"top","left":9,"top":90,"width":281,"height":51,"fill":"rgb(0,0,0)","stroke":null,"strokeWidth":0,"strokeDashArray":null,"strokeLineCap":"butt","strokeDashOffset":0,"strokeLineJoin":"miter","strokeMiterLimit":4,"scaleX":1,"scaleY":1,"angle":0,"flipX":false,"flipY":false,"opacity":1,"shadow":null,"visible":true,"backgroundColor":"","fillRule":"nonzero","paintFirst":"fill","globalCompositeOperation":"source-over","skewX":0,"skewY":0,"id":"replace-barcode","selectable":false,"objects":[{"type":"rect","version":"4.2.0","originX":"center","originY":"center","left":0,"top":0,"width":280,"height":50,"fill":"#fff","stroke":"#ccc","strokeWidth":1,"strokeDashArray":null,"strokeLineCap":"butt","strokeDashOffset":0,"strokeLineJoin":"miter","strokeMiterLimit":4,"scaleX":1,"scaleY":1,"angle":0,"flipX":false,"flipY":false,"opacity":1,"shadow":null,"visible":true,"backgroundColor":"","fillRule":"nonzero","paintFirst":"fill","globalCompositeOperation":"source-over","skewX":0,"skewY":0,"rx":4,"ry":4,"selectable":false},{"type":"text","version":"4.2.0","originX":"center","originY":"center","left":0,"top":0,"width":76.45,"height":18.08,"fill":"#333","stroke":null,"strokeWidth":1,"strokeDashArray":null,"strokeLineCap":"butt","strokeDashOffset":0,"strokeLineJoin":"miter","strokeMiterLimit":4,"scaleX":1,"scaleY":1,"angle":0,"flipX":false,"flipY":false,"opacity":1,"shadow":null,"visible":true,"backgroundColor":"","fillRule":"nonzero","paintFirst":"fill","globalCompositeOperation":"source-over","skewX":0,"skewY":0,"text":"BARCODE","fontSize":16,"fontWeight":"normal","fontFamily":"Times New Roman","fontStyle":"normal","lineHeight":1.16,"underline":false,"overline":false,"linethrough":false,"textAlign":"left","textBackgroundColor":"","charSpacing":0,"selectable":false,"styles":{}}]},{"type":"textbox","version":"4.2.0","originX":"left","originY":"top","left":10,"top":41,"width":280,"height":39.05,"fill":"#000000","stroke":null,"strokeWidth":1,"strokeDashArray":null,"strokeLineCap":"butt","strokeDashOffset":0,"strokeLineJoin":"miter","strokeMiterLimit":4,"scaleX":1,"scaleY":1,"angle":0,"flipX":false,"flipY":false,"opacity":1,"shadow":null,"visible":true,"backgroundColor":"","fillRule":"nonzero","paintFirst":"fill","globalCompositeOperation":"source-over","skewX":0,"skewY":0,"text":"--- Item Name ---\n--- Other info for item. --- ","fontSize":16,"fontWeight":"normal","fontFamily":"Times New Roman","fontStyle":"normal","lineHeight":1.16,"underline":false,"overline":false,"linethrough":false,"textAlign":"left","textBackgroundColor":"","charSpacing":0,"minWidth":20,"splitByGrapheme":false,"editable":false,"selectable":true,"styles":{}},{"type":"text","version":"4.2.0","originX":"left","originY":"top","left":268.45,"top":139.66,"width":137.29,"height":18.08,"fill":"rgb(0,0,0)","stroke":null,"strokeWidth":1,"strokeDashArray":null,"strokeLineCap":"butt","strokeDashOffset":0,"strokeLineJoin":"miter","strokeMiterLimit":4,"scaleX":1,"scaleY":1,"angle":270.13,"flipX":false,"flipY":false,"opacity":1,"shadow":null,"visible":true,"backgroundColor":"","fillRule":"nonzero","paintFirst":"fill","globalCompositeOperation":"source-over","skewX":0,"skewY":0,"text":"--- Price: 1,000.00 ---","fontSize":16,"fontWeight":"normal","fontFamily":"Times New Roman","fontStyle":"normal","lineHeight":1.16,"underline":false,"overline":false,"linethrough":false,"textAlign":"left","textBackgroundColor":"","charSpacing":0,"selectable":true,"styles":{}},{"type":"text","version":"4.2.0","originX":"left","originY":"top","left":11,"top":10,"width":134.24,"height":18.08,"fill":"rgb(0,0,0)","stroke":null,"strokeWidth":1,"strokeDashArray":null,"strokeLineCap":"butt","strokeDashOffset":0,"strokeLineJoin":"miter","strokeMiterLimit":4,"scaleX":1.21,"scaleY":1.21,"angle":0,"flipX":false,"flipY":false,"opacity":1,"shadow":null,"visible":true,"backgroundColor":"","fillRule":"nonzero","paintFirst":"fill","globalCompositeOperation":"source-over","skewX":0,"skewY":0,"text":"Modern POS Solution","fontSize":16,"fontWeight":"normal","fontFamily":"Times New Roman","fontStyle":"normal","lineHeight":1.16,"underline":false,"overline":false,"linethrough":false,"textAlign":"left","textBackgroundColor":"","charSpacing":0,"selectable":true,"styles":{}}]}']);
        factory(Setting::class)->create(['mps_key' => 'svg_string', 'mps_value' => '<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="300" height="150" viewBox="0 0 300 150" xml:space="preserve">
<desc>Created with Fabric.js 4.2.0</desc>
<defs>
</defs>
<g transform="matrix(1 0 0 1 149.5 115.5)" id="replace-barcode"  >
<g style=""   >
        <g transform="matrix(1 0 0 1 0 0)"  >
<rect style="stroke: rgb(204,204,204); stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-dashoffset: 0; stroke-linejoin: miter; stroke-miterlimit: 4; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;"  x="-140" y="-25" rx="4" ry="4" width="280" height="50" />
</g>
        <g transform="matrix(1 0 0 1 0 0)" style=""  >
        <text xml:space="preserve" font-family="Times New Roman" font-size="16" font-style="normal" font-weight="normal" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-dashoffset: 0; stroke-linejoin: miter; stroke-miterlimit: 4; fill: rgb(51,51,51); fill-rule: nonzero; opacity: 1; white-space: pre;" ><tspan x="-38.23" y="5.03" >BARCODE</tspan></text>
</g>
</g>
</g>
<g transform="matrix(1 0 0 1 150.5 61.03)" style=""  >
        <text xml:space="preserve" font-family="Times New Roman" font-size="16" font-style="normal" font-weight="normal" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-dashoffset: 0; stroke-linejoin: miter; stroke-miterlimit: 4; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1; white-space: pre;" ><tspan x="-140" y="-5.46" >--- Item Name ---</tspan><tspan x="-140" y="15.51" style="white-space: pre; ">--- Other info for item. --- </tspan></text>
</g>
<g transform="matrix(0 -1 1 0 278.14 70.54)" style=""  >
        <text xml:space="preserve" font-family="Times New Roman" font-size="16" font-style="normal" font-weight="normal" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-dashoffset: 0; stroke-linejoin: miter; stroke-miterlimit: 4; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1; white-space: pre;" ><tspan x="-68.64" y="5.03" >--- Price: 1,000.00 ---</tspan></text>
</g>
<g transform="matrix(1.21 0 0 1.21 92.5 21.5)" style=""  >
        <text xml:space="preserve" font-family="Times New Roman" font-size="16" font-style="normal" font-weight="normal" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-dashoffset: 0; stroke-linejoin: miter; stroke-miterlimit: 4; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1; white-space: pre;" ><tspan x="-67.12" y="5.03" >Modern POS Solution</tspan></text>
</g>
</svg>']);

        factory(\Modules\MPS\Models\Sequence::class)->create();

        echo "Creating Custom Fields\n";
        factory(\Modules\MPS\Models\Field::class, 4)->create();

        echo "Creating Accounts & Locations\n";

        Carbon::setTestNow(now()->subDays(180));
        $aIds = factory(\Modules\MPS\Models\Account::class, 4)->create()->each(function ($a, $i) use ($faker) {

            if (($i + 1) % 2 == 0) {
                $location = factory(\Modules\MPS\Models\Location::class)->create(['account_id' => $a->id]);
                $location->registers()->saveMany(
                    factory(\Modules\MPS\Models\Register::class, mt_rand(2, 6))->make()
                );
                session(['location_id' => $location->id]);
            }
        });
        Carbon::setTestNow();
        factory(Setting::class)->create(['mps_key' => 'default_account', 'mps_value' => $faker->randomElement($aIds)->id]);


        $users = \Modules\MPS\Models\User::all();
        $users->each(function ($user, $key) {
            $user->saveSettings(['number' => 'E' . ($key + 1)]);
        });
        $locations = \Modules\MPS\Models\Location::all();
        $uIds      = $users->pluck('id');
        $super     = $users->firstWhere('username', 'super');
        $super->assignRole('super');
        $super->update(['location_id' => $faker->randomElement($locations)->id]);
        $admin = $users->firstWhere('username', 'admin');
        $admin->assignRole('admin');
        $admin->update(['location_id' => $faker->randomElement($locations)->id]);
        $staff = $users->firstWhere('username', 'staff');
        $staff->assignRole('staff');
        $staff->update(['location_id' => $faker->randomElement($locations)->id]);

        echo "Creating Customers, Suppliers & Settings\n";

        Carbon::setTestNow(now()->subDays(180));
        $default_customer = factory(\Modules\MPS\Models\Customer::class)->create([
            'name'    => 'Walk-in Customer',
            'company' => 'Walk-in Customer',
            'user_id' => $faker->randomElement($uIds),
        ]);

        factory(Setting::class)->create(['mps_key' => 'default_customer', 'mps_value' => $default_customer]);
        factory(\Modules\MPS\Models\Customer::class, 9)->create(['user_id' => $faker->randomElement($uIds)]);
        factory(\Modules\MPS\Models\Supplier::class, 10)->create(['user_id' => $faker->randomElement($uIds)]);
        Carbon::setTestNow();

        foreach ($uIds as $uId) {
            factory(\Modules\MPS\Models\Customer::class, 5)->create(['user_id' => $uId]);
            factory(\Modules\MPS\Models\Supplier::class, 5)->create(['user_id' => $uId]);
        }
        // sleep(1);
        // $cIds = [];
        echo "Creating Brands, Categories, Expenses & Incomes\n";
        $brands = factory(\Modules\MPS\Models\Brand::class, 25)->create();
        // $cIds = factory(\Modules\MPS\Models\Category::class, 15)->create();
        $cIds = factory(\Modules\MPS\Models\Category::class, 15)->create()->each(function ($c) use ($faker, $aIds, $uIds, $locations) {
            session(['location_id' => $locations->random()->id]);
            Carbon::setTestNow();
            $date = now()->subDays(mt_rand(2, 400));
            Carbon::setTestNow(Carbon::parse($date));

            // $c->items()->saveMany(factory(\Modules\MPS\Models\Item::class, 10)->make()

            $c->expenses()->saveMany(factory(\Modules\MPS\Models\Expense::class, mt_rand(5, 10))->make(['date' => $date, 'account_id' => $faker->randomElement($aIds), 'user_id' => $faker->randomElement($uIds), 'approved_by_id' => $uIds[0]]));
            $c->incomes()->saveMany(factory(\Modules\MPS\Models\Income::class, mt_rand(5, 10))->make(['date' => $date, 'account_id' => $faker->randomElement($aIds), 'user_id' => $faker->randomElement($uIds)]));
        });
        factory(Setting::class)->create(['mps_key' => 'default_category', 'mps_value' => $faker->randomElement($cIds)]);

        echo "Creating Taxes\n";
        $tax1[] = factory(\Modules\MPS\Models\Tax::class)->create(['name' => 'CGST @ 9%', 'code' => 'cgst@9', 'rate' => 9, 'compound' => false, 'state' => true, 'same' => true])->toArray()['id'];
        $tax1[] = factory(\Modules\MPS\Models\Tax::class)->create(['name' => 'SGST @ 9%', 'code' => 'sgst@9', 'rate' => 9, 'compound' => false, 'state' => true, 'same' => true])->toArray()['id'];
        $tax1[] = factory(\Modules\MPS\Models\Tax::class)->create(['name' => 'IGST @ 18%', 'code' => 'igst@18', 'rate' => 18, 'compound' => false, 'recoverable' => true, 'state' => true, 'same' => false])->toArray()['id'];
        $tax2[] = factory(\Modules\MPS\Models\Tax::class)->create(['name' => 'CGST @ 11%', 'code' => 'cgst@11', 'rate' => 11, 'compound' => false, 'state' => true, 'same' => true])->toArray()['id'];
        $tax2[] = factory(\Modules\MPS\Models\Tax::class)->create(['name' => 'SGST @ 11%', 'code' => 'sgst@11', 'rate' => 11, 'compound' => false, 'state' => true, 'same' => true])->toArray()['id'];
        $tax2[] = factory(\Modules\MPS\Models\Tax::class)->create(['name' => 'IGST @ 22%', 'code' => 'igst@22', 'rate' => 22, 'compound' => false, 'recoverable' => true, 'state' => true, 'same' => false])->toArray()['id'];
        $tax3[] = factory(\Modules\MPS\Models\Tax::class)->create(['name' => 'CGST @ 14%', 'code' => 'cgst@14', 'rate' => 14, 'compound' => false, 'state' => true, 'same' => true])->toArray()['id'];
        $tax3[] = factory(\Modules\MPS\Models\Tax::class)->create(['name' => 'SGST @ 14%', 'code' => 'sgst@14', 'rate' => 14, 'compound' => false, 'state' => true, 'same' => true])->toArray()['id'];
        $tax3[] = factory(\Modules\MPS\Models\Tax::class)->create(['name' => 'IGST @ 28%', 'code' => 'igst@28', 'rate' => 28, 'compound' => false, 'recoverable' => true, 'state' => true, 'same' => false])->toArray()['id'];
        $tax4[] = factory(\Modules\MPS\Models\Tax::class)->create(['name' => 'Sales & Services Tax', 'code' => 'SST', 'compound' => false, 'state' => false])->toArray()['id'];
        $tax5[] = factory(\Modules\MPS\Models\Tax::class)->create(['name' => 'Value Added Tax', 'code' => 'VAT', 'compound' => false, 'state' => false])->toArray()['id'];
        $tax6[] = factory(\Modules\MPS\Models\Tax::class)->create(['name' => 'CESS', 'code' => 'cess', 'compound' => true, 'state' => false])->toArray()['id'];

        echo "Creating Events\n";
        factory(\Modules\MPS\Models\Event::class, 30)->create(['user_id' => $faker->randomElement($uIds)]);

        echo "Creating Units\n";
        $meter = factory(\Modules\MPS\Models\Unit::class)->create(['name' => 'Meter', 'code' => 'm']);
        $piece = factory(\Modules\MPS\Models\Unit::class)->create(['name' => 'Piece', 'code' => 'pc']);
        $gram  = factory(\Modules\MPS\Models\Unit::class)->create(['name' => 'Gram', 'code' => 'g']);
        factory(\Modules\MPS\Models\Unit::class)->create(['name' => 'Centimeter', 'code' => 'cm', 'base_id' => $meter->id, 'operator' => '/', 'operation_value' => 100]);
        factory(\Modules\MPS\Models\Unit::class)->create(['name' => 'Dozen', 'code' => 'dozen', 'base_id' => $piece->id, 'operator' => '*', 'operation_value' => 12]);
        factory(\Modules\MPS\Models\Unit::class)->create(['name' => 'Kilogram', 'code' => 'kg', 'base_id' => $gram->id, 'operator' => '*', 'operation_value' => 1000]);
        $units = [$meter->id, $piece->id, $gram->id];

        echo "Creating Items 1 - 50\n";
        $variants = [
            ['name' => 'Size', 'options' => ['S', 'M', 'L', 'XL']],
            ['name' => 'Color', 'options' => ['Black', 'Blue', 'Green', 'Red', 'White']],
        ];
        $locations = \Modules\MPS\Models\Location::all();
        $taxes     = [$tax1, $tax2, $tax3, $tax4, $tax5];
        $items     = [];
        for ($i = 6; $i <= 55; $i++) {
            $p = factory(\Modules\MPS\Models\Item::class)->create([
                'unit_id'  => $faker->randomElement($units),
                'brand_id' => $faker->randomElement($brands)->id,
                'name'     => $name = 'Item ' . ($i < 10 ? '0' . $i : $i),
                'slug'     => Str::slug($name),
            ]);

            $p->taxes()->sync($taxes[mt_rand(0, 4)]);
            $p->categories()->sync($faker->randomElement($cIds));
            foreach ($locations as $location) {
                session(['location_id' => $location->id]);
                $p->stock()->create(['quantity' => mt_rand(10, 20)]);
            }

            $items[] = $p;
        }
        $items = $pitems = collect($items);
        // $items     = factory(\Modules\MPS\Models\Item::class, 50)->create([
        //     'unit_id'  => $faker->randomElement($units),
        //     'brand_id' => $faker->randomElement($brands)->id,
        // ])->each(function ($p) use ($faker, $cIds, $taxes, $locations) {
        //     $p->taxes()->sync($taxes[mt_rand(0, 4)]);
        //     $p->categories()->sync($faker->randomElement($cIds));
        //     foreach ($locations as $location) {
        //         session(['location_id' => $location->id]);
        //         $p->stock()->create(['quantity' => mt_rand(10, 20)]);
        //     }
        // });
        echo "Creating Items 51 - 52 for scale barcodes\n";
        $scale_barcode_item = factory(\Modules\MPS\Models\Item::class)->create([
            'cost'      => '10',
            'price'     => '20',
            'code'      => '00001',
            'symbology' => 'code39',
            'unit_id'   => $faker->randomElement($units),
            'name'      => $name = 'Test Product 1 for Weight Barcode',
            'slug'      => Str::slug($name),
            'brand_id'  => $faker->randomElement($brands)->id,
        ]);
        $scale_barcode_item->categories()->sync($faker->randomElement($cIds));
        foreach ($locations as $location) {
            session(['location_id' => $location->id]);
            $scale_barcode_item->stock()->create(['quantity' => mt_rand(10, 20)]);
        }
        $scale_barcode_item2 = factory(\Modules\MPS\Models\Item::class)->create([
            'cost'      => '10',
            'price'     => '20',
            'code'      => '00002',
            'symbology' => 'code39',
            'unit_id'   => $faker->randomElement($units),
            'name'      => $name = 'Test Product 2 for Weight Barcode',
            'slug'      => Str::slug($name),
            'brand_id'  => $faker->randomElement($brands)->id,
        ]);
        $scale_barcode_item2->taxes()->sync($taxes[mt_rand(0, 4)]);
        $scale_barcode_item2->categories()->sync($faker->randomElement($cIds));
        foreach ($locations as $location) {
            session(['location_id' => $location->id]);
            $scale_barcode_item2->stock()->create(['quantity' => mt_rand(10, 20)]);
        }

        // $ingredients = factory(\Modules\MPS\Models\Ingredient::class, mt_rand(10, 30))->create();
        $modifiers = factory(\Modules\MPS\Models\Modifier::class, mt_rand(10, 30))->create()->each(function ($m) use ($faker, $items) {
            $items = $faker->unique()->randomElements($items, mt_rand(2, 5));
            foreach ($items as $item) {
                $variation = $item->variations()->exists() ? $item->variations()->inRandomOrder()->first() : null;
                $m->options()->save(factory(\Modules\MPS\Models\ModifierOption::class)->make(['item_id' => $item->id, 'variation_id' => $variation ? $variation->id : null]));
            }
        });

        echo "Creating Items 53 - 57\n";
        $mItems = [];
        for ($i = 1; $i < 5; $i++) {
            $item = factory(\Modules\MPS\Models\Item::class)->create([
                'code'         => 'Item0' . $i,
                'symbology'    => 'code128',
                'name'         => $name = 'Item 0' . $i,
                'slug'         => Str::slug($name),
                'variants'     => $variants,
                'has_variants' => 1,
            ]);
            $item->taxes()->sync($taxes[$i - 1]);
            $item->categories()->sync($faker->randomElement($cIds));
            foreach ($locations as $location) {
                session(['location_id' => $location->id]);
                $item->stock()->create(['quantity' => mt_rand(5, 20)]);
            }
            // session(['location_id' => $faker->randomElement($locations)->id]);
            $metas = self::permutations($variants);
            foreach ($metas as $meta) {
                $item_variation = factory(\Modules\MPS\Models\Variation::class)->create(['item_id' => $item->id, 'meta' => $meta, 'code' => 'Variation Color ' . $meta['Color'] . ' Size ' . $meta['Size']]);
                foreach ($locations as $location) {
                    session(['location_id' => $location->id]);
                    factory(\Modules\MPS\Models\VariationStock::class)->create(['variation_id' => $item_variation->id, 'location_id' => $location->id]);
                }
            }

            $item_modifiers[] = $faker->randomElement($modifiers->pluck('id'), mt_rand(1, 3));
            $item->modifiers()->sync($item_modifiers);
            $mItems[] = $item;
        }
        $item5 = factory(\Modules\MPS\Models\Item::class)->create([
            'code'      => 'Item05',
            'symbology' => 'code128',
            'name'      => $name = 'ITEM 05',
            'slug'      => Str::slug($name),
        ]);
        $item_modifiers[] = $faker->randomElement($modifiers->pluck('id'), mt_rand(1, 3));
        $item5->categories()->sync($faker->randomElement($cIds));
        $item5->modifiers()->sync($item_modifiers);
        foreach ($locations as $location) {
            session(['location_id' => $location->id]);
            $item5->stock()->create(['quantity' => 20]);
        }
        $mItems[] = $item5;

        echo "Creating Recipes\n";
        for ($i = 1; $i < 10; $i++) {
            $iItems = $faker->randomElements($items, mt_rand(2, 5));
            $cost   = collect($iItems)->sum('cost');
            $price  = ceil($cost * 1.5);
            $item   = factory(\Modules\MPS\Models\Item::class)->create([
                'code'         => 'Recipe0' . $i,
                'symbology'    => 'code128',
                'name'         => $name = 'Recipe 0' . $i,
                'slug'         => Str::slug($name),
                'type'         => 'recipe',
                'variants'     => null,
                'has_variants' => null,
                'cost'         => $cost,
                'price'        => $price,
                'min_price'    => $cost * 1.2,
                'max_price'    => $cost * 1.8,
            ]);
            $portion = factory(\Modules\MPS\Models\Portion::class)->create(['item_id' => $item->id, 'cost' => $cost, 'price' => $price]);
            foreach ($iItems as $iItem) {
                // $item->ingredients()->save($ingredient, ['quantity' => mt_rand(5, 50)]);
                factory(\Modules\MPS\Models\PortionItem::class)->create([
                    'portion_id' => $portion->id,
                    'item_id'    => $iItem->id,
                ]);
            }
            $item->categories()->sync($faker->randomElement($cIds));
            $mItems[] = $item;
        }

        echo "Creating Services\n";
        for ($i = 1; $i < 10; $i++) {
            $cost  = mt_rand(10, 99);
            $price = ceil($cost * 2);
            $item  = factory(\Modules\MPS\Models\Item::class)->create([
                'code'         => 'Service0' . $i,
                'symbology'    => 'code128',
                'name'         => $name = 'Service 0' . $i,
                'slug'         => Str::slug($name),
                'type'         => 'service',
                'variants'     => null,
                'has_variants' => null,
                'cost'         => $cost,
                'price'        => $price,
                'min_price'    => $cost * 1.5,
                'max_price'    => $cost * 2.5,
            ]);

            $item_modifiers[] = $faker->randomElement($modifiers->pluck('id'), mt_rand(1, 3));
            $item->modifiers()->sync($item_modifiers);
            $item->categories()->sync($faker->randomElement($cIds));
            $mItems[] = $item;
        }

        echo "Creating Combo Deals\n";
        $items = $items->merge($mItems);
        // $items = \Modules\MPS\Models\Item::all();
        // $cIds  = \Modules\MPS\Models\Category::all();
        for ($i = 1; $i <= 5; $i++) {
            $item = factory(\Modules\MPS\Models\Item::class)->create([
                'code'         => 'Deal0' . $i,
                'symbology'    => 'code128',
                'name'         => $name = 'Combo Deal 0' . $i,
                'slug'         => Str::slug($name),
                'type'         => 'combo',
                'variants'     => null,
                'has_variants' => null,
            ]);

            $cost       = 0;
            $price      = 0;
            $portion    = factory(\Modules\MPS\Models\Portion::class)->create(['item_id' => $item->id, 'cost' => $cost, 'price' => $price]);
            $essentials = $faker->randomElements($items, mt_rand(2, 5));
            // foreach ($essentials as $value) {
            //     $item->ingredients()->save($value, ['quantity' => mt_rand(1, 2)]);
            // }
            foreach ($essentials as $essential) {
                $variation = $essential->variants && $essential->variations ? $faker->randomElement($essential->variations) : null;
                $cost      = $cost + (($variation && $variation->cost > 0 ? $variation->cost : $essential->cost) * $essential->quantity);
                factory(\Modules\MPS\Models\PortionEssential::class)->create([
                    'item_id'      => $essential->id,
                    'portion_id'   => $portion->id,
                    'variation_id' => $variation ? $variation->id : null,
                ]);
            }

            // $groups = factory(\Modules\MPS\Models\PortionChoosable::class, 2)->create(['item_id' => $essential->id, 'portion_id' => $portion->id]);
            $groups = factory(\Modules\MPS\Models\PortionChoosable::class, 2)->create(['portion_id' => $portion->id]);
            foreach ($groups as $group) {
                $gi     = 1;
                $gItems = $faker->unique()->randomElements($items, mt_rand(2, 5));
                foreach ($gItems as $gItem) {
                    $variation = $gItem->variants && $gItem->variations ? $faker->randomElement($gItem->variations) : null;
                    if ($gi == 1) {
                        $cost = $cost + ($variation && $variation->cost > 0 ? $variation->cost : $gItem->cost);
                    }
                    factory(\Modules\MPS\Models\PortionChoosableItem::class)->create([
                        'quantity'             => 1,
                        'item_id'              => $gItem->id,
                        'portion_choosable_id' => $group->id,
                        'variation_id'         => $variation ? $variation->id : null,
                    ]);
                    $gi++;
                }
            }

            // foreach ($essentials as $value) {
            //     $variation    = $value->variants && $value->variations ? $faker->randomElement($value->variations) : null;
            //     $variation_id = $variation ? $variation->id : null;
            //     $item->essentials()->save($value, ['quantity' => mt_rand(1, 8), 'variation_id' => $variation_id]);
            // }
            // $item_modifiers[] = $faker->randomElement($modifiers->pluck('id'), mt_rand(1, 3));
            // $item->modifiers()->sync($item_modifiers);
            $item->categories()->sync($faker->randomElement($cIds));
            $price = ceil($cost * 2);
            $item->update([
                'cost'      => $cost,
                'price'     => $price,
                'min_price' => $cost * 1.5,
                'max_price' => $cost * 2.5,
            ]);
            $portion->update(['cost' => $cost, 'price' => $price]);
            $mItems[] = $item;
        }

        echo "Simple Promotion\n";
        $c1     = $faker->randomElement($cIds)->id;
        $c2     = $faker->randomElement($cIds)->id;
        $items  = collect($mItems)->pluck('id')->toArray();
        $simple = factory(\Modules\MPS\Models\Promotion::class)->create(['discount' => '10']);
        $simple->items()->sync($items);
        $simple->categories()->sync([$c1]);

        $advance = factory(\Modules\MPS\Models\Promotion::class)->create([
            'discount'        => '15',
            'name'            => 'Advance',
            'type'            => 'advance',
            'quantity_to_buy' => 2,
        ]);
        $advance->items()->sync([$item5->id]);
        $advance->categories()->sync([$c2]);

        $advance = factory(\Modules\MPS\Models\Promotion::class)->create([
            'discount'        => '0',
            'name'            => 'Buy i6 & get i8',
            'type'            => 'BXGY',
            'item_id_to_buy'  => $pitems->where('slug', 'item-06')->first()->id,
            'quantity_to_buy' => 2,
            'item_id_to_get'  => $pitems->where('slug', 'item-08')->first()->id,
            'quantity_to_get' => 1,
        ]);
        $advance = factory(\Modules\MPS\Models\Promotion::class)->create([
            'discount'        => '0',
            'name'            => 'Buy i6 & get i9',
            'type'            => 'BXGY',
            'item_id_to_buy'  => $pitems->where('slug', 'item-06')->first()->id,
            'quantity_to_buy' => 2,
            'item_id_to_get'  => $pitems->where('slug', 'item-09')->first()->id,
            'quantity_to_get' => 1,
        ]);
        $advance = factory(\Modules\MPS\Models\Promotion::class)->create([
            'discount'        => '0',
            'name'            => 'Buy i7 & get i8',
            'type'            => 'BXGY',
            'item_id_to_buy'  => $pitems->where('slug', 'item-07')->first()->id,
            'quantity_to_buy' => 1,
            'item_id_to_get'  => $pitems->where('slug', 'item-08')->first()->id,
            'quantity_to_get' => 1,
        ]);

        session()->flush();
    }

    protected static function permutations(array $array)
    {
        $metas  = [];
        $sizes  = $array[0]['options'];
        $colors = $array[1]['options'];
        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                $metas[] = ['Color' => $color, 'Size' => $size];
            }
        }
        return $metas;
    }
}
