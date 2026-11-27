<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            @media print {
                body { display: none !important; }
            }
            .anti-screenshot {
                -webkit-user-select: none;
                user-select: none;
                -webkit-touch-callout: none;
            }
            .anti-screenshot img,
            .anti-screenshot a,
            .anti-screenshot p,
            .anti-screenshot span,
            .anti-screenshot div,
            .anti-screenshot h1,
            .anti-screenshot h2,
            .anti-screenshot h3,
            .anti-screenshot label {
                -webkit-user-select: none;
                user-select: none;
                -webkit-touch-callout: none;
            }
            #screenshot-shield {
                display: none;
                position: fixed;
                inset: 0;
                z-index: 99999;
                background: #0f172a;
                color: #fff;
                align-items: center;
                justify-content: center;
                font: 600 16px/1.5 system-ui, sans-serif;
                text-align: center;
                padding: 24px;
            }
            #screenshot-shield.is-active { display: flex; }
        </style>
    </head>
    <body class="font-sans antialiased anti-screenshot">
        @php
            $isChatWorkspace = request()->routeIs(
                'audiens.konsultasi-saya',
                'konsultan.obrolan',
                'konsultasi.show'
            );
        @endphp

        <div id="screenshot-shield" aria-hidden="true">
            <div>
                <div style="font-size:18px; margin-bottom:8px;">Tangkapan layar dibatasi</div>
                <div style="font-size:14px; font-weight:500; opacity:.9;">Konten konsultasi ini bersifat terbatas.</div>
            </div>
        </div>

        <div class="{{ $isChatWorkspace ? 'chat-layout' : 'min-h-screen' }} flex flex-col bg-[#F1F5F9] relative overflow-x-hidden">
            @include('layouts.navigation')

            <div class="relative z-[1] flex flex-col flex-1 min-h-0 {{ $isChatWorkspace ? 'overflow-hidden' : '' }}">
                <!-- Page Heading -->
                @isset($header)
                    <header class="border-b border-slate-200/80 bg-white/95 backdrop-blur-sm shadow-sm">
                        <div class="max-w-7xl mx-auto py-2 px-4 sm:py-3 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1 {{ $isChatWorkspace ? 'chat-main' : '' }}">
                    {{ $slot }}
                </main>
            </div>
        </div>
        @stack('scripts')
        <script>
            (function () {
                const shield = document.getElementById('screenshot-shield');
                if (!shield) return;

                const showShield = () => { shield.classList.add('is-active'); };
                const hideShield = () => { shield.classList.remove('is-active'); };

                document.addEventListener('keyup', function (e) {
                    if (e.key === 'PrintScreen') {
                        showShield();
                        setTimeout(hideShield, 1200);
                    }
                });

                document.addEventListener('keydown', function (e) {
                    const key = (e.key || '').toLowerCase();
                    if (e.key === 'PrintScreen' || ((e.ctrlKey || e.metaKey) && key === 'p')) {
                        e.preventDefault();
                        showShield();
                        setTimeout(hideShield, 1200);
                    }
                    if ((e.ctrlKey || e.metaKey) && e.shiftKey && ['s', '3', '4', '5'].includes(key)) {
                        showShield();
                        setTimeout(hideShield, 1200);
                    }
                });

                document.addEventListener('visibilitychange', function () {
                    if (document.hidden) showShield();
                    else setTimeout(hideShield, 250);
                });

                window.addEventListener('blur', function () {
                    showShield();
                });
                window.addEventListener('focus', function () {
                    setTimeout(hideShield, 250);
                });
            })();
        </script>
    </body>
</html>
