<?php

namespace Modules\MPS\Services;

class PayPal
{
    public function complete($data)
    {
        return $this->gateway()->completePurchase($data)->send();
    }

    public function formatAmount($amount)
    {
        return number_format($amount, 2, '.', '');
    }

    public function gateway()
    {
        $gateway = \Omnipay\Omnipay::create('PayPal_Express');

        $gateway->setUsername(env('PAYPAL_USERNAME'));
        $gateway->setPassword(env('PAYPAL_PASSWORD'));
        $gateway->setSignature(env('PAYPAL_SIGNATURE'));
        $gateway->setBrandName(env('APP_NAME'));
        // $gateway->setLogoImageUrl();
        // $gateway->setHeaderImageUrl();
        $gateway->setTestMode(demo());

        return $gateway;
    }

    public function getCancelUrl($hash)
    {
        return \URL::signedRoute('order', ['act' => 'payment', 'hash' => $hash, 'gateway' => 'paypal', 'type' => 'cancel']);
    }

    public function getNotifyUrl()
    {
    }

    public function getReturnUrl($hash)
    {
        return route('paypal.completed', ['act' => 'payment', 'hash' => $hash]);
    }

    public function purchase($data)
    {
        return $this->gateway()->purchase($data)->send();
    }
}
