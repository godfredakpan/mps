<?php

namespace Modules\MPS\Models\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

trait TableTrait
{
    public static function scopeTable($data, $fields, $special_field = [])
    {
        $request = app()->make('request');
        extract($request->only('query', 'limit', 'page', 'orderBy', 'byColumn'));

        if (isset($query) && $query) {
            if ($byColumn == 1) {
                $query = json_decode($query);
                static::filterByColumn($data, $query);
            } else {
                static::filter($data, $query, $fields);
            }
        }

        $count = $data->count();
        $data->limit($limit)->skip($limit * ($page - 1));
        if (!empty($orderColumns)) {
            $orderColumns = explode(',', $orderBy);
            if (count($orderColumns) > 1) {
                foreach ($orderColumns as $oce) {
                    $order     = explode(' ', $oce);
                    $direction = isset($order[1]) && $order[1] == 'desc' ? 'desc' : 'asc';
                    $data->orderBy($order[0], $direction);
                }
            } else {
                $order     = explode(' ', $orderBy);
                $direction = isset($order[1]) && $order[1] == 'desc' ? 'desc' : 'asc';
                $data->orderBy($order[0], $direction);
            }
        }

        $results = $data->latest()->get();

        if ($special_field) {
            foreach ($special_field as $sf) {
                foreach ($results as &$value) {
                    $value->{$sf} = $value->{$sf};
                }
            }
        }

        return ['data' => $results->toArray(), 'count' => $count];
    }

    protected static function filter($data, $query, $fields)
    {
        $special = explode('=', $query);
        if (isset($special[1])) {
            $data->where(function ($q) use ($special, $fields) {
                if (in_array($special[0], $fields)) {
                    $q->where($special[0], $special[1]);
                }
            });
            return $data;
        }
        $special = explode(':', $query);
        if (isset($special[1])) {
            $data->where(function ($q) use ($special, $fields) {
                if (in_array($special[0], $fields)) {
                    $q->where($special[0], 'like', "%{$special[1]}%");
                }
            });
            return $data;
        }
        $data->where(function ($q) use ($query, $fields) {
            foreach ($fields as $index => $field) {
                $relation = explode('.', $field);
                if (isset($relation[0]) && isset($relation[1]) && isset($relation[2]) && $relation[2] == 'morph') {
                    $method = $index ? 'orWhereHasMorph' : 'whereHasMorph';
                    $q->{$method}($relation[0], static::morphModels($relation[0]), function ($qu) use ($relation, $query) {
                        $qu->where($relation[1], 'like', "%{$query}%");
                    });
                } elseif (isset($relation[0]) && isset($relation[1])) {
                    $method = $index ? 'orWhereHas' : 'whereHas';
                    $q->{$method}($relation[0], function ($qu) use ($relation, $query) {
                        $qu->where($relation[1], 'like', "%{$query}%");
                    });
                } else {
                    $method = $index ? 'orWhere' : 'where';
                    if (in_array($field, ['date', 'created_at', 'updated_at', 'deleted_at'])) {
                        if (!(($timestamp = strtotime($query)) === false)) {
                            $date = date('Y-m-d', $timestamp);
                            $months = ['month', 'jan', 'feb', 'mar', 'apr', 'ma', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
                            if (Str::contains($query, 'week')) {
                                $lastDay = Carbon::parse($date)->endOfWeek()->format('Y-m-d');
                                $firstDay = Carbon::parse($date)->startOfWeek()->format('Y-m-d');
                                $q->{$method . 'Between'}($field, [$firstDay, $lastDay]);
                            } elseif (Str::contains($query, 'days')) {
                                $lastDay = now()->format('Y-m-d');
                                $firstDay = Carbon::parse($date)->format('Y-m-d');
                                Log::info('from ' . $firstDay . ' to ' . $lastDay);
                                $q->{$method . 'Between'}($field, [$firstDay, $lastDay]);
                            } elseif (Str::contains(mb_strtolower($query), $months)) {
                                $lastDay = Carbon::parse($date)->endOfMonth()->format('Y-m-d');
                                $firstDay = Carbon::parse($date)->startOfMonth()->format('Y-m-d');
                                $q->{$method . 'Between'}($field, [$firstDay, $lastDay]);
                            } else {
                                $q->{$method . 'Date'}($field, $date);
                            }
                        }
                    } else {
                        $q->{$method}($field, 'LIKE', "%{$query}%");
                    }
                }
            }
        });
        return $data;
    }

    protected static function filterByColumn($data, $fields)
    {
        $optionals  = ['taxes'];
        $filterable = [
            'id', 'code', 'name', 'reference', 'categories', 'customer', 'vendor', 'phone', 'user',
            'taxes', 'draft', 'date', 'gateway', 'price', 'cost', 'range', 'email', 'company', 'title', 'amount',
            'date_range', 'created_at', 'account', 'received', 'description', 'log_name', 'subject_id', 'subject_type',
        ];
        foreach ($fields as $field => $value) {
            if ($field != 'draft' && $field != 'received' && (empty($value) || !in_array($field, $filterable))) {
                continue;
            }

            if (!empty($value) && $field == 'created_at') {
                if (!empty($value)) {
                    $end   = Carbon::createFromFormat('Y-m-d', $value)->endOfDay();
                    $start = Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
                    $data->whereBetween('created_at', [$start, $end]);
                }
            } elseif (!empty($value) && $field == 'date_range' || $field == 'range') {
                if ($field == 'date_range' && !empty($value)) {
                    $range = explode(' to ', $value);
                    $end   = Carbon::createFromFormat('Y-m-d', $range[1])->endOfDay();
                    $start = Carbon::createFromFormat('Y-m-d', $range[0])->startOfDay();
                    $data->whereBetween($fields->range == 'date' ? 'date' : 'created_at', [$start, $end]);
                }
            } elseif (!empty($value) && is_object($value)) {
                $method = !in_array($field, $optionals) && !$value->name && isset($value->optional) ? 'orWhereHas' : 'whereHas';
                $data->{$method}($field, function ($query) use ($value) {
                    foreach ($value as $f => $q) {
                        $query->where($f, 'like', "%{$q}%");
                    }
                });
            } else {
                $data->where($field, 'like', "%{$value}%");
            }
        }
        return $data;
    }

    protected static function morphModels($subject)
    {
        if ($subject == 'payable') {
            return ['Modules\MPS\Models\Customer', 'Modules\MPS\Models\Supplier'];
        }
        return '*';
    }
}
