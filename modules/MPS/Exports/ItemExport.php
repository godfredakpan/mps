<?php

namespace Modules\MPS\Exports;

use Modules\MPS\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;

class ItemExport implements FromCollection
{
    public function collection()
    {
        return [];
        // return Item::all();
    }
}
