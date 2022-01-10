<?php

namespace Modules\MPS\Http\Controllers\Reports;

use Illuminate\Http\Request;
use Modules\MPS\Models\Item;
use Modules\MPS\Models\Sale;
use Modules\MPS\Models\Brand;
use Modules\MPS\Models\Income;
use Modules\MPS\Models\Expense;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Purchase;
use Modules\MPS\Models\Supplier;
use Modules\MPS\Models\Quotation;
use Modules\MPS\Models\ReturnOrder;
use Modules\MPS\Models\StockTransfer;
use Modules\MPS\Models\StockAdjustment;
use Modules\MPS\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();
        // $expenses->whereBetween('date', [$start_date, $end_date]);
        $totals = Item::selectRaw('COUNT(*) as items')
            ->addSelect(['sales' => Sale::selectRaw('COUNT(*) as sales')])
            ->addSelect(['purchases' => Purchase::selectRaw('COUNT(*) as purchases')])
            ->addSelect(['incomes' => Income::selectRaw('COUNT(*) as incomes')])
            ->addSelect(['expenses' => Expense::selectRaw('COUNT(*) as expenses')])
            ->addSelect(['quotations' => Quotation::selectRaw('COUNT(*) as quotations')])
            ->addSelect(['return_orders' => ReturnOrder::selectRaw('COUNT(*) as return_orders')])
            ->addSelect(['customers' => Customer::selectRaw('COUNT(*) as customers')])
            ->addSelect(['suppliers' => Supplier::selectRaw('COUNT(*) as suppliers')])
            ->addSelect(['categories' => Category::selectRaw('COUNT(*) as categories')])
            ->addSelect(['brands' => Brand::selectRaw('COUNT(*) as brands')])
            ->addSelect(['stock_adjustments' => StockAdjustment::selectRaw('COUNT(*) as stock_adjustments')])
            ->addSelect(['stock_transfers' => StockTransfer::selectRaw('COUNT(*) as stock_transfers')])
            ->addSelect(['standard_items' => Item::selectRaw('COUNT(*) as items')->where('type', 'standard')])
            ->addSelect(['services' => Item::selectRaw('COUNT(*) as services')->where('type', 'service')])
            ->addSelect(['recipes' => Item::selectRaw('COUNT(*) as recipes')->where('type', 'recipe')])
            ->addSelect(['combos' => Item::selectRaw('COUNT(*) as combos')->where('type', 'combo')])
            ->first();
        return response()->json(['totals' => $totals, 'end_date' => $end_date, 'start_date' => $start_date]);
    }
}
