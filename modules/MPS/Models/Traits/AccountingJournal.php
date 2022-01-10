<?php

namespace Modules\MPS\Models\Traits;

use Modules\MPS\Models\Journal;

trait AccountingJournal
{
    public function initJournal()
    {
        if (!$this->journal) {
            $journal = new Journal();
            $journal->setCurrency(env('CURRENCY_CODE', 'USD'));
            $journal->balance = 0;
            return $this->journal()->save($journal);
        }
        throw new \Exception('Journal already exists.');
    }

    public function journal()
    {
        return $this->morphOne(Journal::class, 'morphed');
    }
}
