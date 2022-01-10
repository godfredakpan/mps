<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ __('Sale No.') }}: {{ $sale->number }}</title>
  <link href="{{ asset('css/pdf.css') }}" rel="stylesheet" type="text/css" />
</head>

<body>
  <div class="max-w-3xl mx-auto text-gray-700 text-sm whitespace-normal break-normal">
    <div class="w-full pb-2 text-right">
      <img src="{{ $sale->location->logo ?? $settings['default_logo'] }}" alt="{{ __($sale->location->name) }}"
        class="max-h-20 w-48 float-left" />
      <div class="inline-block text-right ml-48">
        <div class="float-right">
          {!! generate_barcode($sale->reference, null, 1, 70, 'black') !!}
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
      {{ trans_choice('sale', 1) }}
    </div>
    <div class="py-2 border-b">
      <div class="left">
        <div class="pr-2">
          <div>{{ __('date') }}: <span class="font-bold">{{ $sale->date->toFormattedDateString() }}</span>
          </div>
          <div>{{ __('created_at') }}: <span class="font-bold">{{ $sale->created_at->toDayDateTimeString() }}</span></div>
        </div>
      </div>
      <div class="right">
        <div class="pl-2">
          <div>{{ __('Reference') }}: <span class="font-bold">{{ $sale->reference }}</span></div>
          <div>{{ __('id') }}: <span class="font-bold">{{ $sale->id }}</span></div>
        </div>
      </div>
      <div class="clearfix"></div>
    </div>
    @unless($sale->paid)
      @if ($sale->draft)
        <div class="bg-yellow-50 px-4 py-2 border-b text-yellow-700 text-center">
          {{ __('order_draft_text') }}
        </div>
      @endif
    @endunless
    <div class="py-4">
      <div class="left">
        <div class="pr-2 whitespace-normal break-words">
          <div class="text-sm">{{ trans_choice('store', 1) }}:</div>
          <strong>{{ $sale->location->name }}</strong><br />
          {{ $sale->location->address }}
          {{ $sale->location->state_name }}
          {{ $sale->location->country_name }}<br />
          {{ $sale->location->phone }}<br />
          {{ $sale->location->email }}<br />
        </div>
      </div>
      <div class="right">
        <div class="pl-2 whitespace-normal break-words">
          <div class="text-sm">{{ __('bill_to') }}:</div>
          @if ($sale->customer && $sale->customer->id != $settings['default_customer'])
            <strong>{{ $sale->customer->name }}</strong><br />
            {{ $sale->customer->address }}
            {{ $sale->customer->state_name }}
            {{ $sale->customer->country_name }}<br />
            {{ $sale->customer->phone }}<br />
            {{ $sale->customer->email }}<br />
          @else
            <strong>{{ $sale->address->first_name }} {{ $sale->address->last_name }}
            </strong><br />
            {{ $sale->address->address }}
            {{ $sale->address->state_name }}
            {{ $sale->address->country_name }}<br />
            {{ $sale->address->phone }}<br />
            {{ $sale->address->email }}<br />
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
                    {{ __('price') }}
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
                @foreach ($sale->items as $item)
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
                          {{ formatNumber($variation->pivot->price) }}
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
                  @elseif ($item->portions->isNotEmpty())
                    @foreach ($item->portions as $portion)
                      <tr>
                        <td class="border-t px-4 py-2">
                          {{ __($item->item->name) }} [{{ $item->item->code }}]
                          @if ($item->item->alt_name)
                            <br /> {{ $item->item->alt_name }}
                          @endif
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
                      <tr>
                        <td class="px-4 pb-2">
                          {{ __('Portion') }}: <span class="uppercase">{{ $portion->name }}</span>
                        </td>
                        <td class="px-4 pb-2 text-center">
                          {{ formatQuantity($portion->pivot->quantity) }}
                        </td>
                        <td class="px-4 pb-2 text-right">
                          @if ($settings['show_discount'])
                            {{ formatNumber($portion->pivot->net_price) }}
                          @elseif ($settings['show_tax'])
                            {{ formatNumber($portion->pivot->price) }}
                          @else
                            {{ formatNumber($portion->pivot->price - $portion->pivot->discount_amount + $portion->pivot->tax_amount) }}
                          @endif
                          <!-- {{ $portion->pivot->net_price }} -->
                        </td>
                        @if ($settings['show_discount'])
                          <td class="px-4 pb-2 text-right">
                            {{ formatNumber($portion->pivot->total_discount_amount) }}
                          </td>
                        @endif
                        @if ($settings['show_tax'])
                          <td class="px-4 pb-2 text-right">
                            {{ formatNumber($portion->pivot->total_tax_amount) }}
                          </td>
                        @endif
                        <td class="px-4 pb-2 text-right">
                          {{ formatNumber($portion->pivot->total) }}
                        </td>
                      </tr>
                      @if ($portion->portion_items && $portion->portion_items->isNotEmpty())
                        @foreach ($portion->portion_items as $pe)
                          <tr class="text-gray-500">
                            <td class="px-4 {{ $loop->last ? 'pb-2' : 'pb-0' }}">
                              {{ __($pe->item->name) }}
                              @if ($pe->meta)
                                ({{ meta_array_to_string($pe->meta) }})
                              @endif
                            </td>
                            <td class="px-4 {{ $loop->last ? 'pb-2' : 'pb-0' }} text-right">
                              {{ $pe->quantity * $portion->pivot->quantity }}
                            </td>
                            <td class="px-4 {{ $loop->last ? 'pb-2' : 'pb-0' }} text-right">
                            </td>
                            @if ($settings['show_discount'])
                              <td class="px-4 {{ $loop->last ? 'pb-2' : 'pb-0' }} text-right"></td>
                            @endif
                            @if ($settings['show_tax'])
                              <td class="px-4 {{ $loop->last ? 'pb-2' : 'pb-0' }} text-right"></td>
                            @endif
                            <td class="px-4 {{ $loop->last ? 'pb-2' : 'pb-0' }} text-right">
                            </td>
                          </tr>
                        @endforeach
                      @endif
                      @if ($portion->essentials && $portion->essentials->isNotEmpty())
                        @foreach ($portion->essentials as $pe)
                          <tr class="text-gray-500">
                            <td class="px-4 pb-0">
                              {{ __($pe->item->name) }}
                              @if ($pe->meta)
                                ({{ meta_array_to_string($pe->meta) }})
                              @endif
                            </td>
                            <td class="px-4 pb-0 text-center">
                              {{ formatQuantity($pe->quantity * $portion->pivot->quantity) }}</td>
                            <td class="px-4 pb-0 text-right"></td>
                            @if ($settings['show_discount'])
                              <td class="px-4 pb-0 text-right"></td>
                            @endif
                            @if ($settings['show_tax'])
                              <td class="px-4 pb-0 text-right"></td>
                            @endif
                            <td class="px-4 pb-0 text-right"></td>
                          </tr>
                        @endforeach
                      @endif

                      @foreach ($portion->choosables as $group)
                        @foreach ($group->items as $pcitem)
                          @if (collect($portion->pivot->choosables)->where('id', $group->id)->where('item_id', $pcitem->item_id)->first())
                            <tr class="text-gray-500">
                              <td class="px-4 {{ $loop->parent->last ? 'pb-2' : 'pb-0' }}">
                                {{ __($pcitem->item->name) }} (<strong class="text-xs">{{ __($group->name) }}</strong>)
                                @if ($group->meta)
                                  ({{ meta_array_to_string($group->meta) }})
                                @endif
                              </td>
                              <td class="px-4 {{ $loop->parent->last ? 'pb-2' : 'pb-0' }} text-center">
                                {{ formatQuantity($pcitem->quantity * $portion->pivot->quantity) }}
                              </td>
                              <td class="px-4 {{ $loop->parent->last ? 'pb-2' : 'pb-0' }}"></td>
                              @if ($settings['show_discount'])
                                <td class="px-4 {{ $loop->parent->last ? 'pb-2' : 'pb-0' }}"></td>
                              @endif
                              @if ($settings['show_tax'])
                                <td class="px-4 {{ $loop->parent->last ? 'pb-2' : 'pb-0' }}"></td>
                              @endif
                              <td class="px-4 {{ $loop->parent->last ? 'pb-2' : 'pb-0' }}"></td>
                            </tr>
                          @endif
                        @endforeach
                      @endforeach
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
                        {{ formatNumber($item->price) }}
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
                    {{ __('Total') }}
                  </td>
                  <td class="px-4 py-2 font-bold text-right">
                    {{ formatNumber($sale->total) }}
                  </td>
                </tr>
                @if ($sale->total_tax_amount)
                  <tr>
                    <td colspan="3" class="px-4 py-2 text-gray-900">
                      {{ __('Total Tax Amount') }}
                    </td>
                    <td class="px-4 py-2 font-bold text-right">
                      {{ formatNumber($sale->total_tax_amount) }}
                    </td>
                  </tr>
                @endif
                @if ($sale->total_discount_amount)
                  <tr>
                    <td colspan="3" class="px-4 py-2 text-gray-900">
                      {{ __('Total Discount Amount') }}
                    </td>
                    <td class="px-4 py-2 font-bold text-right">
                      {{ formatNumber($sale->total_discount_amount) }}
                    </td>
                  </tr>
                @endif
                @if ($sale->shipping)
                  <tr>
                    <td colspan="3" class="px-4 py-2 text-gray-900">
                      {{ __('Shipping') }}
                    </td>
                    <td class="px-4 py-2 font-bold text-right">
                      {{ formatNumber($sale->shipping) }}
                    </td>
                  </tr>
                @endif
                <tr>
                  <td colspan="3" class="px-4 py-2 text-gray-900">
                    {{ __('Grand Total') }}
                  </td>
                  <td class="px-4 py-2 font-bold text-right">
                    {{ formatNumber($sale->grand_total) }}
                  </td>
                </tr>
                @if ($sale->payments->isNotEmpty())
                  @foreach ($sale->payments as $payment)
                    <tr>
                      <td colspan="3" class="px-4 py-2">
                        <span>{{ __('Payment settlement') }}</span><br />
                        <span class="text-sm">({{ __('Date') }}:
                          {{ $payment->created_at->toDayDateTimeString() }},
                          {{ __('Reference') }}:
                          {{ $payment->reference }})</span>
                      </td>
                      <td class="px-4 py-2 font-bold text-right">
                        {{ formatNumber($payment->pivot->amount) }}</td>
                    </tr>
                  @endforeach
                  <tr>
                    <td colspan="3" class="px-4 py-2">{{ __('Balance Due') }}</td>
                    <td class="px-4 py-2 font-bold text-right">
                      {{ formatNumber($sale->grand_total - ($sale->payments->isNotEmpty() ? $sale->payments->reduce(fn($a, $p) => $a + $p->amount, 0) : 0)) }}
                    </td>
                  </tr>
                @else
                  <tr>
                    <td colspan="3" class="px-4 py-2">{{ __('paid') }}</td>
                    <td class="px-4 py-2 font-bold text-right">{{ formatNumber(0) }}</td>
                  </tr>
                  <tr>
                    <td colspan="3" class="px-4 py-2">{{ __('Balance Due') }}</td>
                    <td class="px-4 py-2 font-bold text-right">
                      {{ formatNumber($sale->grand_total) }}</td>
                  </tr>
                @endif
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>

    @php
      $taxSummary = \Modules\MPS\Actions\TaxSummary::calculate($sale, true);
    @endphp

    @if ($settings['show_tax_summary'] && $sale->total_tax_amount && $taxSummary->isNotEmpty())
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
                  {{ formatNumber($sale->total_tax_amount) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
    @endif

    @if ($sale->details)
      <div class="py-4">
        {{ __($sale->details) }}
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
