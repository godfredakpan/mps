<?php

namespace Modules\MPS\Models;

class Tax extends Base
{
    public static $searchable = ['id', 'code', 'name', 'rate', 'details', 'number', 'compound', 'recoverable', 'state', 'same'];

    protected $casts = ['state' => 'boolean', 'same' => 'boolean', 'compound' => 'boolean', 'recoverable' => 'boolean'];

    protected $fillable = ['name', 'code', 'rate', 'details', 'number', 'compound', 'recoverable', 'state', 'same'];

    protected $hidden = ['pivot'];

    public function del()
    {
        // TODO
        // if ($this->products()->exists() || $this->invoices()->exists() || $this->purchases()->exists()) {
        //     return false;
        // }

        return $this->delete();
    }

    public function products()
    {
        return $this->morphedByMany(Product::class, 'taxable');
    }

    public function purchaseItems()
    {
        return $this->morphedByMany(PurchaseItem::class, 'taxable');
    }

    public function purchases()
    {
        return $this->morphedByMany(Purchase::class, 'taxable');
    }

    public function saleItems()
    {
        return $this->morphedByMany(SaleItem::class, 'taxable');
    }

    public function sales()
    {
        return $this->morphedByMany(Sale::class, 'taxable');
    }

    public static function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('code', 'LIKE', "%$search%")
                    ->orWhere('name', 'LIKE', "%$search%");
            });
        }
        return $query;
    }
}
