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
    </head>
    <body class="font-sans antialiased">
        @php
            $isChatWorkspace = request()->routeIs(
                'audiens.konsultasi-saya',
                'konsultan.obrolan',
                'konsultasi.show'
            );
        @endphp

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
    </body>
</html>
