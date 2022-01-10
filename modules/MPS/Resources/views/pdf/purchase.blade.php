<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ __('Purchase No.') }}: {{ $purchase->number }}</title>
  <link href="{{ asset('css/pdf.css') }}" rel="stylesheet" type="text/css" />
</head>

<body>
  <div class="max-w-3xl mx-auto text-gray-700 text-sm whitespace-normal break-normal">
    <div class="w-full pb-2 text-right">
      <img src="{{ $purchase->location->logo ?? $settings['default_logo'] }}" alt="{{ __($purchase->location->name) }}"
        class="max-h-20 w-48 float-left" />
      <div class="inline-block text-right ml-48">
        <div class="float-right">
          {!! generate_barcode($purchase->reference, null, 1, 70, 'black') !!}
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
      {{ trans_choice('purchase', 1) }}
    </div>
    <div class="py-2 border-b">
      <div class="left">
        <div class="pr-2">
          <div>{{ __('date') }}: <span class="font-bold">{{ $purchase->date->toFormattedDateString() }}</span>
          </div>
          <div>{{ __('created_at') }}: <span class="font-bold">{{ $purchase->created_at->toDayDateTimeString() }}</span></div>
        </div>
      </div>
      <div class="right">
        <div class="pl-2">
          <div>{{ __('Reference') }}: <span class="font-bold">{{ $purchase->reference }}</span></div>
          <div>{{ __('id') }}: <span class="font-bold">{{ $purchase->id }}</span></div>
        </div>
      </div>
      <div class="clearfix"></div>
    </div>
    @unless($purchase->paid)
      @if ($purchase->draft)
        <div class="bg-yellow-50 px-4 py-2 border-b text-yellow-700 text-center">
          {{ __('order_draft_text') }}
        </div>
      @endif
    @endunless
    <div class="py-4">
      <div class="left">
        <div class="pr-2 whitespace-normal break-words">
          <div class="text-sm">{{ trans_choice('store', 1) }}:</div>
          <strong>{{ $purchase->location->name }}</strong><br />
          {{ $purchase->location->address }}
          {{ $purchase->location->state_name }}
          {{ $purchase->location->country_name }}<br />
          {{ $purchase->location->phone }}<br />
          {{ $purchase->location->email }}<br />
        </div>
      </div>
      <div class="right">
        <div class="pl-2 whitespace-normal break-words">
          <div class="text-sm">{{ __('order_to') }}:</div>
          @if ($purchase->supplier)
            <strong>{{ $purchase->supplier->name }}</strong><br />
            {{ $purchase->supplier->address }}
            {{ $purchase->supplier->state_name }}
            {{ $purchase->supplier->country_name }}<br />
            {{ $purchase->supplier->phone }}<br />
            {{ $purchase->supplier->email }}<br />
          @endif
        </div>
      </div>
      <div class="clearfix"></div>
    </div>

    <div class="flex flex-col mt-4">
      <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="align-middle inline-block min-w-full sm:px-4 lg:px-8">
          <div class="overflow-hidden border">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-4 py-2 text-left text-xs uppercase">
                    {{ __('description') }}
                  </th>
                  <th scope="col" class="w-20 px-4 py-2 text-center text-xs uppercase">
                    {{ __('quantity') }}
                  </th>
                  <th scope="col" class="w-20 px-4 py-2 text-center text-xs uppercase">
                    {{ __('cost') }}
                  </th>
                  @if ($settings['show_discount'])
                    <td>{{ __('discount') }}</td>
                  @endif
                  @if ($settings['show_tax'])
                    <td>{{ __('tax') }}</td>
                  @endif
                  <th scope="col" class="w-24 px-4 py-2 text-center text-xs uppercase">
                    {{ __('subtotal') }}
                  </th>
                </tr>
              </thead>
              <tbody>
                @foreach ($purchase->items as $item)
                  @if ($item->variations->isNotEmpty())
                    <tr>
                      <td class="border-t px-4 py-2">
                        {{ __($item->item->name) }} [{{ $item->item->code }}]
                        @if ($item->item->alt_name)
                          <br /> {{ $item->item->alt_name }}
                        @endif
                        {{-- <div v-if="$item->variation" {{ $item->variation->id }}</div> --}}
                      </td>
                      <td class="border-t"></td>
                      <td class="border-t"></td>
                      @if ($settings['show_discount'])
                        <td class="border-t"></td>
                      @endif
                      @if ($settings['show_tax'])
                        <td class="border-t"></td>
                      @endif
                      <td class="border-t"></td>
                    </tr>
                    @foreach ($item->variations as $variation)
                      <tr>
                        <td class="px-4 pb-2">
                          {{ meta_array_to_string($variation->meta) }}
                        </td>
                        <td class="px-4 pb-2 text-center">
                          {{ formatQuantity($variation->pivot->quantity) }}
                        </td>
                        <td class="px-4 pb-2 text-right">
                          {{ formatNumber($variation->pivot->cost) }}
                        </td>
                        @if ($settings['show_discount'])
                          <td class="px-4 pb-2 text-right">
                            {{ formatNumber($variation->pivot->total_discount_amount) }}
                          </td>
                        @endif
                        @if ($settings['show_tax'])
                          <td class="px-4 pb-2 text-right">
                            {{ formatNumber($variation->pivot->total_tax_amount) }}
                          </td>
                        @endif
                        <td class="px-4 pb-2 text-right">
                          {{ formatNumber($variation->pivot->total) }}</td>
                      </tr>
                    @endforeach
                  @else
                    <tr>
                      <td class="border-t px-4 py-2">
                        {{ __($item->item->name) }} [{{ $item->item->code }}]
                        @if ($item->item->alt_name)
                          <br /> {{ $item->item->alt_name }}
                        @endif
                      </td>
                      <td class="border-t px-4 py-2 text-center">
                        {{ formatNumber($item->quantity) }}
                      </td>
                      <td class="border-t px-4 py-2 text-right">
                        {{ formatNumber($item->cost) }}
                      </td>
                      <td class="border-t px-4 py-2 text-right">
                        {{ formatNumber($item->subtotal) }}
                      </td>
                    </tr>
                  @endif
                @endforeach
              </tbody>
              <tfoot class="stripe divide-y divide-gray-200 text-gray-900">
                <tr>
                  <td colspan="3" class="px-4 py-2 text-gray-900">
                    {{ __('total') }}
                  </td>
                  <td class="px-4 py-2 font-bold text-right">
                    {{ formatNumber($purchase->total) }}
                  </td>
                </tr>
                @if ($purchase->total_tax_amount)
                  <tr>
                    <td colspan="3" class="px-4 py-2 text-gray-900">
                      {{ __('total_tax_amount') }}
                    </td>
                    <td class="px-4 py-2 font-bold text-right">
                      {{ formatNumber($purchase->total_tax_amount) }}
                    </td>
                  </tr>
                @endif
                @if ($purchase->total_discount_amount)
                  <tr>
                    <td colspan="3" class="px-4 py-2 text-gray-900">
                      {{ __('total_discount_amount') }}
                    </td>
                    <td class="px-4 py-2 font-bold text-right">
                      {{ formatNumber($purchase->total_discount_amount) }}
                    </td>
                  </tr>
                @endif
                @if ($purchase->shipping)
                  <tr>
                    <td colspan="3" class="px-4 py-2 text-gray-900">
                      {{ __('shipping') }}
                    </td>
                    <td class="px-4 py-2 font-bold text-right">
                      {{ formatNumber($purchase->shipping) }}
                    </td>
                  </tr>
                @endif
                <tr>
                  <td colspan="3" class="px-4 py-2 text-gray-900">
                    {{ __('grand_total') }}
                  </td>
                  <td class="px-4 py-2 font-bold text-right">
                    {{ formatNumber($purchase->grand_total) }}
                  </td>
                </tr>
                @if ($purchase->payments->isNotEmpty())
                  @foreach ($purchase->payments as $payment)
                    <tr>
                      <td colspan="3" class="px-4 py-2">
                        <span>{{ __('payment_settlement') }}</span><br />
                        <span class="text-sm">({{ __('date') }}:
                          {{ $payment->created_at->toDayDateTimeString() }},
                          {{ __('Reference') }}:
                          {{ $payment->reference }})</span>
                      </td>
                      <td class="px-4 py-2 font-bold text-right">
                        {{ formatNumber($payment->pivot->amount) }}</td>
                    </tr>
                  @endforeach
                  <tr>
                    <td colspan="3" class="px-4 py-2">{{ __('balance_due') }}</td>
                    <td class="px-4 py-2 font-bold text-right">
                      {{ formatNumber($purchase->grand_total - ($purchase->payments->isNotEmpty() ? $purchase->payments->reduce(fn($a, $p) => $a + $p->amount, 0) : 0)) }}
                    </td>
                  </tr>
                @else
                  <tr>
                    <td colspan="3" class="px-4 py-2">{{ __('paid') }}</td>
                    <td class="px-4 py-2 font-bold text-right">{{ formatNumber(0) }}</td>
                  </tr>
                  <tr>
                    <td colspan="3" class="px-4 py-2">{{ __('balance_due') }}</td>
                    <td class="px-4 py-2 font-bold text-right">
                      {{ formatNumber($purchase->grand_total) }}</td>
                  </tr>
                @endif
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>

    @php
      $taxSummary = \Modules\MPS\Actions\TaxSummary::calculate($purchase);
    @endphp

    @if ($settings['show_tax_summary'] && $purchase->total_tax_amount && $taxSummary->isNotEmpty())
      <div class="mt-4 avoid">
        <div class="flex flex-col border">
          <table class="summary min-w-full border divide-y divide-gray-200">
            <thead class="bg-gray-50 divide-y divide-gray-200">
              <tr>
                <th scope="col" colspan="5" class="px-4 py-2 text-left text-xs uppercase">
                  {{ __('tax_summary') }}
                </th>
              </tr>
              <tr>
                <th scope="col" class="px-4 py-2 text-left text-xs uppercase">
                  {{ __('name') }}
                </th>
                <th scope="col" class="px-4 py-2 text-center text-xs uppercase">
                  {{ __('code') }}
                </th>
                <th scope="col" class="px-4 py-2 text-center text-xs uppercase">
                  {{ __('qty_weight') }}
                </th>
                <th scope="col" class="px-4 py-2 text-center text-xs uppercase">
                  {{ __('tax_ex_amount') }}
                </th>
                <th scope="col" class="px-4 py-2 text-center text-xs uppercase">
                  {{ __('tax_amount') }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              @foreach ($taxSummary as $tax)
                <tr>
                  {{-- <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}"> --}}
                  <td class="px-4 py-2">
                    {{ __($tax['name']) }}
                  </td>
                  <td class="px-4 py-2 text-center">
                    {{ $tax['code'] }}
                  </td>
                  <td class="px-4 py-2 text-center">
                    {{ formatNumber($tax['quantity'] ?? 1) }}
                  </td>
                  <td class="px-4 py-2 text-right">
                    {{ formatNumber($tax['item_net_amount']) }}
                  </td>
                  <td class="px-4 py-2 text-right">
                    {{ formatNumber($tax['amount']) }}
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot class="divide-y divide-gray-200 text-gray-900">
              <tr class="bg-gray-50">
                <td colspan="4" class="px-4 py-2 text-right">
                  {{ __('total_tax_amount') }}
                </td>
                <td class="px-4 py-2 font-bold text-right">
                  {{ formatNumber($purchase->total_tax_amount) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
    @endif

    @if ($purchase->details)
      <div class="py-4">
        {{ __($purchase->details) }}
      </div>
    @endif

    <div class="w-full fixed bottom-4">
      <div class="cgd text-center text-sm py-2 text-gray-500">
        {{ __('order_cgd') }}
      </div>
    </div>

  </div>

</body>

</html>
