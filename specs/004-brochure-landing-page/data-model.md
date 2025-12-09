# Data Model: Brochure Landing Page

**Feature**: 004-brochure-landing-page
**Date**: 2025-12-09

## Overview

This feature is a **static landing page** with no database interactions. The data model below defines the **display-only structures** used to render content. These are not persisted to a database - they exist as static configuration or Blade template data.

---

## Key Entities

### 1. PricingTier (Display Only)

Represents a subscription tier displayed in the pricing section.

| Attribute | Type | Description | Example |
|-----------|------|-------------|---------|
| id | string | Unique identifier | "starter", "professional", "enterprise" |
| name | string | Display name | "Starter" |
| price_monthly | integer | Monthly price in USD | 49 |
| price_annual | integer | Annual price in USD (per month) | 39 |
| tagline | string | Brief description | "Perfect for getting started" |
| features | array\<string\> | List of included features | ["5,000 emails/month", "3 email accounts"] |
| cta_text | string | Call-to-action button text | "Start Free Trial" |
| cta_url | string | Link destination | "/register?plan=starter" |
| is_highlighted | boolean | Show as recommended tier | false |
| badge | string\|null | Optional badge text | "Most Popular" |

**Static Configuration (Blade/PHP):**
```php
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
        'cta_url' => '/register?plan=starter',
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
        'cta_url' => '/register?plan=professional',
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
```

---

### 2. Feature (Display Only)

Represents a product feature displayed in the features section.

| Attribute | Type | Description | Example |
|-----------|------|-------------|---------|
| id | string | Unique identifier | "ai-analysis" |
| title | string | Feature heading | "AI Response Analysis" |
| description | string | Brief explanation | "Claude AI automatically classifies responses..." |
| icon | string | Icon identifier/SVG | "sparkles" |
| accent_color | string | Brand or accent color | "brand" or "accent" |

**Static Configuration:**
```php
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
```

---

### 3. NavigationItem (Display Only)

Represents an in-page navigation link.

| Attribute | Type | Description | Example |
|-----------|------|-------------|---------|
| label | string | Display text | "Features" |
| href | string | Anchor link | "#features" |
| order | integer | Display order | 1 |

**Static Configuration:**
```php
$navItems = [
    ['label' => 'Features', 'href' => '#features', 'order' => 1],
    ['label' => 'Pricing', 'href' => '#pricing', 'order' => 2],
];
```

---

### 4. HeroContent (Display Only)

Content for the hero section.

| Attribute | Type | Description | Example |
|-----------|------|-------------|---------|
| headline | string | Main heading | "AI-Powered Cold Outreach That Works" |
| subheadline | string | Supporting text | "Discover interested leads automatically..." |
| primary_cta_text | string | Primary button text | "Start Free Trial" |
| primary_cta_url | string | Primary button link | "/register" |
| secondary_cta_text | string\|null | Secondary button text | "Watch Demo" |
| secondary_cta_url | string\|null | Secondary button link | "#demo" |

---

## Relationships

```
Landing Page
├── HeroContent (1)
├── NavigationItems (1..*)
├── Features (1..*)
└── PricingTiers (1..*)
```

All relationships are **static composition** - data is defined in the Blade template or a config file, not fetched from database.

---

## Notes

- **No Database Changes**: This feature does not add any database tables or migrations
- **No Eloquent Models**: Data structures are arrays defined in Blade templates or config
- **Future Consideration**: If pricing needs to be dynamic (A/B testing, region-based), consider moving to database-backed model
- **CTA URLs**: Point to existing auth routes (`/register`, `/login`) with optional query params for plan selection
