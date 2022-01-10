@extends('auth::app')
@section('title', 'Auth')

@section('content')
  <p class="mt-8 text-center text-sm leading-5 text-gray-500">
    @foreach ($modules as $module)
      @if (!$loop->first) &nbsp;|&nbsp; @endif
      <a href="{{ url($module->name == 'Auth' ? '/' : $module->route ?? '/') }}"
        class="font-bold text-blue-600 hover:text-blue-700 dark:hover:text-gray-300 focus:outline-none focus:underline transition ease-in-out duration-150">
        {{ $module->name == 'Auth' ? 'Home' : $module->name }}
      </a>
    @endforeach
  </p>
@endsection
