<?php

namespace Modules\MPS\Services;

use Omnipay\Omnipay;
use Illuminate\Support\Facades\Log;

class Payment
{
    public $gateway;

    public function __construct($gateway = null, $testMode = false)
    {
        $this->gateway = Omnipay::create($gateway);
        if ($gateway == 'Stripe') {
            $this->gateway->setApiKey(env('STRIPE_SECRET'));
        } elseif ($gateway == 'PayPal_Pro') {
            $this->gateway->setUsername(env('PAYPAL_USERNAME'));
            $this->gateway->setPassword(env('PAYPAL_PASSWORD'));
            $this->gateway->setSignature(env('PAYPAL_SIGNATURE'));
        } elseif ($gateway == 'AuthorizeNetApi_Api') {
            $this->gateway->setAuthName(env('AUTHORIZE_LOGIN'));
            $this->gateway->setTransactionKey(env('AUTHORIZE_TRANSACTION_KEY'));
        } elseif ($gateway == 'PayPal_Rest') {
            $this->gateway->setSecret(env('PAYPAL_SECRET'));
            $this->gateway->setClientId(env('PAYPAL_CLIENT_ID'));
        } else {
            abort(500, 'Unknown payment gateway!');
        }
        $this->gateway->setTestMode($testMode);
    }

    public function processStoreRequest($request, $data, $payer)
    {
        $card_gateway  = env('CARD_GATEWAY');
        $currency_code = env('CURRENCY_CODE');
        try {
            if ($card_gateway == 'Stripe') {
                $response = $this->purchase([
                    'currency' => $currency_code,
                    'token'    => $request->token_id,
                    'amount'   => ($data['amount'] * 100),
                ]);
            } else {
                $card   = $request->only(['firstName', 'lastName', 'cvv', 'billingAddress1', 'billingCity', 'billingPostcode', 'billingState', 'billingCountry']);
                $expiry = explode('-', $request->expiry_date);
                if ($expiry[0] && $expiry[1]) {
                    $card['expiryYear']  = $expiry[0];
                    $card['expiryMonth'] = $expiry[1];
                } else {
                    return ['success' => false, 'message' => __('Expiry date has wrong format.')];
                }
                $card['number'] = $request->card_number;
                $response       = $this->purchase([
                    'card'        => $card,
                    'currency'    => $currency_code,
                    'amount'      => $data['amount'],
                    'description' => __('Processing payment ' . $data['amount'] . ' for ' . $payer->name . ($payer->company ? ' (' . $payer->company . ')' : '')),
                ]);
            }

            if ($response->isSuccessful()) {
                $data['received']               = 1;
                $data['gateway_transaction_id'] = $response->getTransactionReference();
                return $data;
            }
            Log::error(print_r($response, true));
            return ['success' => false, 'message' => $response->getMessage()];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function purchase($data)
    {
        return $this->gateway->purchase($data)->send();
    }
}
