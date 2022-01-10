<?php

namespace Modules\MPS\Models;

use Money\Money;
use Carbon\Carbon;
use Money\Currency;

class Journal extends Base
{
    protected $guareded = [];

    public function credit($value, $type = null, $memo = null, $post_date = null)
    {
        $value = is_a($value, Money::class) ? $value : new Money($value, new Currency($this->currency));
        return $this->post($value, null, $type, $memo, $post_date);
    }

    public function creditDollars($value, $type = null, $memo = null, $post_date = null)
    {
        $value = $value * 100;
        return $this->credit($value, $type, $memo, $post_date);
    }

    public function debit($value, $type = null, $memo = null, $post_date = null)
    {
        $value = is_a($value, Money::class) ? $value : new Money($value, new Currency($this->currency));
        return $this->post(null, $value, $type, $memo, $post_date);
    }

    public function debitDollars($value, $type = null, $memo = null, $post_date = null)
    {
        $value = $value * 100;
        return $this->debit($value, $type, $memo, $post_date);
    }

    public function getBalance()
    {
        $balance = $this->transactions()->sum('credit') - $this->transactions()->sum('debit');
        return new Money($balance, new Currency($this->currency));
    }

    public function getBalanceAttribute($value)
    {
        return new Money($value, new Currency($this->currency));
    }

    public function getBalanceInDollars()
    {
        return $this->getBalance()->getAmount() / 100;
    }

    public function getBalanceOn(Carbon $date)
    {
        return $this->getCreditBalanceOn($date)->subtract($this->getDebitBalanceOn($date));
    }

    public function getCreditBalanceOn(Carbon $date)
    {
        $balance = $this->transactions()->where('post_date', '<=', $date)->sum('credit') ?: 0;
        return new Money($balance, new Currency($this->currency));
    }

    public function getCurrentBalance()
    {
        return $this->getBalanceOn(Carbon::now());
    }

    public function getCurrentBalanceInDollars()
    {
        return $this->getCurrentBalance()->getAmount() / 100;
    }

    public function getDebitBalanceOn(Carbon $date)
    {
        $balance = $this->transactions()->where('post_date', '<=', $date)->sum('debit') ?: 0;
        return new Money($balance, new Currency($this->currency));
    }

    public function getDollarsCreditedOn(Carbon $date)
    {
        return $this->transactions()
            ->whereBetween('post_date', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ])
            ->sum('credit') / 100;
    }

    public function getDollarsCreditedToday()
    {
        $today = Carbon::now();
        return $this->getDollarsCreditedOn($today);
    }

    public function getDollarsDebitedOn(Carbon $date)
    {
        return $this->transactions()
            ->whereBetween('post_date', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ])
            ->sum('debit') / 100;
    }

    public function getDollarsDebitedToday()
    {
        $today = Carbon::now();
        return $this->getDollarsDebitedOn($today);
    }

    public function morphed()
    {
        return $this->morphTo();
    }

    public function resetCurrentBalances()
    {
        $this->balance = $this->getBalance();
        $this->save();
    }

    public function setBalanceAttribute($value)
    {
        $value                       = is_a($value, Money::class) ? $value : new Money($value, new Currency($this->currency));
        $this->attributes['balance'] = $value ? (int) $value->getAmount() : null;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    public function transactions()
    {
        return $this->hasMany(JournalTransaction::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::created(function (Journal $journal) {
            $journal->resetCurrentBalances();
        });
    }

    private function post(Money $credit = null, Money $debit = null, $type = null, $memo = null, $post_date = null)
    {
        $transaction            = new JournalTransaction();
        $transaction->credit    = $credit ? $credit->getAmount() : null;
        $transaction->debit     = $debit ? $debit->getAmount() : null;
        $currency_code          = $credit ? $credit->getCurrency()->getCode() : $debit->getCurrency()->getCode();
        $transaction->type      = $type;
        $transaction->memo      = $memo;
        $transaction->currency  = $currency_code;
        $transaction->post_date = $post_date ?: Carbon::now();
        $this->transactions()->save($transaction);
        return $transaction;
    }
}
