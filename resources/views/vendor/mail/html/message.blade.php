@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            @php
            $logo = false;
            if (function_exists('mps_config')) { $logo = mps_config('default_logo'); }
            echo $logo
            ? '<img src="'.url('storage/images/logo.png').'" alt="'.config('app.name').'" style="max-width:250px;" />'
            : config('app.name');
            @endphp
        @endcomponent
    @endslot

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
        @endcomponent
    @endslot
@endcomponent
