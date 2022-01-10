<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <link rel="icon" href="/storage/images/icon.png" />
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>{{ config('app.name', 'Modern Point of Sale Solution') }}</title>
    <link rel="manifest" href="/manifest.json" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @if (demo())
    <meta name="description"
        content="MPS (Modern Point of Sale Solution) is all in one retail POS solution that offers a lot of features such as Sales, Promotions, Recurring Sales, Purchases, Stock, CRM, Accounting and Payments (Offline, Online & Credit/Debit Cards) & many more feature." />
    <meta name="keywords"
        content="Tecdiary, POS, Point of Sale, Business Manager, CRM, HRM, Accounting, Purchases, Incomes & Expenses" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://mps.tecdiary.net/admin" />
    <meta property="og:title" content="Modern Point of Sale Solution" />
    <meta property="og:image" content="{{ asset('/images/pos.jpg') }}" />
    <meta property="og:image:alt" content="{{ asset('/images/pos.png') }}" />
    <meta property="og:description"
        content="MPS (Modern Point of Sale Solution) is all in one retail POS solution that offers a lot of features such as Sales, Promotions, Recurring Sales, Purchases, Stock, CRM, Accounting and Payments (Offline, Online & Credit/Debit Cards) & many more feature." />
    @endif
    <style>
        .preloaderapp {
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 99999;
            background: #fff;
            border-radius: 4px;
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .spin {
            color: #2d8cf0;
            text-align: center;
            vertical-align: middle;
        }

        @if (mps_config('loader')=='circle') .circle {
            width: 30px;
            height: 30px;
            position: relative;
            margin: 0 auto;
        }

        .spin-large .circle {
            width: 50px;
            height: 50px;
        }

        .rotating-circle {
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            margin: auto;
            position: absolute;
            transform-origin: center center;
            animation: rotate 2s linear infinite;
        }

        .rotating-circle-path {
            stroke-dashoffset: 0;
            stroke-linecap: round;
            stroke-dasharray: 1, 200;
            animation: dash 1.5s ease-in-out infinite, color 6s ease-in-out infinite;
        }

        @keyframes rotate {
            to {
                transform: rotate(1turn);
            }
        }

        @keyframes dash {
            0% {
                stroke-dasharray: 1, 200;
                stroke-dashoffset: 0;
            }

            50% {
                stroke-dasharray: 89, 200;
                stroke-dashoffset: -35;
            }

            to {
                stroke-dasharray: 89, 200;
                stroke-dashoffset: -124;
            }
        }

        @keyframes color {

            0%,
            to {
                stroke: #d62d20;
            }

            40% {
                stroke: #0057e7;
            }

            66% {
                stroke: #008744;
            }

            80%,
            90% {
                stroke: #ffa700;
            }
        }

        @else .spin-dot {
            width: 20px;
            height: 20px;
            display: block;
            position: relative;
            border-radius: 50%;
            background-color: #2d8cf0;
            animation: spin-bounce 1s 0s ease-in-out infinite;
        }

        .spin-large .spin-dot {
            width: 32px;
            height: 32px;
        }

        @keyframes spin-bounce {
            0% {
                transform: scale(0);
            }

            to {
                opacity: 0;
                transform: scale(1);
            }
        }

        @endif;
    </style>
    <link href="{{ mix('css/mps_ui.css') }}" rel="stylesheet" />
    <link href="{{ mix('css/mps.css') }}" rel="stylesheet" />
</head>

<body class="noselect main-scrollbar">
    <div id="app">
        <div class="preloaderapp" ref="preloaderapp">
            <div>
                <div class="spin spin-large spin-default">
                    <div class="spin-main">
                        @if (mps_config('loader') == 'circle')
                        <div class="spin-text">
                            <div class="circle">
                                <svg viewBox="25 25 50 50" class="rotating-circle">
                                    <circle cx="50" cy="50" r="20" fill="none" stroke-width="5" stroke-miterlimit="10"
                                        class="rotating-circle-path"></circle>
                                </svg>
                            </div>
                        </div>
                        @else
                        <span class="spin-dot"></span>
                        <div class="spin-text"></div>
                        @endif
                    </div>
                </div>
                <div class="error"
                    style="text-align:center;margin-top:2rem;font-size:1.2rem;color:#3F4448;display:none">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        x="0px" y="0px" viewBox="0 0 451.74 451.74" style="width:100px;height:100px;">
                        <path style="fill:#E24C4B;"
                            d="M446.324,367.381L262.857,41.692c-15.644-28.444-58.311-28.444-73.956,0L5.435,367.381 c-15.644,28.444,4.267,64,36.978,64h365.511C442.057,429.959,461.968,395.825,446.324,367.381z" />
                        <path style="fill:#FFFFFF;"
                            d="M225.879,63.025l183.467,325.689H42.413L225.879,63.025L225.879,63.025z" />
                        <g>
                            <path style="fill:#3F4448;"
                                d="M196.013,212.359l11.378,75.378c1.422,8.533,8.533,15.644,18.489,15.644l0,0 c8.533,0,17.067-7.111,18.489-15.644l11.378-75.378c2.844-18.489-11.378-34.133-29.867-34.133l0,0 C207.39,178.225,194.59,193.87,196.013,212.359z" />
                            <circle style="fill:#3F4448;" cx="225.879" cy="336.092" r="17.067" />
                        </g>
                    </svg><br />
                    <span>Network Error<br /></span>
                    <span style="color:#E24C4B">Unable to load application.</span>
                </div>
            </div>
        </div>
        @yield('content')
    </div>
    <script>
        var data = {!! json_encode(mps_data()) !!};
    </script>
    <script src="{{ mix('js/mps.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>
</body>

</html>
