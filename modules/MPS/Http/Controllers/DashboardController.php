<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Services\ChartService;

class DashboardController extends Controller
{
    public function chart(Request $request)
    {
        $this->form($request);
        $cahrt = new ChartService($request->get('month'), $request->get('year'));
        return response()->json(['week' => $cahrt->week(), 'chart' => ['year' => $cahrt->year(), 'month' => $cahrt->month()]]);
    }

    public function form(Request $request)
    {
        return $request->validate([
            'month' => 'nullable|integer|date_format:n',
            'year'  => 'nullable|integer|date_format:Y',
        ]);
    }

    public function monthChart(Request $request)
    {
        $this->form($request);
        $cahrt = new ChartService($request->get('month'), $request->get('year'));
        return response()->json(['chart' => ['month' => $cahrt->month()]]);
    }

    public function yearChart(Request $request)
    {
        $this->form($request);
        $cahrt = new ChartService($request->get('month'), $request->get('year'));
        return response()->json(['chart' => ['year' => $cahrt->year(), 'month' => $cahrt->month()]]);
    }
}
