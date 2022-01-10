@extends('auth::app')
@section('title', 'Login')
@section('widthClass', 'w-full max-w-sm')

@section('content')
  @include('auth::flash-message')
  <h2 class="mt-2 text-center leading-9 font-bold text-gray-500 dark:text-gray-400">
    {{ __('Please sign in to your account') }}
  </h2>
  <form class="mt-4" action="{{ route('auth.login.post') }}" method="POST" autocomplete="off">
    @csrf
    <!-- <input type="hidden" name="remember" value="true" /> -->
    <div class="rounded-md shadow-sm">
      <div>
        <input required type="text" name="username" placeholder="{{ __('Username or Email Address') }}"
          class="bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700 placeholder-gray-500 appearance-none rounded-none relative block w-full px-6 py-3 border text-gray-900 dark:text-gray-300 rounded-t-md focus:outline-none focus:shadow-outline-blue focus:border-blue-300 focus:z-10 sm:text-sm sm:leading-5" />
      </div>
      <div class="relative" style="margin-top:-1px;">
        <input required name="password" type="password" placeholder="{{ __('Password') }}"
          class="bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700 placeholder-gray-500 appearance-none rounded-none relative block w-full px-6 py-3 border text-gray-900 dark:text-gray-300 rounded-b-md focus:outline-none focus:shadow-outline-blue focus:border-blue-300 focus:z-10 sm:text-sm sm:leading-5" />
      </div>
    </div>

    <div class="mt-6 flex items-center justify-between">
      <div class="flex items-center">
        <input name="remember" type="checkbox" id="remember_me"
          class="form-checkbox h-4 w-4 text-gray-700 dark:text-gray-300 transition duration-150 ease-in-out" />
        <label for="remember_me" class="ml-2 block text-sm leading-5 text-gray-700 dark:text-gray-300">
          {{ __('Remember me') }}
        </label>
      </div>

      <div class="text-sm leading-5">
        <a href="{{ route('auth.password.forgot') }}"
          class="font-medium text-blue-600 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:underline transition ease-in-out duration-150">
          {{ __('Forgot your password?') }}
        </a>
      </div>
    </div>

    <div class="mt-6">
      <button type="submit"
        class="group relative w-full flex justify-center px-6 py-3 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-700 transition duration-150 ease-in-out">
        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
          <svg fill="currentColor" viewBox="0 0 20 20"
            class="h-5 w-5 text-blue-500 group-hover:text-blue-400 transition ease-in-out duration-150">
            <path fill-rule="evenodd" clip-rule="evenodd"
              d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" />
          </svg>
        </span>
        {{ __('Sign in') }}
      </button>
    </div>
  </form>
  <p class="mt-8 text-center text-sm leading-5 text-gray-500">
    @foreach ($modules as $module)
      @if (!$loop->first) &nbsp;|&nbsp; @endif
      <a href="{{ url($module->name == 'Auth' ? '/' : $module->route ?? '/') }}"
        class="font-bold text-blue-600 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:underline transition ease-in-out duration-150">
        {{ $module->name == 'Auth' ? 'Home' : $module->name }}
      </a>
    @endforeach
  </p>
@endsection
