<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Groundwork') }} - AI-Powered Cold Outreach</title>
        <meta name="description" content="Discover interested leads automatically with AI-powered cold email campaigns. Claude AI analyzes responses to help you focus on leads that matter.">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans bg-bg-base text-text-primary">
        {{-- ============================================================
             DATA DEFINITIONS
             ============================================================ --}}
        @php
            // Hero Content
            $hero = [
                'headline' => 'AI-Powered Cold Outreach That Works',
                'subheadline' => 'Discover interested leads automatically. Claude AI analyzes every response so you can focus on prospects that matter.',
                'primary_cta_text' => 'Start Free Trial',
                'primary_cta_url' => route('register'),
                'secondary_cta_text' => 'View Pricing',
                'secondary_cta_url' => '#pricing',
            ];

            // Navigation Items
            $navItems = [
                ['label' => 'Features', 'href' => '#features'],
                ['label' => 'Pricing', 'href' => '#pricing'],
            ];

            // Features (from data-model.md)
            $features = [
                [
                    'id' => 'cold-email',
                    'title' => 'Cold Email Outreach',
                    'description' => 'AI-powered campaigns with intelligent scheduling and personalization. Reach the right prospects at the right time.',
                    'icon' => 'mail',
                    'accent_color' => 'brand',
                ],
                [
                    'id' => 'ai-analysis',
                    'title' => 'AI Response Analysis',
                    'description' => 'Claude AI automatically classifies responses as hot, warm, cold, or negative. Focus on leads that matter.',
                    'icon' => 'sparkles',
                    'accent_color' => 'brand',
                ],
                [
                    'id' => 'campaign-insights',
                    'title' => 'Campaign Insights',
                    'description' => 'Track response rates, lead quality, and campaign performance. Make data-driven decisions.',
                    'icon' => 'chart-bar',
                    'accent_color' => 'accent',
                ],
                [
                    'id' => 'mailbox-management',
                    'title' => 'Mailbox Management',
                    'description' => 'Manage multiple sending accounts with intelligent warmup and daily limits. Protect your sender reputation.',
                    'icon' => 'inbox',
                    'accent_color' => 'brand',
                ],
            ];

            // Pricing Tiers (from data-model.md)
            $pricingTiers = [
                [
                    'id' => 'starter',
                    'name' => 'Starter',
                    'price_monthly' => 49,
                    'price_annual' => 39,
                    'tagline' => 'Perfect for getting started with cold outreach',
                    'features' => [
                        '5,000 emails/month',
                        '3 email accounts',
                        '2,000 active leads',
                        '100 AI responses/month',
                        'Email warmup included',
                        'Email & chat support',
                    ],
                    'cta_text' => 'Start Free Trial',
                    'cta_url' => route('register') . '?plan=starter',
                    'is_highlighted' => false,
                    'badge' => null,
                ],
                [
                    'id' => 'professional',
                    'name' => 'Professional',
                    'price_monthly' => 99,
                    'price_annual' => 79,
                    'tagline' => 'For teams serious about scaling outreach',
                    'features' => [
                        '50,000 emails/month',
                        '10 email accounts',
                        '10,000 active leads',
                        'Unlimited AI response analysis',
                        'Inbox rotation & deliverability',
                        'A/B testing',
                        'Advanced analytics',
                        'API access',
                        'Priority support',
                    ],
                    'cta_text' => 'Start Free Trial',
                    'cta_url' => route('register') . '?plan=professional',
                    'is_highlighted' => true,
                    'badge' => 'Most Popular',
                ],
                [
                    'id' => 'enterprise',
                    'name' => 'Enterprise',
                    'price_monthly' => 249,
                    'price_annual' => 199,
                    'tagline' => 'For agencies and high-volume teams',
                    'features' => [
                        'Unlimited emails/month',
                        'Unlimited email accounts',
                        '100,000+ active leads',
                        'Custom AI response training',
                        'Multi-client management',
                        'White-labeling',
                        'Salesforce & HubSpot integrations',
                        'Dedicated account manager',
                        'SLA guarantee',
                    ],
                    'cta_text' => 'Contact Sales',
                    'cta_url' => '/contact?plan=enterprise',
                    'is_highlighted' => false,
                    'badge' => null,
                ],
            ];
        @endphp

        {{-- ============================================================
             NAVIGATION
             ============================================================ --}}
        <nav class="fixed top-0 left-0 right-0 z-50 landing-nav bg-bg-base/80 border-b border-border-default">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 md:h-20">
                    {{-- Logo --}}
                    <div class="flex-shrink-0">
                        <a href="/" class="flex items-center" aria-label="Groundwork Home">
                            <x-application-logo class="h-8 w-auto" />
                        </a>
                    </div>

                    {{-- Desktop Navigation --}}
                    <div class="hidden md:flex items-center space-x-8">
                        @foreach($navItems as $item)
                            <a href="{{ $item['href'] }}" class="landing-nav-link text-text-secondary hover:text-brand transition-colors">
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>

                    {{-- Auth Navigation --}}
                    <div class="flex items-center space-x-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ route('dashboard') }}" class="text-text-secondary hover:text-brand transition-colors">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-text-secondary hover:text-brand transition-colors">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="cta-button inline-flex items-center px-4 py-2 bg-brand text-text-inverse font-medium rounded-lg hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-bg-base">
                                        Get Started
                                    </a>
                                @endif
                            @endauth
                        @endif

                        {{-- Mobile Menu Button --}}
                        <button
                            type="button"
                            class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-text-secondary hover:text-text-primary hover:bg-bg-elevated focus:outline-none focus:ring-2 focus:ring-brand"
                            x-data="{ open: false }"
                            @click="open = !open"
                            aria-label="Toggle navigation menu"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mobile Navigation Menu --}}
            <div class="md:hidden border-t border-border-default" x-data="{ open: false }" x-show="open" x-cloak>
                <div class="px-4 py-3 space-y-2">
                    @foreach($navItems as $item)
                        <a href="{{ $item['href'] }}" class="block px-3 py-2 text-text-secondary hover:text-brand hover:bg-bg-elevated rounded-md">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </nav>

        {{-- ============================================================
             HERO SECTION
             ============================================================ --}}
        <section id="hero" class="landing-section relative min-h-screen flex items-center justify-center pt-20 overflow-hidden">
            {{-- p5.js Canvas Container --}}
            <div id="hero-canvas" aria-hidden="true"></div>

            {{-- Hero Content --}}
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-32 text-center">
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold tracking-tight">
                    <span class="block text-text-primary">{{ $hero['headline'] }}</span>
                </h1>

                <p class="mt-6 md:mt-8 max-w-2xl mx-auto text-lg sm:text-xl md:text-2xl text-text-secondary">
                    {{ $hero['subheadline'] }}
                </p>

                <div class="mt-8 md:mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a
                        href="{{ $hero['primary_cta_url'] }}"
                        class="cta-button w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-lg font-semibold bg-brand text-text-inverse rounded-lg hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-bg-base"
                    >
                        {{ $hero['primary_cta_text'] }}
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>

                    <a
                        href="{{ $hero['secondary_cta_url'] }}"
                        class="cta-button w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-text-primary bg-bg-elevated border border-border-default rounded-lg hover:border-brand focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-bg-base"
                    >
                        {{ $hero['secondary_cta_text'] }}
                    </a>
                </div>
            </div>

            {{-- Scroll Indicator --}}
            <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce" aria-hidden="true">
                <svg class="w-6 h-6 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </div>
        </section>

        {{-- ============================================================
             FEATURES SECTION
             ============================================================ --}}
        <section id="features" class="landing-section relative py-20 md:py-32 bg-bg-elevated overflow-hidden">
            {{-- p5.js Canvas Container --}}
            <div id="features-canvas" aria-hidden="true"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{-- Section Header --}}
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-text-primary">
                        Everything you need to scale outreach
                    </h2>
                    <p class="mt-4 text-lg text-text-secondary">
                        Powerful tools to send smarter campaigns, analyze responses with AI, and close more deals.
                    </p>
                </div>

                {{-- Features Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                    @foreach($features as $index => $feature)
                        <div class="feature-card group p-6 lg:p-8 rounded-2xl bg-bg-base border border-border-default">
                            {{-- Icon --}}
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-{{ $feature['accent_color'] }}/10 mb-6">
                                @if($feature['icon'] === 'mail')
                                    <svg class="w-6 h-6 text-{{ $feature['accent_color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                @elseif($feature['icon'] === 'sparkles')
                                    <svg class="w-6 h-6 text-{{ $feature['accent_color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                @elseif($feature['icon'] === 'chart-bar')
                                    <svg class="w-6 h-6 text-{{ $feature['accent_color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                @elseif($feature['icon'] === 'inbox')
                                    <svg class="w-6 h-6 text-{{ $feature['accent_color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                @endif
                            </div>

                            {{-- Content --}}
                            <h3 class="text-xl font-semibold text-text-primary mb-3">
                                {{ $feature['title'] }}
                            </h3>
                            <p class="text-text-secondary">
                                {{ $feature['description'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============================================================
             PRICING SECTION
             ============================================================ --}}
        <section id="pricing" class="landing-section relative py-20 md:py-32 overflow-hidden" x-data="{ annual: true }">
            {{-- p5.js Canvas Container --}}
            <div id="pricing-canvas" aria-hidden="true"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{-- Section Header --}}
                <div class="text-center max-w-3xl mx-auto mb-12">
                    <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-text-primary">
                        Simple, transparent pricing
                    </h2>
                    <p class="mt-4 text-lg text-text-secondary">
                        Start free, upgrade when you're ready. No hidden fees.
                    </p>

                    {{-- Annual/Monthly Toggle --}}
                    <div class="mt-8 flex items-center justify-center gap-4">
                        <span class="text-sm" :class="annual ? 'text-text-secondary' : 'text-text-primary font-medium'">Monthly</span>
                        <button
                            type="button"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-bg-base"
                            :class="annual ? 'bg-brand' : 'bg-border-default'"
                            @click="annual = !annual"
                            role="switch"
                            :aria-checked="annual"
                            aria-label="Toggle annual billing"
                        >
                            <span
                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                :class="annual ? 'translate-x-6' : 'translate-x-1'"
                            ></span>
                        </button>
                        <span class="text-sm" :class="annual ? 'text-text-primary font-medium' : 'text-text-secondary'">
                            Annual <span class="text-brand">(Save 20%)</span>
                        </span>
                    </div>
                </div>

                {{-- Pricing Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                    @foreach($pricingTiers as $tier)
                        <div class="pricing-card relative flex flex-col p-6 lg:p-8 rounded-2xl {{ $tier['is_highlighted'] ? 'pricing-card-highlighted bg-bg-elevated border-2 border-brand' : 'bg-bg-elevated border border-border-default' }}">
                            {{-- Badge --}}
                            @if($tier['badge'])
                                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand text-text-inverse">
                                        {{ $tier['badge'] }}
                                    </span>
                                </div>
                            @endif

                            {{-- Tier Name --}}
                            <h3 class="text-xl font-semibold text-text-primary">{{ $tier['name'] }}</h3>
                            <p class="mt-2 text-sm text-text-secondary">{{ $tier['tagline'] }}</p>

                            {{-- Price --}}
                            <div class="mt-6 flex items-baseline">
                                <span class="text-4xl font-bold text-text-primary" x-text="annual ? '${{ $tier['price_annual'] }}' : '${{ $tier['price_monthly'] }}'"></span>
                                <span class="ml-1 text-text-secondary">/month</span>
                            </div>
                            <p class="mt-1 text-xs text-text-muted" x-show="annual">Billed annually</p>
                            <p class="mt-1 text-xs text-text-muted" x-show="!annual">Billed monthly</p>

                            {{-- CTA Button --}}
                            <a
                                href="{{ $tier['cta_url'] }}"
                                class="cta-button mt-6 w-full inline-flex items-center justify-center px-4 py-3 font-semibold rounded-lg transition-all {{ $tier['is_highlighted'] ? 'bg-brand text-text-inverse hover:bg-brand-hover' : 'bg-bg-base text-text-primary border border-border-default hover:border-brand' }}"
                            >
                                {{ $tier['cta_text'] }}
                            </a>

                            {{-- Features List --}}
                            <ul class="mt-8 space-y-3 flex-grow" role="list">
                                @foreach($tier['features'] as $feature)
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-brand flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="text-sm text-text-secondary">{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============================================================
             FOOTER
             ============================================================ --}}
        <footer class="py-12 border-t border-border-default">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <x-application-logo class="h-6 w-auto" />
                        <span class="text-sm text-text-muted">
                            &copy; {{ date('Y') }} Groundwork. All rights reserved.
                        </span>
                    </div>

                    <div class="flex items-center gap-6 text-sm text-text-muted">
                        <a href="#features" class="hover:text-brand transition-colors">Features</a>
                        <a href="#pricing" class="hover:text-brand transition-colors">Pricing</a>
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="hover:text-brand transition-colors">Log in</a>
                        @endif
                    </div>
                </div>
            </div>
        </footer>

        {{-- ============================================================
             NOSCRIPT FALLBACK
             ============================================================ --}}
        <noscript>
            <style>
                #hero-canvas,
                #features-canvas,
                #pricing-canvas { display: none; }
                [x-cloak] { display: none !important; }
            </style>
        </noscript>
    </body>
</html>
