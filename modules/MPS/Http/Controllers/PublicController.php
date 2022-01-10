<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PublicController extends Controller
{
    public function completed(Request $request, $hash)
    {
        $payment = \Modules\MPS\Models\Payment::where('hash', $hash)->first();

        if (!empty($payment)) {
            $paypal   = new \Modules\MPS\Services\PayPal;
            $response = $paypal->complete([
                'amount'        => $paypal->formatAmount($payment->amount),
                'transactionId' => $payment->id,
                'currency'      => env('CURRENCY_CODE'),
                'cancelUrl'     => $paypal->getCancelUrl($hash),
                'returnUrl'     => $paypal->getReturnUrl($hash),
            ]);

            if ($response->isSuccessful()) {
                $payment->update(['gateway' => 'PayPal_Express', 'gateway_transaction_id' => $response->getTransactionReference(), 'received' => 1]);
                return redirect()->route('order', ['act' => 'payment', 'hash' => $payment->hash])->with('message', __('mps::order.payment_success_text'));
            }

            return redirect()->route('order', ['act' => 'payment', 'hash' => $payment->hash])->with('message', $response->getMessage());
        }
        abort(404);
    }

    // public function index(Request $request, $view, $hash)
    // {
    //     if (auth()->guest() && !$request->token && !$request->hasValidSignature()) {
    //         abort(404);
    //     }

    //     if ($view == 'sale') {
    //         $sale = \Modules\MPS\Models\Sale::where('hash', $hash)->first();
    //         if (!empty($sale)) {
    //             $sale->attributes = extra_attributes('sale');
    //             $settings         = json_decode(json_encode(mps_config()));
    //             $sale->load(['taxes', 'location', 'customer', 'payments'])->withSelected();
    //             return view('mps::sales.simple', compact('sale', 'settings', 'request'));
    //         }
    //     } elseif ($view == 'purchase') {
    //         $purchase = \Modules\MPS\Models\Purchase::where('hash', $hash)->first();
    //         if (!empty($purchase)) {
    //             $purchase->attributes = extra_attributes('purchase');
    //             $settings             = json_decode(json_encode(mps_config()));
    //             $purchase->load('items', 'taxes', 'location', 'supplier', 'payments');
    //             return view('mps::purchases.simple', compact('purchase', 'settings', 'request'));
    //         }
    //     } elseif ($view == 'payment') {
    //         $payment = \Modules\MPS\Models\Payment::where('hash', $hash)->first();
    //         if (!empty($payment)) {
    //             $accounts = \Modules\MPS\Models\Account::offline()->get();
    //             $settings = json_decode(json_encode(mps_config()));
    //             return view('mps::payments.simple', compact('payment', 'settings', 'accounts', 'request'));
    //         }
    //     }

    //     abort(404);
    //     // return redirect()->to('/');
    // }

    public function pay(Request $request, $gateway, $hash)
    {
        $payment = \Modules\MPS\Models\Payment::withoutGlobalScopes()->where('hash', $hash)->first();
        if (!empty($payment)) {
            if ($payment->received) {
                return response()->json(['success' => false, 'message' => __('payment_already_received_text')]);
            }
            if ($gateway == 'offline') {
                if ($request->approve && $payment->gateway == 'offline' && auth()->check()) {
                    $user = auth()->user();
                    if (!$user->hasRole(['customer', 'supplier'])) {
                        $payment->update(['review' => null, 'reviewed_by' => $user->id, 'received' => 1]);
                        return response(['success' => true], 200);
                    }
                    return response(['success' => false], 403);
                }
                $v = $request->validate([
                    'file'    => 'required|file',
                    'account' => 'required|exists:accounts,id',
                ]);

                if ($request->file->isValid()) {
                    $ext  = $request->file->extension();
                    $name = $payment->hash . '.' . $ext;
                    $request->file->storeAs('payments', $name);
                    $payment->update(['account_id' => $v['account'], 'gateway' => 'offline', 'file' => $name, 'review' => 1]);
                    return redirect()->route('order', ['act' => 'payment', 'hash' => $payment->hash])->with('message', __('mps::order.uploaded_text'));
                }
                return redirect()->route('order', ['act' => 'payment', 'hash' => $payment->hash])->with('error', __('mps::order.payment_error_text'));
            }

            $card_gateway  = env('CARD_GATEWAY');
            $currency_code = env('CURRENCY_CODE');
            $omni          = new \Modules\MPS\Services\Payment($card_gateway, demo());

            try {
                if ($card_gateway == 'Stripe') {
                    $response = $omni->purchase([
                        'currency' => $currency_code,
                        'amount'   => $payment->amount * 100,
                        'token'    => $request->input('source'),
                    ]);
                } else {
                    $card     = $request->only(['firstName', 'lastName', 'number', 'expiryMonth', 'expiryYear', 'cvv', 'billingAddress1', 'billingCity', 'billingPostcode', 'billingState', 'billingCountry']);
                    $response = $omni->purchase([
                        'card'        => $card,
                        'currency'    => $currency_code,
                        'amount'      => $payment->amount,
                        'description' => $payment->id . ', ' . __('reference') . ': ' . $payment->reference,
                    ]);
                }

                if ($response->isRedirect()) {
                    // $response->redirect();
                    return response()->json(['success' => true, 'redirect' => $response->redirect()]);
                } elseif ($response->isSuccessful()) {
                    $payment->update(['gateway' => $card_gateway, 'gateway_transaction_id' => $response->getTransactionReference(), 'received' => 1]);
                    return response()->json(['success' => true, 'payment' => $payment->refresh()]);
                    // return redirect()->route('order', ['act' => 'payment', 'hash' => $payment->hash])->with('message', __('mps::order.payment_success_text'));
                }
                Log::error(print_r($response, true));
                return response()->json(['success' => false, 'message' => $response->getMessage()]);
                // return redirect()->route('order', ['act' => 'payment', 'hash' => $payment->hash])->with('error', $response->getMessage());
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
                // abort(500, __('mps::order.exception_error_text'));
            }
        }
        abort(400);
    }

    public function paypal(Request $request, $hash)
    {
        $payment = \Modules\MPS\Models\Payment::where('hash', $hash)->first();

        if (!empty($payment)) {
            $paypal   = new \Modules\MPS\Services\PayPal;
            $response = $paypal->purchase([
                'amount'        => $paypal->formatAmount($payment->amount),
                'transactionId' => $payment->id,
                'currency'      => env('CURRENCY_CODE'),
                'cancelUrl'     => $paypal->getCancelUrl($hash),
                'returnUrl'     => $paypal->getReturnUrl($hash),
            ]);

            if ($response->isRedirect()) {
                $response->redirect();
            }

            return redirect()->away(route('mps') . '#/views?payment=' . $hash . '&error=' . $response->getMessage());
            // return redirect()->route('order', ['act' => 'payment', 'hash' => $payment->hash])->with('message', $response->getMessage());
        }
        abort(404);
    }
}
