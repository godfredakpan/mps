<?php

namespace Modules\MPS\Http\Controllers;

use Modules\MPS\Models\Activity;

class UtilityController extends Controller
{
    public function logs()
    {
        return response()->json(Activity::table(Activity::$searchable));
    }

    public function showLog(Activity $activity)
    {
        $activity->load('subject');
        return $activity;
    }
}
