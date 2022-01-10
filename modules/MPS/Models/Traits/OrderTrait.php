<?php

namespace Modules\MPS\Models\Traits;

trait OrderTrait
{
    public static function scopeActive($query)
    {
        return $query->whereNull('draft')->orWhere('draft', 0);
    }

    public static function scopeDraft($query)
    {
        return $query->where('draft', 1);
    }

    public static function scopeOfCustomer($query, $customer)
    {
        return $query->where('customer_id', $customer);
    }

    public static function scopeOfSupplier($query, $supplier)
    {
        return $query->where('supplier_id', $supplier);
    }

    public static function scopePaid($query)
    {
        return $query->where('paid', 1);
    }

    public static function scopeUnpaid($query)
    {
        return $query->whereNull('paid')->orWhere('paid', 0);
    }

    public function withAll()
    {
        return $this->loadMissing([
            'items' => fn ($q) => $q->withAll(),
        ]);
    }

    public function withSelected()
    {
        return $this->loadMissing([
            'items' => function ($query) {
                $query->withSelected();
            },
        ]);
    }
}
