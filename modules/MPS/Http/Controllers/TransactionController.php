<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Models\JournalTransaction;

class TransactionController extends Controller
{
    public function account(Request $request, Account $account)
    {
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $transactions = $this->prepareAccountRequest($request, $account);
        $balances     = [
            'close_balance' => ($transactions->where('post_date', '<=', $end_date)->sum('credit') ?: 0) - ($transactions->where('post_date', '<=', $end_date)->sum('debit') ?: 0),
            'start_balance' => ($transactions->where('post_date', '<=', $start_date)->sum('credit') ?: 0) - ($transactions->where('post_date', '<=', $start_date)->sum('debit') ?: 0),
        ];

        return response()->json(['transactions' => $balances, 'end_date' => $end_date, 'start_date' => $start_date]);
    }

    public function accountTable(Request $request, Account $account)
    {
        $transactions = $this->prepareAccountRequest($request, $account);
        return response()->json($transactions->with('journal.morphed')->table(JournalTransaction::$searchable));
    }

    public function customer(Request $request, Customer $customer)
    {
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $transactions = $this->prepareCustomerRequest($request, $customer);
        $balances     = [
            'close_balance' => ($transactions->where('post_date', '<=', $end_date)->sum('credit') ?: 0) - ($transactions->where('post_date', '<=', $end_date)->sum('debit') ?: 0),
            'start_balance' => ($transactions->where('post_date', '<=', $start_date)->sum('credit') ?: 0) - ($transactions->where('post_date', '<=', $start_date)->sum('debit') ?: 0),
        ];

        return response()->json(['transactions' => $balances, 'end_date' => $end_date, 'start_date' => $start_date]);
    }

    public function customerTable(Request $request, Customer $customer)
    {
        $transactions = $this->prepareCustomerRequest($request, $customer);
        return response()->json($transactions->with('journal.morphed')->table(JournalTransaction::$searchable));
    }

    public function index()
    {
        return response()->json(JournalTransaction::with('journal.morphed')->table(JournalTransaction::$searchable));
    }

    public function supplier(Request $request, Supplier $supplier)
    {
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $transactions = $this->prepareSupplierRequest($request, $supplier);
        $balances     = [
            'close_balance' => ($transactions->where('post_date', '<=', $end_date)->sum('credit') ?: 0) - ($transactions->where('post_date', '<=', $end_date)->sum('debit') ?: 0),
            'start_balance' => ($transactions->where('post_date', '<=', $start_date)->sum('credit') ?: 0) - ($transactions->where('post_date', '<=', $start_date)->sum('debit') ?: 0),
        ];

        return response()->json(['transactions' => $balances, 'end_date' => $end_date, 'start_date' => $start_date]);
    }

    public function supplierTable(Request $request, Supplier $supplier)
    {
        $transactions = $this->prepareSupplierRequest($request, $supplier);
        return response()->json($transactions->with('journal.morphed')->table(JournalTransaction::$searchable));
    }

    private function prepareAccountRequest(Request $request, Account $account)
    {
        // $transactions = $account->journal->transactions()->query();
        $transactions = JournalTransaction::query();
        $end_date     = $request->end_date ?: now()->endOfDay();
        $start_date   = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $transactions->whereBetween('created_at', [$start_date, $end_date]);
        $transactions->whereHas('journal', fn ($q) => $q->where('morphed_id', $account->id)->where('morphed_type', 'Modules\MPS\Models\Account'));

        if ($request->customer_id) {
            $transactions->whereHasMorph(
                'subject',
                ['Modules\MPS\Models\Payment'],
                fn (Builder $q) => $q->where('payable_id', $request->input('customer_id'))->where('payable_type', 'Modules\MPS\Models\Customer')
            );
        }
        if ($request->supplier_id) {
            $transactions->whereHasMorph(
                'subject',
                ['Modules\MPS\Models\Payment'],
                fn (Builder $q) => $q->where('payable_id', $request->input('supplier_id'))->where('payable_type', 'Modules\MPS\Models\Supplier')
            );
        }
        if ($request->user_id) {
            $transactions->whereHasMorph(
                'subject',
                ['Modules\MPS\Models\Payment', 'Modules\MPS\Models\Expense', 'Modules\MPS\Models\Income'],
                fn (Builder $q) => $q->where('user_id', $request->input('user_id'))
            );
        }

        return $transactions;
    }

    private function prepareCustomerRequest(Request $request, Customer $customer)
    {
        // $transactions = $account->journal->transactions()->query();
        $transactions = JournalTransaction::query();
        $end_date     = $request->end_date ?: now()->endOfDay();
        $start_date   = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $transactions->whereBetween('created_at', [$start_date, $end_date]);
        $transactions->whereHas('journal', fn ($q) => $q->where('morphed_id', $customer->id)->where('morphed_type', 'Modules\MPS\Models\Customer'));

        if ($request->user_id) {
            $transactions->whereHasMorph(
                'subject',
                ['Modules\MPS\Models\Payment'],
                fn (Builder $q) => $q->where('user_id', $request->input('user_id'))
            );
        }

        return $transactions;
    }

    private function prepareSupplierRequest(Request $request, Supplier $supplier)
    {
        // $transactions = $account->journal->transactions()->query();
        $transactions = JournalTransaction::query();
        $end_date     = $request->end_date ?: now()->endOfDay();
        $start_date   = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $transactions->whereBetween('created_at', [$start_date, $end_date]);
        $transactions->whereHas('journal', fn ($q) => $q->where('morphed_id', $supplier->id)->where('morphed_type', 'Modules\MPS\Models\Supplier'));

        if ($request->user_id) {
            $transactions->whereHasMorph(
                'subject',
                ['Modules\MPS\Models\Payment'],
                fn (Builder $q) => $q->where('user_id', $request->input('user_id'))
            );
        }

        return $transactions;
    }
}
