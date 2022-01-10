<?php

namespace Modules\MPS\Console;

use Illuminate\Console\Command;
use Modules\MPS\Models\Sequence;

class ResetReferenceSequence extends Command
{
    protected $description = 'Reset references sequence number';

    protected $signature = 'reference:reset';

    public function __construct()
    {
        parent::__construct();
    }

    protected function resetReference($sequence)
    {
        return $sequence->update([
            'sale'             => 0,
            'order'            => 0,
            'income'           => 0,
            'salary'           => 0,
            'account'          => 0,
            'expense'          => 0,
            'payment'          => 0,
            'delivery'         => 0,
            'purchase'         => 0,
            'quotation'        => 0,
            'return_order'     => 0,
            'recurring_sale'   => 0,
            'asset_transfer'   => 0,
            'stock_transfer'   => 0,
            'stock_adjustment' => 0,
            'last_reset_date'  => now(),
        ]);
    }

    public function handle()
    {
        $sequence = Sequence::first();
        $format   = mps_config('reference', true);
        if ($format == 'yearly') {
            if (!$sequence->last_reset_date->isSameYear(now())) {
                if ($this->resetReference($sequence)) {
                    $this->info('The sequence numbers has been reset for this year.');
                    activity()->withProperties($sequence)->log('The sequence numbers has been reset for this year.');
                } else {
                    $this->error('The sequence numbers reset command has been failed for this year.');
                }
            }
        } elseif ($format == 'monthly') {
            if (!$sequence->last_reset_date->isSameMonth(now())) {
                if ($this->resetReference($sequence)) {
                    $this->info('The sequence numbers has been reset for this month.');
                    activity()->withProperties($sequence)->log('The sequence numbers has been reset for this month.');
                } else {
                    $this->error('The sequence numbers reset command has been failed for this month.');
                }
            }
            // } else {
        //     $this->line('There is no need to reset sequence numbers for your current reference setting.');
        }
    }
}
