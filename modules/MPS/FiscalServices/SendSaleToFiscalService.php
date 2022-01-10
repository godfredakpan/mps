<?php

namespace Modules\MPS\FiscalServices;

class SendSaleToFiscalService
{
    protected $original;

    protected $sale;

    public function __construct($sale, $original = null)
    {
        $this->sale     = $sale;
        $this->original = $original;
    }

    public function handle()
    {
        // Use `$this->sale` to report the sale to Fiscal Service Data Registry
        // If updating you should have `original sale` available to compare like `if ($this->original) { }`
        $isUpdating = $this->original ? true : false;
        // $isGrandTotalChanged =  $this->sale->grand_total != $this->original->grand_total;
        // $isTaxAmountChanged = $this->sale->total_tax_amount != $this->original->total_tax_amount;
        // if ($this->sale->paid) { } // only if sale is paid
        if (!$isUpdating && !$this->sale->draft && !$this->sale->reported_at) {
            // You can access order items as `$this->sale->items`
            // foreach ($this->sale->items as $item) {
            //     $item->code; // Item code
            //     $item->name; // Item name
            //     $item->quantity; // Sold quantity
            //     $item->net_price; // Nett price
            //     $item->unit_price; // Unit price (nett price + tax amount)
            //     $item->tax_amount; // tax amount
            //     $item->discount_amount; // discount amount
            //     $item->total_tax_amount; // total tax amount (tax amount * sold quantity)
            //     $item->total_discount_amount; // total discount amount (discount amount * sold quantity)
            //     $item->subtotal; // Subtotal or row total > item unit_price * sold quantity
            // }

            // Uncomment the below line to update sale as reported
            // $this->sale->disableLogging(); // disable activity log
            // $this->sale->update(['reported_at' => now()]);
        }
    }
}
