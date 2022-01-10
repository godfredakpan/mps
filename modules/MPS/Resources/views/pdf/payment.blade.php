<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ __('Sale No.') }}: {{ $payment->number }}</title>
  <link href="{{ asset('css/pdf.css') }}" rel="stylesheet" type="text/css" />
</head>

<body>
  <div class="max-w-3xl mx-auto text-gray-700 text-sm whitespace-normal break-normal">
    <div class="w-full pb-2 text-right">
      <img src="{{ $payment->location->logo ?? $settings['default_logo'] }}" alt="{{ __($payment->location->name) }}"
        class="max-h-20 w-48 float-left" />
      <div class="inline-block text-right ml-48">
        <div class="float-right">
          {!! generate_barcode($payment->reference, null, 1, 70, 'black') !!}
        </div>
        <div class="text-xs text-right pr-2 float-right inline-block">
          <strong>{{ $settings['name'] }} ({{ $settings['short_name'] }})</strong><br />
          <strong>{{ $settings['company'] }}</strong><br />
          {{ $settings['email'] }} <span class="text-muted">|</span> {{ $settings['phone'] }}<br />
          {{ $settings['address'] }}
        </div>
      </div>
      <div class="clearfix"></div>
    </div>

    <div class="mt-2 py-2 text-center uppercase font-bold border-b-2 border-t-2">
      {{ $payment->received ? __('Payment Note') : __('Payment Request') }}
    </div>

    <div class="py-2 border-b">
      <div class="left">
        <div class="pr-2">
          <div>{{ __('Created at') }}: <span class="font-bold">{{ $payment->created_at->toDayDateTimeString() }}</span></div>
          @if ($payment->updated_at != $payment->created_at)
            <div>{{ __('Updated at') }}: <span class="font-bold">{{ $payment->updated_at->toDayDateTimeString() }}</span></div>
          @endif
        </div>
      </div>
      <div class="right">
        <div class="pl-2">
          <div>{{ __('Reference') }}: <span class="font-bold">{{ $payment->reference }}</span></div>
          <div>{{ __('Id') }}: <span class="font-bold">{{ $payment->id }}</span></div>
        </div>
      </div>
      <div class="clearfix"></div>
    </div>

    @if ($payment->review && !$payment->reviewed_by)
      <div class="bg-yellow-50 px-4 py-2 border-b text-yellow-700 text-center">
        {{ __('We will update the payment status after reviewing the submitted receipt.') }}
      </div>
    @elseif(!$payment->received)
      <div class="bg-yellow-50 px-4 py-2 border-b text-yellow-700 text-center">
        {{ __('Please make payments at your earliest convenience.') }}
      </div>
    @endif

    <div class="py-4">
      <div class="left">
        <div class="pr-2 whitespace-normal break-words">
          <div class="text-sm">{{ __('Store') }}:</div>
          <strong>{{ $payment->location->name }}</strong><br />
          {{ $payment->location->address }}
          {{ $payment->location->state_name }}
          {{ $payment->location->country_name }}<br />
          {{ $payment->location->phone }}<br />
          {{ $payment->location->email }}<br />
        </div>
      </div>
      <div class="right">
        <div class="pl-2 whitespace-normal break-words">
          <div class="text-sm">{{ $payment->payable instanceof \Modules\MPS\Models\Customer ? __('From') : __('To') }}:</div>
          @if ($payment->payable && $payment->payable->id != $settings['default_customer'])
            <strong>{{ $payment->payable->name }}</strong><br />
            {{ $payment->payable->address }}
            {{ $payment->payable->state_name }}
            {{ $payment->payable->country_name }}<br />
            {{ $payment->payable->phone }}<br />
            {{ $payment->payable->email }}<br />
          @elseif ($payment->sale && default_customer($payment->sale->customer_id) && $payment->sale->address)
            <strong>{{ $$payment->sale->address->first_name }} {{ $$payment->sale->address->last_name }}
            </strong><br />
            {{ $$payment->sale->address->address }}
            {{ $$payment->sale->address->state_name }}
            {{ $$payment->sale->address->country_name }}<br />
            {{ $$payment->sale->address->phone }}<br />
            {{ $$payment->sale->address->email }}<br />
          @endif
        </div>
      </div>
      <div class="clearfix"></div>
    </div>

    <div class="flex flex-col mt-4">
      <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="align-middle inline-block min-w-full sm:px-4 lg:px-8">
          <div class="overflow-hidden border rounded-md">
            <table class="summary min-w-full divide-y divide-gray-200">
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr>
                  <td class="px-6 py-3">{{ __('Account') }}</td>
                  <td class="px-6 py-3">{{ __($payment->account->name) }}</td>
                </tr>
                <tr>
                  <td class="px-6 py-3 font-bold">
                    {{ $payment->received ? __('Payment Received') : __('Payment Requested') }}</td>
                  <td class="px-6 py-3 font-bold">{{ formatNumber($payment->amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    @if ($payment->details)
      <div class="py-4">
        {{ __($payment->details) }}
      </div>
    @endif

    <div class="w-full fixed bottom-4">
      <div class="cgd text-center text-sm py-2 text-gray-500">
        {{ __('This is a computer-generated document. No signature is required.') }}
      </div>
    </div>

  </div>

</body>

</html>
