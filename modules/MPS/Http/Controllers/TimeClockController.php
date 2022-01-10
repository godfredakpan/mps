<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\TimeClock;

class TimeClockController extends Controller
{
    public function index(Request $request)
    {
        $logs = TimeClock::query()->with(['user:id,name', 'location:id,name']);
        if ($request->user_id) {
            $logs->where('user_id', $request->input('user_id'));
        }
        return response()->json($logs->table(TimeClock::$searchable));
    }
}
