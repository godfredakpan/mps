<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>MPS - Installer</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link as="style" href="{{ asset('vendor/installer/css/app.24d616b7.css') }}" rel="style">
    <link as="script" href="{{ asset('vendor/installer/js/app.6c7d5ed3.js') }}" rel="preload">
    <link as="script" href="{{ asset('vendor/installer/js/chunk-vendors.2f603125.js') }}" rel="preload">
    <link href="{{ asset('vendor/installer/css/app.24d616b7.css') }}" rel="stylesheet">
    <style>
        body,
        .noscript {
            display: flex;
            min-width: 1000px;
            min-height: 650px;
            align-items: center;
            justify-content: center;
            background: hsl(217, 71%, 53%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }

        .noscript h1 {
            color: #FFF;
            line-height: 2;
            font-weight: 100;
            text-align: center;
        }

    </style>
</head>

<body>
    <noscript>
        <div class="noscript">
            <h1 class="msg">
                We're sorry!<br>
                <small>but installer doesn't work without JavaScript.</small>
                <br>Please enable JavaScript to continue!
            </h1>
        </div>
    </noscript>
    <div id="app">
        <vue-progress-bar></vue-progress-bar>
        <notifications group="default" animation-name="fade" />
    </div>
    <script src="{{ asset('vendor/installer/js/chunk-vendors.2f603125.js') }}"></script>
    <script src="{{ asset('vendor/installer/js/app.6c7d5ed3.js') }}"></script>
</body>

</html>
