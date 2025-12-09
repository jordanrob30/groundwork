<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Groundwork') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans bg-bg-base text-text-primary">
        <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-brand selection:text-text-inverse">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                    <div class="flex lg:justify-center lg:col-start-2">
                        <x-application-logo class="scale-125" />
                    </div>
                    @if (Route::has('login'))
                        <livewire:welcome.navigation />
                    @endif
                </header>

                <main class="mt-6">
                    <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
                        <a
                            href="{{ route('login') }}"
                            class="flex flex-col items-start gap-6 overflow-hidden rounded-lg bg-bg-elevated p-6 shadow ring-1 ring-border-default transition duration-300 hover:ring-brand focus:outline-none focus-visible:ring-brand md:row-span-3 lg:p-10 lg:pb-10"
                        >
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-brand/10 sm:size-16">
                                <svg class="size-5 sm:size-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>

                            <div class="pt-3 sm:pt-5 lg:pt-0">
                                <h2 class="text-xl font-semibold text-text-primary">Cold Email Outreach</h2>

                                <p class="mt-4 text-sm/relaxed text-text-secondary">
                                    AI-powered cold email campaigns with intelligent response analysis. Discover interested leads automatically and scale your outreach effectively.
                                </p>
                            </div>

                            <svg class="size-6 shrink-0 stroke-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"/></svg>
                        </a>

                        <a
                            href="{{ route('login') }}"
                            class="flex items-start gap-4 rounded-lg bg-bg-elevated p-6 shadow ring-1 ring-border-default transition duration-300 hover:ring-brand focus:outline-none focus-visible:ring-brand lg:pb-10"
                        >
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-brand/10 sm:size-16">
                                <svg class="size-5 sm:size-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>

                            <div class="pt-3 sm:pt-5">
                                <h2 class="text-xl font-semibold text-text-primary">AI Response Analysis</h2>

                                <p class="mt-4 text-sm/relaxed text-text-secondary">
                                    Automatically classify responses by interest level. Hot, warm, cold, and negative leads identified instantly by Claude AI.
                                </p>
                            </div>

                            <svg class="size-6 shrink-0 self-center stroke-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"/></svg>
                        </a>

                        <a
                            href="{{ route('login') }}"
                            class="flex items-start gap-4 rounded-lg bg-bg-elevated p-6 shadow ring-1 ring-border-default transition duration-300 hover:ring-brand focus:outline-none focus-visible:ring-brand lg:pb-10"
                        >
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-brand/10 sm:size-16">
                                <svg class="size-5 sm:size-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>

                            <div class="pt-3 sm:pt-5">
                                <h2 class="text-xl font-semibold text-text-primary">Campaign Insights</h2>

                                <p class="mt-4 text-sm/relaxed text-text-secondary">
                                    Track response rates, lead quality, and campaign performance. Make data-driven decisions to improve your outreach.
                                </p>
                            </div>

                            <svg class="size-6 shrink-0 self-center stroke-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"/></svg>
                        </a>

                        <div class="flex items-start gap-4 rounded-lg bg-bg-elevated p-6 shadow ring-1 ring-border-default lg:pb-10">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-brand/10 sm:size-16">
                                <svg class="size-5 sm:size-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>

                            <div class="pt-3 sm:pt-5">
                                <h2 class="text-xl font-semibold text-text-primary">Mailbox Management</h2>

                                <p class="mt-4 text-sm/relaxed text-text-secondary">
                                    Manage multiple sending accounts with intelligent warmup and daily limits. Keep your sender reputation healthy.
                                </p>
                            </div>
                        </div>
                    </div>
                </main>

                <footer class="py-16 text-center text-sm text-text-muted">
                    Groundwork &copy; {{ date('Y') }}
                </footer>
            </div>
        </div>
    </body>
</html>
