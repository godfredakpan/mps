<?php

namespace Modules\MPS\Models\Traits;

use App\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

trait ActivityTrait
{
    use LogsActivity;

    protected static $logFillable = true;

    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent($event)
    {
        return static::getLogNameToUse() . " has been {$event}";
    }

    public function tapActivity(Activity $activity)
    {
        $user = session()->has('impersonate') ? user(session()->get('impersonate')) : user(auth()->id());
        $activity->causer()->associate($user);
    }

    // protected static $recordEvents = ['created', 'updated', 'deleting'];

    protected static function getLogNameToUse()
    {
        return (new \ReflectionClass(static::class))->getShortName();
    }
}
