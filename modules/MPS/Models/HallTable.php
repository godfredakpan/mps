<?php

namespace Modules\MPS\Models;

class HallTable extends Base
{
    public static $searchable = ['id', 'code', 'title', 'details', 'hall_id', 'extra_attributes'];

    protected $casts = ['extra_attributes' => 'array'];

    protected $fillable = ['code', 'title', 'details', 'hall_id', 'extra_attributes'];

    public function del()
    {
        return $this->delete();
    }

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }
}
