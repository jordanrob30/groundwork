# Quickstart: Dark Mode Branding Implementation

**Feature Branch**: `002-dark-mode-branding`
**Date**: 2025-12-09

## Prerequisites

- Laravel Sail running (`./vendor/bin/sail up`)
- Node.js dependencies installed (`sail npm install`)
- Vite dev server ready (`sail npm run dev`)

## Quick Implementation Steps

### Step 1: Add CSS Custom Properties

Copy the CSS variables into `resources/css/app.css`:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

:root {
  /* Background colors */
  --color-bg-base: #0f0f14;
  --color-bg-elevated: #1a1b26;
  --color-bg-surface: #24253a;
  --color-bg-overlay: rgba(15, 15, 20, 0.8);

  /* Text colors */
  --color-text-primary: #e5e5e5;
  --color-text-secondary: #a0a0a0;
  --color-text-muted: #6b7280;
  --color-text-inverse: #0f0f14;

  /* Border colors */
  --color-border-default: #24253a;
  --color-border-muted: #363852;
  --color-border-focus: #14b8a6;

  /* Brand colors */
  --color-brand: #14b8a6;
  --color-brand-hover: #2dd4bf;
  --color-brand-active: #0d9488;

  /* Accent colors */
  --color-accent: #f59e0b;
  --color-accent-hover: #fbbf24;
  --color-accent-active: #d97706;

  /* Semantic colors */
  --color-success: #22c55e;
  --color-success-bg: rgba(34, 197, 94, 0.1);
  --color-warning: #f59e0b;
  --color-warning-bg: rgba(245, 158, 11, 0.1);
  --color-error: #ef4444;
  --color-error-bg: rgba(239, 68, 68, 0.1);
  --color-info: #3b82f6;
  --color-info-bg: rgba(59, 130, 246, 0.1);

  /* Lead interest level colors */
  --color-lead-hot-bg: #3f1219;
  --color-lead-hot-text: #fca5a5;
  --color-lead-hot-border: #ef4444;
  --color-lead-warm-bg: #431407;
  --color-lead-warm-text: #fdba74;
  --color-lead-warm-border: #f97316;
  --color-lead-cold-bg: #1e3a5f;
  --color-lead-cold-text: #93c5fd;
  --color-lead-cold-border: #3b82f6;
  --color-lead-negative-bg: #1f2937;
  --color-lead-negative-text: #9ca3af;
  --color-lead-negative-border: #6b7280;
}

html {
  background-color: var(--color-bg-base);
  color: var(--color-text-primary);
}
```

### Step 2: Update Tailwind Config

Update `tailwind.config.js`:

```javascript
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'bg-base': 'var(--color-bg-base)',
                'bg-elevated': 'var(--color-bg-elevated)',
                'bg-surface': 'var(--color-bg-surface)',
                'text-primary': 'var(--color-text-primary)',
                'text-secondary': 'var(--color-text-secondary)',
                'text-muted': 'var(--color-text-muted)',
                'text-inverse': 'var(--color-text-inverse)',
                'border-default': 'var(--color-border-default)',
                'brand': {
                    DEFAULT: 'var(--color-brand)',
                    hover: 'var(--color-brand-hover)',
                    active: 'var(--color-brand-active)',
                },
                'accent': {
                    DEFAULT: 'var(--color-accent)',
                    hover: 'var(--color-accent-hover)',
                    active: 'var(--color-accent-active)',
                },
                'success': {
                    DEFAULT: 'var(--color-success)',
                    bg: 'var(--color-success-bg)',
                },
                'warning': {
                    DEFAULT: 'var(--color-warning)',
                    bg: 'var(--color-warning-bg)',
                },
                'error': {
                    DEFAULT: 'var(--color-error)',
                    bg: 'var(--color-error-bg)',
                },
                'info': {
                    DEFAULT: 'var(--color-info)',
                    bg: 'var(--color-info-bg)',
                },
            },
            ringColor: {
                brand: 'var(--color-brand)',
            },
            ringOffsetColor: {
                base: 'var(--color-bg-base)',
            },
        },
    },

    plugins: [forms],
};
```

### Step 3: Update App Layout

Update `resources/views/components/layouts/app.blade.php`:

**Before** (light mode):
```html
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
```

**After** (dark mode):
```html
<body class="font-sans antialiased bg-bg-base text-text-primary">
    <div class="min-h-screen">
```

### Step 4: Update Primary Button

Update `resources/views/components/primary-button.blade.php`:

**Before**:
```html
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
```

**After**:
```html
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-brand border border-transparent rounded-md font-semibold text-xs text-text-inverse uppercase tracking-widest hover:bg-brand-hover focus:bg-brand-hover active:bg-brand-active focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-base transition ease-in-out duration-150']) }}>
```

### Step 5: Update Text Input

Update `resources/views/components/text-input.blade.php`:

**Before**:
```html
<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) !!}>
```

**After**:
```html
<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'bg-bg-base border-border-default text-text-primary placeholder:text-text-muted focus:border-brand focus:ring-brand rounded-md shadow-sm']) !!}>
```

## Common Pattern Mappings

| Old Class | New Class |
|-----------|-----------|
| `bg-white` | `bg-bg-elevated` |
| `bg-gray-50` | `bg-bg-base` |
| `bg-gray-100` | `bg-bg-surface` |
| `bg-gray-800` | `bg-brand` |
| `text-gray-900` | `text-text-primary` |
| `text-gray-600` | `text-text-secondary` |
| `text-gray-400` | `text-text-muted` |
| `border-gray-200` | `border-border-default` |
| `border-gray-300` | `border-border-default` |
| `focus:ring-indigo-500` | `focus:ring-brand` |
| `hover:bg-gray-50` | `hover:bg-bg-surface` |

## Verification Checklist

After each component update, verify:

- [ ] Background uses semantic token (`bg-bg-elevated`, `bg-bg-base`)
- [ ] Text uses semantic token (`text-text-primary`, `text-text-secondary`)
- [ ] Borders use semantic token (`border-border-default`)
- [ ] Focus rings use brand color (`focus:ring-brand`)
- [ ] Hover states are visible on dark background
- [ ] Contrast meets WCAG AA (4.5:1 text, 3:1 interactive)

## Testing

Run the application and verify:

```bash
# Start development server
sail npm run dev

# In another terminal, start Laravel
sail up

# Visit http://localhost to verify dark mode
```

## Troubleshooting

### Vite not picking up changes
```bash
sail npm run build
# or restart the dev server
sail npm run dev
```

### CSS variables not applying
Ensure `:root` block is at the top of `app.css` before `@tailwind` directives.

### Focus rings not visible
Check `focus:ring-offset-base` is added alongside `focus:ring-brand`.

---

## Implementation Notes (2025-12-09)

### Completed Implementation

All 62 tasks have been completed. The dark mode branding has been applied across:

**Core Infrastructure:**
- CSS custom properties in `app.css` with complete design token system
- Tailwind config extended with semantic color palette
- Base dark mode styles on HTML/body elements
- Focus ring CSS variables for accessibility
- Selection color styles

**Components Updated:**
- All button components (primary, secondary, danger)
- All navigation components (nav-link, responsive-nav-link, dropdown, dropdown-link)
- All form components (text-input, input-label, input-error, modal)
- All feedback components (action-message, auth-session-status)

**Views Updated:**
- Layouts: app, dev, guest
- Dashboard and welcome pages
- All campaign views (list, form, insights)
- All response views (inbox, view)
- All lead views (list, form, import)
- All mailbox views (list, form, health)
- All template views (editor, sequence-builder)
- All auth views (login, register, forgot-password, reset-password, verify-email, confirm-password)
- All profile views (update-profile-information, update-password, delete-user)
- Dev tools (dev-mail-tool)

### Key Class Mappings Applied

| Category | Old Classes | New Classes |
|----------|-------------|-------------|
| Background | `bg-white` | `bg-bg-elevated` |
| Background | `bg-gray-50` | `bg-bg-base` |
| Background | `bg-gray-100` | `bg-bg-surface` |
| Text | `text-gray-900`, `text-gray-700` | `text-text-primary` |
| Text | `text-gray-600`, `text-gray-500` | `text-text-secondary` |
| Text | `text-gray-400` | `text-text-muted` |
| Border | `border-gray-200`, `border-gray-300` | `border-border-default` |
| Divider | `divide-gray-200` | `divide-border-default` |
| Brand | `bg-indigo-600` | `bg-brand` |
| Brand | `text-indigo-600` | `text-brand` |
| Focus | `focus:ring-indigo-500` | `focus:ring-brand` |
| Status (success) | `bg-green-100 text-green-800` | `bg-success-bg text-success` |
| Status (warning) | `bg-yellow-100 text-yellow-800` | `bg-warning-bg text-warning` |
| Status (error) | `bg-red-100 text-red-800` | `bg-error-bg text-error` |

### Lead Interest Badge Colors

| Level | Background | Text | Border |
|-------|------------|------|--------|
| Hot | `bg-lead-hot-bg` | `text-lead-hot-text` | `border-lead-hot-border` |
| Warm | `bg-lead-warm-bg` | `text-lead-warm-text` | `border-lead-warm-border` |
| Cold | `bg-lead-cold-bg` | `text-lead-cold-text` | `border-lead-cold-border` |
| Negative | `bg-lead-negative-bg` | `text-lead-negative-text` | `border-lead-negative-border` |

### Build Verification

Run the following to verify the build:

```bash
# Rebuild CSS
sail npm run build

# Start the application
sail up -d

# Visit http://localhost to verify
```
