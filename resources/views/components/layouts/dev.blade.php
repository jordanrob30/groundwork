<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Dev Tools' }} - {{ config('app.name', 'Discovery Engine') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <!-- Dev Tools Header -->
            <nav class="bg-amber-500 border-b border-amber-600">
                <div style="max-width: 1400px; margin-left: auto; margin-right: auto; padding: 0 1rem;">
                    <div class="flex justify-between h-12">
                        <div class="flex items-center">
                            <span class="text-white font-semibold text-sm">
                                <svg class="w-4 h-4 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                DEV TOOLS
                            </span>
                            <span class="ml-4 text-amber-100 text-sm">{{ $title ?? 'Developer Tools' }}</span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('dev.mail') }}" class="text-white text-sm hover:text-amber-100 {{ request()->routeIs('dev.mail') ? 'underline' : '' }}">
                                Mail
                            </a>
                            @auth
                                <a href="{{ route('dashboard') }}" class="text-white text-sm hover:text-amber-100">
                                    &larr; Back to App
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-white text-sm hover:text-amber-100">
                                    Login
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main>
                <div style="max-width: 1400px; margin-left: auto; margin-right: auto; padding-left: 1rem; padding-right: 1rem;">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
