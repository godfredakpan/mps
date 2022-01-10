<?php

namespace Modules\MPS\Services;

use Illuminate\Http\Request;
use Modules\MPS\Models\GiftCard;
use Modules\MPS\Models\Purchase;
use Modules\MPS\Models\ReturnOrder;

class OrderPaymentService
{
    private $model;

    private $request;

    public function __construct(Request $request, $model)
    {
        $this->model   = $model;
        $this->request = $request;
    }

    public function process()
    {
        if (($this->request->payment && $this->request->input('payment.amount') > 0) || $this->request->add_payment) {
            $purchase         = ($this->model instanceof Purchase);
            $return           = ($this->model instanceof ReturnOrder);
            $gift_card_number = $this->request->input('payment.gift_card_number');
            $payment          = $this->model->payable->payments()->create([
                'user_id'          => auth()->id(),
                'gift_card_number' => $gift_card_number,
                'amount'           => $this->getAmount(),
                'account_id'       => location()->account_id,
                'return_id'        => $return ? $this->model->id : null,
                'sale_id'          => $purchase ? null : $this->model->id,
                'purchase_id'      => $purchase ? $this->model->id : null,
                'gateway'          => $this->request->input('payment.gateway'),
                'received'         => $this->request->add_payment ? false : true,
                'card_holder'      => $this->request->input('payment.card_holder'),
                'expiry_date'      => $this->request->input('payment.expiry_date'),
                'cheque_number'    => $this->request->input('payment.cheque_number'),
                'note'             => $this->request->input('payment.payment_details'),
                'card_number'      => $this->getCardNumber($this->request->input('payment.card_number')),
            ]);
            if ($gift_card_number) {
                $gift_card = GiftCard::where('number', $gift_card_number)->first();
                $gift_card->updateBalance($gift_card->balance - $payment->amount);
                $gift_card->log([
                    'amount'      => $payment->amount,
                    'description' => __('gift_card_used', ['reference' => $payment->reference]),
                ]);
            }
        }
    }

    private function getAmount()
    {
        if ($this->request->add_payment) {
            return $this->model->grand_total;
        }
        return formatDecimal($this->request->input('payment.amount'), 2);
    }

    private function getCardNumber($number = null)
    {
        return $number ? substr($number, -4) : '';
    }
}
