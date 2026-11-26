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

        <div class="{{ $isChatWorkspace ? 'chat-layout' : 'min-h-screen' }} flex flex-col bg-slate-50">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-slate-200 bg-white shadow-sm">
                    <div class="max-w-7xl mx-auto py-3 px-4 sm:py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1 {{ $isChatWorkspace ? 'chat-main' : '' }}">
                {{ $slot }}
            </main>
        </div>
        @stack('scripts')
    </body>
</html>
