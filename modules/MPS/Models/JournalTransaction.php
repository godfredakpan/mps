<?php

namespace Modules\MPS\Models;

class JournalTransaction extends Base
{
    public static $searchable = ['id', 'created_at', 'debit', 'credit', 'type', 'subject_type', 'subject_id'];

    protected $hidden = ['posted_at', 'updated_at', 'deleted_at'];

    protected $with = ['subject'];

    public function child()
    {
        return $this->hasOne(JournalTransaction::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function parent()
    {
        return $this->belongsTo(JournalTransaction::class);
    }

    public function referencesObject($object)
    {
        $this->subject_type = get_class($object);
        $this->subject_id   = $object->id;
        $this->save();
        return $this;
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(function ($transaction) {
            $transaction->journal->resetCurrentBalances();
        });
    }
}
