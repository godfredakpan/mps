<?php

namespace Modules\MPS\Database\Seeders;

use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Support\Arr;
use Modules\MPS\Models\Item;
use Modules\MPS\Models\User;
use Illuminate\Database\Seeder;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Location;
use Modules\MPS\Actions\TaxAction;
use Illuminate\Support\Facades\Auth;
use Modules\MPS\Actions\DiscountAction;

class SalesTableSeeder extends Seeder
{
    protected function create($allItems, $account, $location, $uId, $fc = false)
    {
        session(['location_id' => $location->id]);
        $customer = $fc ? Customer::where('user_id', $uId)->first() : Customer::where('user_id', $uId)->inRandomOrder()->first();
        factory('Modules\MPS\Models\Sale', mt_rand(1, 5))->make(['user_id' => $uId, 'customer_id' => $customer->id])->each(function ($sale) use ($allItems, $account, $location, $customer) {
            Carbon::setTestNow();
            Carbon::setTestNow(now()->subDays(mt_rand(2, 400)));
            $sale->date = $sale->created_at = $sale->updated_at = now();
            $items = $allItems->random(mt_rand(1, 5));
            $total = $total_items = 0;
            $saleItems = [];
            foreach ($items as $item) {
                $qty = mt_rand(1, 3);
                $applicable_taxes = $item->taxes->filter(function ($tax) use ($location, $customer) {
                    if ($tax->state) {
                        $check = $customer->state == $location->state;
                        return $tax->same ? $check : !$check;
                    }
                    return true;
                })->pluck('id')->all();
                $taxes = (new TaxAction)->calculate($applicable_taxes, $item->price, $qty);
                $saleItem = factory('Modules\MPS\Models\SaleItem')->make([
                    'item_id'          => $item->id,
                    'quantity'         => $qty,
                    'price'            => $item->price,
                    'net_price'        => $item->price,
                    'tax_amount'       => $tax = $taxes && $taxes->isNotEmpty() ? $taxes->sum('amount') : 0,
                    'total_tax_amount' => formatDecimal($tax * $qty, 2),
                    'unit_price'       => $unit_price = $item->price + $tax,
                    'subtotal'         => ($unit_price * $qty),
                    'taxes'            => $taxes,
                    'item_taxes'       => Arr::pluck($item->taxes, 'id'),
                ]);
                $total += $saleItem->subtotal;
                unset($saleItem['cost'], $saleItem['net_cost'], $saleItem['unit_cost']);
                $saleItems[] = $saleItem;
            }

            $v['type'] = 'nett';
            $v['total'] = $total;
            $v['item_tax_amount'] = formatDecimal(collect($saleItems)->sum('total_tax_amount'), 2);
            $discount_amount = (new DiscountAction)->calculate(0, $total);
            $order_taxes = (new TaxAction)->calculate(0, ($total - $discount_amount));
            $order_tax_amount = $order_taxes ? formatDecimal($order_taxes->sum('amount'), 2) : 0;
            $v['total_tax_amount'] = formatDecimal($v['item_tax_amount'] + $order_tax_amount, 2);
            $v['grand_total'] = formatDecimal($total - $discount_amount + $order_tax_amount, 2);
            $rT = (new TaxAction)->recoverableTaxAmount($sale->taxes);
            $v['recoverable_tax_amount'] = $rT['recoverable_tax_amount'];
            $v['recoverable_tax_calculated_on'] = $rT['recoverable_tax_calculated_on'];
            foreach ($saleItems as $item) {
                $rT = (new TaxAction)->recoverableTaxAmount($item['taxes']);
                $v['recoverable_tax_amount'] += $rT['recoverable_tax_amount'];
                $v['recoverable_tax_calculated_on'] += $rT['recoverable_tax_calculated_on'];
            }

            $sale->fill($v)->save();
            foreach ($saleItems as $item) {
                $taxes = $item->taxes;
                unset($item->taxes);
                $sale->items()->save($item);
                if ($taxes && $taxes->isNotEmpty()) {
                    $item->taxes()->sync($taxes->toArray());
                }
            }
            if (!$sale->draft) {
                if (mt_rand(0, 1)) {
                    $sale->customer->payments()->create(
                        factory('Modules\MPS\Models\Payment')->make([
                            'received'   => 1,
                            'gateway'    => 'cash',
                            'sale_id'    => $sale->id,
                            'account_id' => $account->id,
                            'user_id'    => $sale->user_id,
                            'amount'     => $v['grand_total'],
                        ])->toArray()
                    );
                }
            }
        });
    }

    public function run()
    {
        $faker = Factory::create();

        echo "Creating Sales\n";
        $users     = User::all();
        $accounts  = Account::all();
        $locations = Location::all();
        $allItems  = Item::inRandomOrder()->limit(30)->get();
        for ($i = 1; $i <= mt_rand(10, 30); $i++) {
            $location = $locations->random();
            Auth::login($user = $users->random());
            $account = $faker->randomElement($accounts);
            for ($r = 1; $r <= 2; $r++) {
                $this->create($allItems, $account, $location, $user->id, true);
            }
            $this->create($allItems, $account, $location, $user->id);
        }
        Carbon::setTestNow();
    }
}
