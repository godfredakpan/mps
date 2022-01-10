<?php

namespace Modules\MPS\Models;

use Ulid\Ulid;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Modules\MPS\Models\Traits\TableTrait;
use Modules\MPS\Models\Traits\ActivityTrait;

class Base extends Model
{
    use ActivityTrait;
    use TableTrait;

    public $hasHash = false;

    public $hasLocation = false;

    public $hasNumber = false;

    public $hasReference = false;

    public $hasSKU = false;

    public $hasUser = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static $logFillable = true;

    protected static $logOnlyDirty = true;

    // protected static $recordEvents = ['created', 'updated', 'deleting'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
            if ($model->hasUser && !$model->user_id) {
                $model->user_id = session()->has('impersonate') ? session()->get('impersonate') : auth()->id();
            }
            if ($model->hasHash && !$model->hash) {
                $model->hash = sha1($model->id . Str::random());
            }
            if ($model->hasNumber && !$model->number) {
                $number = DB::table($model->getTable())->max('number');
                $model->number = $number ? ((int) $number) + 1 : 1;
            }
            if ($model->hasSKU && !$model->sku) {
                $model->sku = (string) Ulid::generate(true);
            }
            if ($model->hasLocation && !$model->location_id) {
                $model->location_id = session('location_id');
            }
            if ($model->hasReference && !$model->reference) {
                $model->reference = generate_reference($model);
            }
        });
    }
}
