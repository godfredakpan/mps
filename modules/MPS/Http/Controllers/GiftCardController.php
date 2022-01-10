<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\GiftCard;
use Modules\MPS\Models\GiftCardLog;
use Modules\MPS\Http\Requests\GiftCardRequest;

class GiftCardController extends Controller
{
    public function destroy(GiftCard $gift_card)
    {
        $gift_card->del();
        return response(['success' => true]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (GiftCard::whereIn('id', $request->ids)->get() as $gift_card) {
            $gift_card->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index()
    {
        return response()->json(GiftCard::with(['customer:id,name'])->table(GiftCard::$searchable));
    }

    public function logs(GiftCard $gift_card)
    {
        return response()->json($gift_card->logs()->table(GiftCardLog::$searchable));
    }

    public function search(Request $request)
    {
        return response()->json(GiftCard::search($request->q)->latest()->get());
    }

    public function show(GiftCard $gift_card)
    {
        return $gift_card->load('customer:id,name');
    }

    public function store(GiftCardRequest $request)
    {
        $form            = $request->validated();
        $form['balance'] = $form['amount'];
        $gift_card       = GiftCard::create($form);
        return response(['success' => !!$gift_card, 'data' => $gift_card]);
    }

    public function update(GiftCardRequest $request, GiftCard $gift_card)
    {
        $form            = $request->validated();
        $form['balance'] = $form['amount'] + $gift_card->balance - $gift_card->amount;
        $updated         = $gift_card->update($form);
        return response(['success' => $updated, 'data' => $gift_card->load('customer:id,name')]);
    }
}
