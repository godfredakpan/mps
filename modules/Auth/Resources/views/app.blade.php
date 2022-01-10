<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <link rel="icon" href="/storage/images/icon.png" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=400, initial-scale=1, maximum-scale=1" />
  <title>@yield('title') - {{ config('app.name', 'Auth Module') }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <link href="{{ mix('/css/auth.css') }}" rel="stylesheet" />
  <script>
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia(
        '(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  </script>
  <script defer src="https://unpkg.com/alpinejs@3.2.4/dist/cdn.min.js"></script>
</head>

<body class="noselect bg-gray-50 dark:bg-gray-800">
  <div id="app">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 select-none">
      <div class="@yield('widthClass')">
        <a href="/">
          <h1 class="text-3xl md:text-4xl text-gray-700 dark:text-gray-300 font-thin text-center">{{ config('app.name') }}</h1>
        </a>
        @yield('content')
      </div>
    </div>
  </div>
</body>

</html>
