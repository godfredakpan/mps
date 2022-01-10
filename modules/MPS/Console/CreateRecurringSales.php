<?php

namespace Modules\MPS\Console;

use Carbon\Carbon;
use Modules\MPS\Models\Sale;
use Illuminate\Console\Command;
use Modules\MPS\Models\RecurringSale;
use Modules\MPS\Services\OrderService;

class CreateRecurringSales extends Command
{
    protected $description = 'Generate sales for recurring sales';

    protected $signature = 'recurring:sales';

    public function __construct()
    {
        parent::__construct();
    }

    protected function biennially($date)
    {
        return $date->startOfDay()->addYears(2)->lte(Carbon::now()->startOfDay());
    }

    protected function create($recurring_sale)
    {
        $v = $recurring_sale->toArray();
        // $v = unserialize(serialize($recurring_sale->toArray()));

        $sale_items = [];

        foreach ($v['items'] as $item) {
            $sale_item = [
                'name'            => $item['name'],
                'code'            => $item['code'],
                'price'           => $item['price'],
                'item_id'         => $item['item_id'],
                'unit_id'         => $item['unit_id'],
                'quantity'        => $item['quantity'],
                'discount_amount' => $item['discount_amount'],
                'discount'        => $item['discount'],
                'expiry_date'     => null, // Check item and set $item['expiry_date'],
                'batch_no'        => null, // Check item and set $item['batch_no'],
                // 'item_unit_id' => $item['item_unit_id'],
            ];
            $sale_item['taxes'] = [];
            if (!empty($item['taxes'])) {
                foreach ($item['taxes'] as $d) {
                    $sale_item['taxes'][] = $d['id'];
                }
            }
            $sale_item['allTaxes'] = [];
            if (!empty($item['item']['taxes'])) {
                foreach ($item['item']['taxes'] as $d) {
                    $sale_item['allTaxes'][]   = $d['id'];
                    $sale_item['item_taxes'][] = $d['id'];
                }
            }
            $sale_item['categories'] = [];
            if (!empty($item['item']['categories'])) {
                foreach ($item['item']['categories'] as $d) {
                    $sale_item['categories'][] = $d['id'];
                }
            }
            $sale_item['promotions'] = [];
            if (!empty($item['promotions'])) {
                foreach ($item['promotions'] as $d) {
                    $sale_item['promotions'][] = $d['id'];
                }
            }
            $sale_item['item_promotions'] = [];
            if (!empty($item['promotions'])) {
                foreach ($item['promotions'] as $d) {
                    $sale_item['item_promotions'][] = $d['id'];
                }
            }
            $sale_item['allPromotions'] = [];
            if (!empty($item['item']['promotions'])) {
                foreach ($item['item']['promotions'] as $d) {
                    $sale_item['allPromotions'][] = $d['id'];
                }
            }

            $sale_item['selected'] = ['variations' => [], 'portions' => [], 'serials' => [], 'modifiers' => []];

            if (!empty($item['modifier_options'])) {
                foreach ($item['modifier_options'] as $d) {
                    $sale_item['selected']['modifiers'][] = [
                        'id'       => $d['modifier_id'],
                        'price'    => $d['pivot']['price'],
                        'quantity' => $d['pivot']['quantity'],
                        'selected' => $d['pivot']['modifier_option_id'],
                    ];
                }
            }

            if (!empty($item['variations'])) {
                foreach ($item['variations'] as $d) {
                    $sale_item['selected']['variations'][] = [
                        'id'       => $d['pivot']['variation_id'],
                        'price'    => $d['pivot']['price'],
                        'quantity' => $d['pivot']['quantity'],
                    ];
                }
            }

            if (!empty($item['portions'])) {
                foreach ($item['portions'] as $p) {
                    $pportion = [
                        'id'       => $p['pivot']['portion_id'],
                        'price'    => $p['pivot']['price'],
                        'quantity' => $p['pivot']['quantity'],
                    ];
                    if (!empty($p['pivot']['choosables'])) {
                        foreach ($p['pivot']['choosables'] as $choosable) {
                            $pportion['choosables'][] = ['id' => $choosable->id, 'selected' => $choosable->item_id];
                            // $pportion['choosables'][] = ['id' => $choosable['id'], 'selected' => $choosable['item_id']];
                        }
                    } else {
                        $pportion['choosables'] = [];
                    }
                    $sale_item['selected']['portions'][] = $pportion;
                }
            }

            // dump($sale_item);
            $sale_items[] = $sale_item;
        }

        $v['items']             = $sale_items;
        $v['type']              = 'nett';
        $v['date']              = Carbon::now()->format('Y-m-d');
        $v['recurring_sale_id'] = $recurring_sale->id;
        unset(
            $v['id'],
            $v['repeat'],
            $v['start_date'],
            $v['create_before'],
            $v['last_created_at'],
            $v['user'],
            $v['hash'],
            $v['customer'],
            $v['reference'],
            $v['created_at'],
            $v['updated_at'],
            $v['attachments']
        );

        $sale = (new OrderService($v, new Sale))->process()->save();
        $sale->customer->payments()->create([
            'received'    => false,
            'amount'      => $sale->grand_total,
            'account_id'  => env('DEFAULT_ACCOUNT'),
            'location_id' => $recurring_sale->location_id,
            'note'        => 'This is auto generated payment request for the sale number ' . $sale->reference,
        ]);
    }

    protected function daily($date)
    {
        return $date->startOfDay()->addDay()->lte(Carbon::now()->startOfDay());
    }

    protected function monthly($date)
    {
        return $date->startOfDay()->addMonth()->lte(Carbon::now()->startOfDay());
    }

    protected function quarterly($date)
    {
        return $date->startOfDay()->addQuarter()->lte(Carbon::now()->startOfDay());
    }

    protected function semiannual($date)
    {
        return $date->startOfDay()->addMonths(6)->lte(Carbon::now()->startOfDay());
    }

    protected function weekly($date)
    {
        return $date->startOfDay()->addWeek()->lte(Carbon::now()->startOfDay());
    }

    protected function yearly($date)
    {
        return $date->startOfDay()->addYear()->lte(Carbon::now()->startOfDay());
    }

    public function handle()
    {
        $recurring_sales = RecurringSale::with(['items' => fn ($q) => $q->withRecurring()])->active()->get()->filter(function ($recurring_sale) {
            $date = $recurring_sale->last_created_at ? $recurring_sale->last_created_at : $recurring_sale->start_date;
            if ($recurring_sale->create_before) {
                $date = $recurring_sale->create_before == 1 ? $date->subDay() : $date->subDays($recurring_sale->create_before);
            }
            return $this->{$recurring_sale->repeat}($date);
        });

        $recurring_sales->each(function ($recurring_sale) {
            $this->create($recurring_sale);
            $recurring_sale->disableLogging();
            $recurring_sale->refresh()->update(['last_created_at' => date('Y-m-d')]);
        });

        $sales_text = __choice('sale', [], $recurring_sales->count());
        activity()->withProperties($recurring_sales)
            ->log($recurring_sales->count() . ' ' . $sales_text . ' have been created with recurring:sales command.');
        $this->info(sprintf('%d ' . $sales_text . ' have been created with recurring:sales command.', $recurring_sales->count()));
    }
}
