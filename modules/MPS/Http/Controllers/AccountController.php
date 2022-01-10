<?php

namespace Modules\MPS\Http\Controllers;

use Modules\MPS\Models\Account;
use Modules\MPS\Models\JournalTransaction;
use Modules\MPS\Http\Requests\AccountRequest;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super']);
    }

    public function destroy(Account $account)
    {
        if (demo()) {
            return response(['message' => 'This feature is disabled on demo.'], 422);
        }

        return response([
            'success' => $account->del(),
            'message' => __('record_deleted'),
            'error'   => __choice('delete_error', ['relations' => trans_choice('payment', 2) . ', ' . trans_choice('expense', 2) . ', ' . trans_choice('income', 2) . ' ']),
        ]);
    }

    public function index()
    {
        return response()->json(Account::with('journal')->table(Account::$searchable));
    }

    public function show(Account $account)
    {
        return $account;
    }

    public function store(AccountRequest $request)
    {
        $account = Account::create($request->validated());
        return response(['success' => true, 'data' => $account]);
    }

    public function transactions(Account $account)
    {
        return response()->json(JournalTransaction::whereHas('journal', function ($query) use ($account) {
            $query->where('morphed_id', $account->id);
        })->with('journal.morphed')->vueTable(JournalTransaction::$searchable));
    }

    public function update(AccountRequest $request, Account $account)
    {
        $account->update($request->validated());
        return response(['success' => true, 'data' => $account->refresh()]);
    }
}
