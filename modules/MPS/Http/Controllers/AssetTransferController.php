<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\AssetTransfer;

class AssetTransferController extends Controller
{
    public function create()
    {
        return extra_attributes('asset_transfer');
    }

    public function destroy(AssetTransfer $asset)
    {
        $asset->delete();
        return response(['success' => true]);
    }

    public function index()
    {
        return response()->json(AssetTransfer::with('user:id,name')->table(AssetTransfer::$searchable));
    }

    public function show(AssetTransfer $asset)
    {
        return $asset;
    }

    public function store(Request $request)
    {
        $asset = AssetTransfer::create($this->form($request));
        return response(['success' => true, 'data' => $asset]);
    }

    public function update(Request $request, AssetTransfer $asset)
    {
        $asset->update($this->form($request));
        return response(['success' => true, 'data' => $asset->refresh()]);
    }

    private function form($request)
    {
        return $request->validate([
            'details' => 'nullable',
            'amount'  => 'required|numeric',
            'from'    => 'required|different:to|exists:accounts,id',
            'to'      => 'required|different:from|exists:accounts,id',
        ]);
    }
}
