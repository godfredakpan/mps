<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Event;

class EventController extends Controller
{
    public function destroy(Event $event)
    {
        return response(['success' => $event->del()]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Event::whereIn('id', $request->ids)->get() as $event) {
            $event->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index(Request $request)
    {
        $month = $request->month ?? date('Y-m');
        return response()->json(Event::where('start_date', 'like', "{$month}%")->orderBy('start_date')->orderBy('start_time')->get()->groupBy('start_date'));
    }

    public function show(Event $event)
    {
        return $event;
    }

    public function store(Request $request)
    {
        $event = Event::create($this->form($request));
        return response(['success' => true]);
    }

    public function update(Request $request, Event $event)
    {
        $updated = $event->update($this->form($request));
        return response(['success' => $updated]);
    }

    private function form(Request $request)
    {
        return $request->validate([
            'end_time'   => 'nullable',
            'end_time'   => 'nullable',
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date',
            'title'      => 'required|string|max:90',
            'details'    => 'nullable|string|max:190',
        ]);
    }
}
