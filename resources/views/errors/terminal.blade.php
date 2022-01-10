<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            min-width: 350px;
            min-height: 300px;
            background: #363e4f;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .background {
            margin: 0px;
            width: 100%;
            height: 100%;
            position: absolute;
        }

        .foreground {
            width: 100%;
            height: 100%;
            z-index: 999;
            overflow: visible;
            position: absolute;
            pointer-events: none;
        }

        .terminal-window {
            width: 90%;
            height: 100%;
            margin: 16px auto;
            text-align: left;
            min-width: 300px;
            max-width: 600px;
            min-height: 100px;
            max-height: 200px;
            position: relative;
            border-radius: 8px;
            /*border: 1px solid #363e4f;*/
            /*box-shadow: 0px 0px 2px 1px rgba(255,255,255,0.05);*/
        }

        .terminal-window header {
            height: 1.875rem;
            background: #E0E8F0;
            padding-left: .625rem;
            border-radius: 8px 8px 0 0;
        }

        .terminal-window header .button {
            width: .75rem;
            height: .75rem;
            border-radius: 8px;
            display: inline-block;
            margin: .625rem .25rem 0 0;
        }

        .terminal-window header .button.green {
            background: #3BB662;
        }

        .terminal-window header .button.yellow {
            background: #E5C30F;
        }

        .terminal-window header .button.red {
            background: #E75448;
        }

        .terminal-window section.terminal {
            bottom: 0;
            width: 100%;
            top: 1.875rem;
            color: #ffff00;
            overflow: auto;
            font-size: 11pt;
            padding: .625rem;
            position: absolute;
            background: #30353A;
            box-sizing: border-box;
            border-radius: 0 0 8px 8px;
            font-family: Menlo, Monaco, "Consolas", "Courier New", "Courier";
        }

        .history {
            color: #ffff00;
        }

        .prompt {
            color: #ffffff;
        }

        .terminal-row {
            display: flex;
            line-height: 1.5;
            align-items: flex-start;
        }

        .terminal-data {
            display: none;
        }

        .terminal-window .gray {
            color: gray;
        }

        .terminal-window .green {
            color: green;
        }

        .terminal-window section.terminal .typed-cursor {
            opacity: 0;
            /* animation: blink 0.7s infinite; */
        }

        .hidden {
            display: none !important;
        }

        #snowflake {
            top: -50px;
            color: #fff;
            font-size: 25px;
            position: absolute;
            animation: spin-clockwise 6s linear infinite;
        }

        @keyframes blink {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }

        @keyframes spin-clockwise {
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes spin-counterclockwise {
            100% {
                transform: rotate(-360deg);
            }
        }
    </style>
</head>

<body class="background">
    <div class="full-height flex-center">
        <div class="terminal-window">
            <header>
                <div class="button green"></div>
                <div class="button yellow"></div>
                <div class="button red"></div>
            </header>
            <section class="terminal">
                <div class="history"></div>
                <div class="terminal-row">
                    $&nbsp;<span class="prompt"></span>
                </div>
                <span class="typed-cursor"></span>
            </section>
        </div>
    </div>
    <span class="hidden" id="snowflake">&#10052;</span>

    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="//www.mattboldt.com/demos/typed-js/js/typed.custom.js"></script>
    <script>
        $(function() {
        var data = [
        { action: 'type', strings: ["Error! "], postDelay: 500 },
        { action: 'type', strings: ["@yield('code') @yield('title')"], output: '<br>', postDelay: 500 },
        { action: 'type', strings: ["@yield('message')"], postDelay: 2000 }
        ];
        runScripts(data, 0);

        setInterval(function() {
            var documentHeight = $(document).height();
            var startPositionLeft = Math.random() * $(document).width() - 100;
            var startOpacity = 0.5 + Math.random();
            var endPositionTop = documentHeight - 50;
            var endPositionLeft = startPositionLeft - 100 + Math.random() * 200;
            var durationFall = documentHeight * 10 + Math.random() * 4000;
            var animationFlake = endPositionLeft > startPositionLeft ? 'clockwise' : 'counterclockwise';
            var sizeFlake = 5 + Math.random() * 10;
            $('#snowflake.hidden').clone().attr('class', null).attr('id', 'snowflake')
            .appendTo('.background, .foreground').css({
                left: startPositionLeft, opacity: startOpacity, 'font-size': sizeFlake,
                'animation': 'spin-' + animationFlake + ' 6s linear infinite',
            }).animate({ top: endPositionTop, left: endPositionLeft, opacity: 0.2},
            durationFall, 'linear', function() { $(this).remove(); });
        }, 250);
        if (@yield('code') == 503) {
            setTimeout(function() {
                console.log('Reloading the page');
                window.location.reload(true);
            }, 60000);
        }
    });

    function runScripts(data, pos) {
        var prompt = $('.prompt'),
        script = data[pos];
        if (script.clear === true) {
            $('.history').html('');
        }
        switch(script.action) {
            case 'type':
            prompt.removeData();
            $('.typed-cursor').text('');
            prompt.typed({
                typeSpeed: 30,
                strings: script.strings,
                callback: function() {
                    var history = $('.history').html();
                    history = history ? [history] : [];
                    history.push('$ ' + prompt.text());
                    if (script.output) {
                        prompt.html('');
                        history.push(script.output);
                        $('.history').html(history.join('<br>'));
                    }
                    $('section.terminal').scrollTop($('section.terminal').height());
                    pos++;
                    if (pos < data.length) {
                        setTimeout(function() {
                            runScripts(data, pos);
                        }, script.postDelay || 1000);
                    }
                }
            });
            break;
            case 'view':
            break;
        }
    }
    </script>
</body>

</html>
