<?php

namespace Modules\MPS\Http\Controllers;

use Modules\MPS\Models\StockTransfer;
use Modules\MPS\Events\StockTransferEvent;
use Modules\MPS\Http\Requests\StockTransferRequest;

class StockTransferController extends Controller
{
    public function create()
    {
        return extra_attributes('stock_transfer');
    }

    public function destroy(StockTransfer $stock)
    {
        $stock->delete();
        return response(['success' => true]);
    }

    public function index()
    {
        return response()->json(StockTransfer::with('user:id,name')->table(StockTransfer::$searchable));
    }

    public function show(StockTransfer $stock)
    {
        return $stock->withItems();
    }

    public function store(StockTransferRequest $request)
    {
        $form  = $request->validated();
        $stock = (new StockTransfer())->saveTransfer($form);
        $stock->saveAttachments($request->file('attachments'));
        event(new StockTransferEvent($stock, 'created'));
        return response(['success' => true, 'data' => $stock]);
    }

    public function update(StockTransferRequest $request, StockTransfer $stock)
    {
        $form = $request->validated();
        $stock->load(['items', 'items.stock']);
        $stock_transfer = $stock->saveTransfer($form, true);
        $stock_transfer->saveAttachments($request->file('attachments'));
        event(new StockTransferEvent($stock_transfer, 'updated', $stock));
        return response(['success' => true, 'data' => $stock_transfer->refresh()]);
    }
}
