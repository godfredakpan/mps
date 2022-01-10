<?php

namespace Modules\MPS\Observers;

use Modules\MPS\Models\AssetTransfer;

class AssetTransferObserver
{
    public function created(AssetTransfer $asset_transfer)
    {
        $to_transfer_record = $asset_transfer->toAccount->journal
            ->creditDollars($asset_transfer->amount, 'transfer_created')
            ->referencesObject($asset_transfer);
        $from_transfer_record = $asset_transfer->fromAccount->journal
            ->debitDollars($asset_transfer->amount, 'transfer_created')
            ->referencesObject($asset_transfer);
        $asset_transfer->disableLogging();
        $asset_transfer->update([
            'to_transaction_id'   => $to_transfer_record->id,
            'from_transaction_id' => $from_transfer_record->id,
        ]);
    }

    public function deleting(AssetTransfer $asset_transfer)
    {
        $to_transfer_record = $asset_transfer->toAccount->journal
            ->debitDollars($asset_transfer->amount, 'transfer_deleted')
            ->referencesObject($asset_transfer);
        $from_transfer_record = $asset_transfer->fromAccount->journal
            ->creditDollars($asset_transfer->amount, 'transfer_deleted')
            ->referencesObject($asset_transfer);
    }

    public function updating(AssetTransfer $asset_transfer)
    {
        if (!$asset_transfer->wasRecentlyCreated && $asset_transfer->getOriginal('amount') != $asset_transfer->amount) {
            $to_transfer_record = $asset_transfer->toAccount
                ->journal->debitDollars($asset_transfer->getOriginal('amount'), 'transfer_editing')
                ->referencesObject($asset_transfer);
            $from_transfer_record = $asset_transfer->fromAccount
                ->journal->creditDollars($asset_transfer->getOriginal('amount'), 'transfer_editing')
                ->referencesObject($asset_transfer);

            $to_transfer_record = $asset_transfer->toAccount->journal
                ->creditDollars($asset_transfer->amount, 'transfer_updated')
                ->referencesObject($asset_transfer);
            $from_transfer_record = $asset_transfer->fromAccount->journal
                ->debitDollars($asset_transfer->amount, 'transfer_updated')
                ->referencesObject($asset_transfer);
        }
    }
}
