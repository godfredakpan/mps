<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait IntervalTrait
{
    public static function scopeDaily($query, $interval = '1 DAY', $field = 'created_at')
    {
        return $query->where($field, '<=', DB::raw("(NOW() + INTERVAL {$interval})"));
    }

    public static function scopeMonthly($query, $year = null, $month = null, $field = 'created_at')
    {
        return $query->whereMonth($field, ($month ?: date('m')))->whereYear($field, ($year ?: date('Y')));
    }

    public static function scopeYearly($query, $year = null, $field = 'created_at')
    {
        return $query->whereYear($field, ($year ?: date('Y')));
    }
}
