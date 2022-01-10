<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Item;
use App\Http\Controllers\Controller;

class AddItemToOrderController extends Controller
{
    public function purchase(Request $request, Item $item)
    {
        return $item->purchaseRelation();
    }

    public function sale(Request $request, Item $item)
    {
        return $item->allRelation();
    }

    public function transfer(Request $request, Item $item)
    {
        return $item->transferRelation($request->input('location'));
    }
}
