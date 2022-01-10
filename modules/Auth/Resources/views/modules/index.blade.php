@extends('auth::app')
@section('title', 'Manage Modules')
@section('widthClass', 'w-full max-w-2xl')

@section('content')
  <div class="max-w-md mx-auto">
    <div x-data="{sm: true}" @width-message.window="sm = false; $el.parentNode.classList.remove('max-w-md');" class="mx-auto"
      :class="sm ? 'max-w-md' : 'max-w-2xl'">
      <div class="flex items-center justify-center mt-4 font-mono text-sm text-gray-600 dark:text-gray-400">
        <span class="animate-pulse px-4 py-2 rounded border dark:border-gray-700">Version: {{ $version }}</span>
      </div>
      <div x-data="{show: true, init() { setTimeout(() => (this.show = false), 2000) }}">
        <div x-show="show" x-transition>
          @include('auth::flash-message')
        </div>
      </div>
      <div class="bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 mt-6 w-full rounded-lg shadow-lg">
        <h1 class="text-lg px-6 py-3 border-b dark:border-gray-800">Manage Modules</h1>
        <div class="divide-y dark:divide-gray-800">
          @foreach ($mods as $module)
            <div
              x-data="{ message: '', shop: {{ $module['isEnabled'] ? 'true' : 'false' }}, exitCode: 0, disabled: false, installed: {{ $module['installed'] ? 'true' : 'false' }}, current{{ $module['name'] }}: {{ $module['isEnabled'] ? 'true' : 'false' }}, shopKey: null, submitData() { this.message = ''; this.disabled = true; fetch('/modules/install', { method: 'POST', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': '{{ csrf_token() }}' }, body: JSON.stringify({ name: '{{ $module['name'] }}', key: this.shopKey }) }).then(response => response.json()).then(res => { console.log(res); this.exitCode = res.exitCode; this.message = res.output || 'Process exited unexpectedly!'; this.$dispatch('width-message'); if (res.exitCode) { this.disabled = false; } else { setTimeout(function() { window.location.reload(); }, 10000); } }).catch(err => { console.log(err); this.exitCode = 1; this.message = 'Ooops! Something went wrong, please try again.', this.$dispatch('width-message'); this.disabled = false; }); } }">
              <div x-show="!installed" x-transition style="display:none">
                <form action="/modules/install" method="POST" @submit.prevent="submitData">
                  @csrf
                  <div class="p-4">
                    <input type="hidden" name="name" value="{{ $module['name'] }}" />
                    <input type="checkbox" name="shop" x-model="current{{ $module['name'] }}" class="hidden"
                      id="current{{ $module['name'] }}" />
                    <div class="flex items-center">
                      <button type="button" @click="current{{ $module['name'] }} = !current{{ $module['name'] }}"
                        :class="current{{ $module['name'] }} ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700'"
                        class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        role="switch">
                        <span aria-hidden="true" :class="current{{ $module['name'] }} ? 'translate-x-5' : 'translate-x-0'"
                          class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                      </button>
                      <div class="ml-3 flex-1 flex justify-between items-center">
                        <div class="flex-1">
                          <span @click="current{{ $module['name'] }} = !current{{ $module['name'] }}"
                            class="text-sm font-medium">{{ $module['description'] }}</span>
                        </div>
                        <a x-transition x-show="!shop" target="_blank" href="https://tecdiary.net"
                          class="text-sm text-gray-500 hover:text-indigo-600">Buy</a>
                      </div>
                    </div>
                    <div x-show="!shop && current{{ $module['name'] }}" x-transition class="mt-4" style="display:none">
                      <label for="key{{ $module['name'] }}" class="block text-sm font-medium">License Key</label>
                      <div class="mt-1 relative rounded-md shadow-sm">
                        <input type="text" x-model="shopKey" name="key" id="key{{ $module['name'] }}"
                          :class="shopKey && shopKey.length != 36 ? 'pr-10 border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500' : 'text-gray-700 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-800'"
                          class="block w-full px-4 py-2 border focus:outline-none sm:text-sm rounded-md" placeholder="Type Your License Key">
                        <div x-transition x-show="shopKey && shopKey.length != 36"
                          class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                          <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                              d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                              clip-rule="evenodd" />
                          </svg>
                        </div>
                      </div>
                      <p x-transition x-show="shopKey && shopKey.length != 36" class="mt-2 text-sm text-red-600" id="key-error">
                        License key is invalid.
                      </p>
                      <button x-transition x-show="shopKey && shopKey.length == 36" type="submit" :disabled="disabled"
                        :class="disabled ? 'opacity-50 bg-opacity-50' : ''"
                        class="w-full mt-4 flex items-center justify-between px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="flex-1 text-center">
                          Install
                        </div>
                        <svg x-show="disabled" x-transition class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                          xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                          </path>
                        </svg>
                      </button>
                      <div x-show="!message && disabled" x-transition class="mt-4">
                        <p class="mt-1 ml-2 text-xs text-blue-500">
                          {{ __('Installing module could take 5 - 20 minutes, please wait...') }}
                        </p>
                      </div>
                      <div x-show="!!message" x-transition class="mt-4">
                        <p x-html="message.split('\n').slice(-5).join('\n')"
                          class="border dark:border-gray-800 rounded-md text-xs p-4 whitespace-pre-line font-mono"
                          :class="exitCode ? 'text-red-500' : ''"></p>
                        <p class="mt-1 ml-2 text-xs text-blue-500">
                          {{ __choice('Please check latest log file in :path folder.', ['path' => storage_path('logs')]) }}
                        </p>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
              <div x-show="installed" x-transition class="p-4" style="display:none">
                <input type="hidden" name="name" value="{{ $module['name'] }}" />
                <input type="checkbox" name="shop" x-model="current{{ $module['name'] }}" class="hidden"
                  id="current{{ $module['name'] }}" />
                <div class="flex items-center">
                  <button type="button" @click="current{{ $module['name'] }} = !current{{ $module['name'] }}"
                    :class="current{{ $module['name'] }} ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700'"
                    class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    role="switch">
                    <span aria-hidden="true" :class="current{{ $module['name'] }} ? 'translate-x-5' : 'translate-x-0'"
                      class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                  </button>
                  <div class="ml-3 flex-1 flex justify-between items-center">
                    <div class="flex-1">
                      <span @click="current{{ $module['name'] }} = !current{{ $module['name'] }}"
                        class="text-sm font-medium">{{ $module['description'] }}</span>
                    </div>
                  </div>
                </div>
                <div x-show="!shop && current{{ $module['name'] }}" x-transition class="mt-4" style="display:none">
                  <form action="/modules/enable" method="POST">
                    @csrf
                    <input type="hidden" name="name" value="{{ $module['name'] }}" />
                    <button type="submit"
                      class="w-full mt-4 flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                      Enable Module
                    </button>
                  </form>
                </div>
              </div>
              <div x-show="shop && !current{{ $module['name'] }}" x-transition class="px-4 pb-4" style="display:none">
                <form action="/modules/disable" method="POST">
                  @csrf
                  <input type="hidden" name="name" value="{{ $module['name'] }}" />
                  <button type="submit"
                    class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    Disable Module
                  </button>
                </form>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
@endsection
