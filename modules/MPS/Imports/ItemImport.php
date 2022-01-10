<?php

namespace Modules\MPS\Imports;

use Modules\MPS\Models\Item;
use Maatwebsite\Excel\Concerns\ToModel;

class ItemImport implements ToModel
{
    public function model(array $row)
    {
        return new Item([
            //
        ]);
    }
}
