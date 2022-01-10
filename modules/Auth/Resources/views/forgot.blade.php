@extends('auth::app')
@section('title', 'Forgot Password')
@section('widthClass', 'w-full max-w-sm')

@section('content')
  <h2 class="mt-2 text-center leading-9 font-bold text-gray-400">
    {{ __('Get instructions to reset password') }}
  </h2>
  @include('auth::flash-message')
  <form class="mt-4" action="{{ route('auth.password.email') }}" method="POST" autocomplete="off">
    @csrf
    <input type="hidden" name="remember" value="true" />
    <div class="rounded shadow-sm">
      <div>
        <input required type="email" name="email" placeholder="{{ __('Email Address') }}"
          class="bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700 placeholder-gray-500 appearance-none rounded-none relative block w-full px-6 py-3 border text-gray-900 dark:text-gray-300 rounded-t-md focus:outline-none focus:shadow-outline-blue focus:border-blue-300 z-0 focus:z-10 sm:text-sm sm:leading-5" />
      </div>
      <button type="submit"
        class="w-full px-6 py-3 font-medium rounded-b text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-700 transition duration-150 ease-in-out sm:text-sm sm:leading-5 z-0 focus:z-20 hover:z-20">
        {{ __('Submit') }}
      </button>
    </div>
    <div class="mt-6"></div>
  </form>
  <p class="mt-8 text-center text-sm leading-5 text-gray-500">
    <a href="{{ route('auth.login') }}"
      class="font-bold text-blue-600 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:underline transition ease-in-out duration-150">
      {{ __('Login') }}
    </a>
    @foreach ($modules as $module) &nbsp;|&nbsp;
      <a href="{{ url($module->name == 'Auth' ? '/' : $module->route ?? '/') }}"
        class="font-bold text-blue-600 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:underline transition ease-in-out duration-150">
        {{ $module->name == 'Auth' ? 'Home' : $module->name }}
      </a>
    @endforeach
  </p>
@endsection
