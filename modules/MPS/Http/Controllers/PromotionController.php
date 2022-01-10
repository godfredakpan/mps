<?php

namespace Modules\MPS\Http\Controllers;

use Modules\MPS\Models\Promotion;
use Modules\MPS\Http\Requests\PromotionRequest;

class PromotionController extends Controller
{
    public function destroy(Promotion $promo)
    {
        $promo->delete();
        return response(['success' => true]);
    }

    public function index()
    {
        return response()->json(Promotion::table(Promotion::$searchable));
    }

    public function show(Promotion $promo)
    {
        return $promo->load('categories:id,code,name', 'items:id,code,name');
    }

    public function store(PromotionRequest $request)
    {
        $data  = $request->validated();
        $promo = Promotion::create($data);
        $promo->categories()->sync($data['categories'] ?? []);
        $promo->items()->sync($data['items'] ?? $data['item_id_to_buy'] ?? []);
        return response(['success' => true, 'data' => $promo]);
    }

    public function update(PromotionRequest $request, Promotion $promo)
    {
        $data    = $request->validated();
        $updated = $promo->update($data);
        $promo->categories()->sync($data['categories'] ?? []);
        $promo->items()->sync($data['items'] ?? $data['item_id_to_buy'] ?? []);
        return response(['success' => $updated, 'data' => $promo->refresh()]);
    }
}
