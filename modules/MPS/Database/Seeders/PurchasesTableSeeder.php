<?php

namespace Modules\MPS\Database\Seeders;

use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Support\Arr;
use Modules\MPS\Models\Item;
use Modules\MPS\Models\User;
use Illuminate\Database\Seeder;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Supplier;
use Modules\MPS\Actions\TaxAction;
use Illuminate\Support\Facades\Auth;
use Modules\MPS\Actions\DiscountAction;

class PurchasesTableSeeder extends Seeder
{
    protected function create($allItems, $account, $location, $uId, $fc = false)
    {
        session(['location_id' => $location->id]);
        $supplier = $fc ? Supplier::first() : Supplier::inRandomOrder()->first();
        factory('Modules\MPS\Models\Purchase', mt_rand(1, 5))->make(['user_id' => $uId, 'supplier_id' => $supplier->id])->each(function ($purchase) use ($allItems, $account, $location, $supplier) {
            Carbon::setTestNow();
            Carbon::setTestNow(now()->subDays(mt_rand(2, 400)));
            $purchase->date = $purchase->created_at = $purchase->updated_at = now();
            $items = $allItems->random(mt_rand(1, 5));
            $total = $total_items = 0;
            $purchaseItems = [];
            foreach ($items as $item) {
                $qty = mt_rand(1, 3);
                $applicable_taxes = $item->taxes->filter(function ($tax) use ($location, $supplier) {
                    if ($tax->state) {
                        $check = $supplier->state == $location->state;
                        return $tax->same ? $check : !$check;
                    }
                    return true;
                })->pluck('id')->all();
                $taxes = (new TaxAction)->calculate($applicable_taxes, $item->cost, $qty);
                $purchaseItem = factory('Modules\MPS\Models\PurchaseItem')->make([
                    'item_id'          => $item->id,
                    'unit_id'          => $item->unit_id,
                    'quantity'         => $qty,
                    'balance'          => $qty,
                    'cost'             => $item->cost,
                    'net_cost'         => $item->cost,
                    'tax_amount'       => $tax = $taxes && $taxes->isNotEmpty() ? $taxes->sum('amount') : 0,
                    'total_tax_amount' => formatDecimal($tax * $qty, 2),
                    'unit_cost'        => $unit_cost = $item->cost + $tax,
                    'base_net_cost'    => $item->cost,
                    'base_unit_cost'   => $unit_cost,
                    'subtotal'         => ($unit_cost * $qty),
                    'taxes'            => $taxes,
                    'item_taxes'       => Arr::pluck($item->taxes, 'id'),
                ]);
                $total += $purchaseItem->subtotal;
                $purchaseItems[] = $purchaseItem;
            }

            $v['total'] = $total;
            $v['item_tax_amount'] = formatDecimal(collect($purchaseItems)->sum('total_tax_amount'), 2);
            $discount_amount = (new DiscountAction)->calculate(0, $total);
            $order_taxes = (new TaxAction)->calculate(0, ($total - $discount_amount));
            $order_tax_amount = $order_taxes ? formatDecimal($order_taxes->sum('amount'), 2) : 0;
            $v['total_tax_amount'] = formatDecimal($v['item_tax_amount'] + $order_tax_amount, 2);
            $v['grand_total'] = formatDecimal($total - $discount_amount + $order_tax_amount, 2);
            $rT = (new TaxAction)->recoverableTaxAmount($purchase->taxes);
            $v['recoverable_tax_amount'] = $rT['recoverable_tax_amount'];
            $v['recoverable_tax_calculated_on'] = $rT['recoverable_tax_calculated_on'];
            foreach ($purchaseItems as $item) {
                $rT = (new TaxAction)->recoverableTaxAmount($item['taxes']);
                $v['recoverable_tax_amount'] += $rT['recoverable_tax_amount'];
                $v['recoverable_tax_calculated_on'] += $rT['recoverable_tax_calculated_on'];
            }

            $purchase->fill($v)->save();
            foreach ($purchaseItems as $item) {
                $taxes = $item->taxes;
                unset($item->taxes);
                $purchase->items()->save($item);
                if ($taxes && $taxes->isNotEmpty()) {
                    $item->taxes()->sync($taxes->toArray());
                }
            }
            if (!$purchase->draft) {
                if (mt_rand(0, 1)) {
                    $purchase->supplier->payments()->create(
                        factory('Modules\MPS\Models\Payment')->make([
                            'received'    => 1,
                            'gateway'     => 'cash',
                            'purchase_id' => $purchase->id,
                            'account_id'  => $account->id,
                            'user_id'     => $purchase->user_id,
                            'amount'      => $v['grand_total'],
                        ])->toArray()
                    );
                }
            }
        });
    }

    public function run()
    {
        $faker = Factory::create();

        echo "Creating Purchases\n";
        $users     = User::all();
        $accounts  = Account::all();
        $locations = Location::all();
        $allItems  = Item::where('type', 'standard')->orWhere('type', 'service')->inRandomOrder()->limit(30)->get();
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
