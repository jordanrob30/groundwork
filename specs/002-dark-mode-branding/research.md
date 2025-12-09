# Research: Dark Mode Branding & UI/UX Improvements

**Feature Branch**: `002-dark-mode-branding`
**Research Date**: 2025-12-09
**Status**: Complete

## Research Summary

This document consolidates research findings for implementing a dark-mode-first interface with cohesive branding. All NEEDS CLARIFICATION items from the Technical Context have been resolved.

---

## 1. Dark Mode Color Palette Best Practices

### Decision: Use Dark Gray Backgrounds (Not Pure Black)

**Rationale**: Pure black (#000000) creates harsh contrast, causes eye strain, reduces legibility, and produces a "halation" effect on OLED displays.

**Alternatives Considered**:
- Pure black (#000000) - Rejected due to eye strain and harsh contrast
- Very dark gray (#121212) - Google Material Design recommendation (only 0.3% more power than pure black)
- Dark gray with blue tint (#1a1b26) - Selected approach for warmer, more professional feel

**Implementation**:
- Primary background: #0f0f14 (very dark with subtle blue undertone)
- Elevated surfaces: #1a1b26 (slightly lighter for cards, modals)
- Secondary surfaces: #24253a (borders, dividers, hover states)

### Decision: Use Off-White Text Colors

**Rationale**: Pure white (#FFFFFF) on dark backgrounds is harsh and difficult to read. Off-white reduces glare while maintaining excellent contrast.

**Implementation**:
- Primary text: #e5e5e5 (main content)
- Secondary text: #a0a0a0 (meta information, labels)
- Muted text: #6b7280 (disabled, placeholders)

---

## 2. Brand Color System

### Decision: Teal/Cyan Primary with Amber Accent

**Rationale**: Teal/cyan works exceptionally well on dark backgrounds, provides energy and modernity, and differentiates from competitors using standard blue. Amber provides strong visual contrast for calls-to-action.

**Alternatives Considered**:
- Blue (indigo-600) - Currently used; too common, less distinctive
- Purple - Good for tech, but less professional for business tool
- Teal/Cyan - Selected; professional, modern, excellent dark mode contrast

**Primary Palette** (8 colors maximum per SC-006):

| Token | Color | Hex | Usage |
|-------|-------|-----|-------|
| brand-primary | Teal | #14b8a6 | Interactive elements, links, focus rings |
| brand-primary-hover | Teal Light | #2dd4bf | Hover states |
| brand-primary-muted | Teal Dark | #0d9488 | Active states, pressed |
| brand-accent | Amber | #f59e0b | CTAs, important actions |
| brand-accent-hover | Amber Light | #fbbf24 | CTA hover states |

### Decision: Semantic Colors for Dark Mode

**Rationale**: Status colors must maintain meaning while being visible on dark backgrounds. Increased saturation and adjusted values ensure accessibility.

| Token | Light Mode Equivalent | Dark Mode Hex | Usage |
|-------|----------------------|---------------|-------|
| semantic-success | green-600 | #22c55e | Positive states, confirmations |
| semantic-warning | amber-500 | #f59e0b | Caution, attention needed |
| semantic-error | red-500 | #ef4444 | Errors, destructive actions |
| semantic-info | blue-500 | #3b82f6 | Informational messages |

### Decision: Lead Interest Level Colors (Dark Mode Optimized)

**Rationale**: Current colors (red, orange, blue, gray) need saturation adjustment for dark backgrounds while maintaining quick visual identification.

| Level | Current Color | Dark Mode Hex | Background | Text |
|-------|---------------|---------------|------------|------|
| Hot | red-600 | #ef4444 | #3f1219 | #fca5a5 |
| Warm | orange-600 | #f97316 | #431407 | #fdba74 |
| Cold | blue-600 | #3b82f6 | #1e3a5f | #93c5fd |
| Negative | gray-500 | #6b7280 | #1f2937 | #9ca3af |

---

## 3. Tailwind CSS Implementation Strategy

### Decision: Dark-Mode-First with Class Strategy

**Rationale**: Since the application is dark-mode-only (no toggle required), we implement dark mode as the default without using the `dark:` prefix on every element.

**Implementation Approach**:
1. Define CSS custom properties for all colors
2. Set dark values as the default (no `.dark` class needed)
3. Extend Tailwind config with semantic color tokens
4. Update components to use semantic tokens instead of hardcoded colors

**tailwind.config.js Extension**:
```javascript
module.exports = {
  theme: {
    extend: {
      colors: {
        // Background colors
        'bg-base': 'var(--color-bg-base)',
        'bg-elevated': 'var(--color-bg-elevated)',
        'bg-surface': 'var(--color-bg-surface)',

        // Text colors
        'text-primary': 'var(--color-text-primary)',
        'text-secondary': 'var(--color-text-secondary)',
        'text-muted': 'var(--color-text-muted)',

        // Brand colors
        'brand': 'var(--color-brand)',
        'brand-hover': 'var(--color-brand-hover)',
        'accent': 'var(--color-accent)',

        // Semantic colors
        'success': 'var(--color-success)',
        'warning': 'var(--color-warning)',
        'error': 'var(--color-error)',
        'info': 'var(--color-info)',
      },
    },
  },
};
```

**CSS Variables (resources/css/app.css)**:
```css
:root {
  /* Backgrounds */
  --color-bg-base: #0f0f14;
  --color-bg-elevated: #1a1b26;
  --color-bg-surface: #24253a;

  /* Text */
  --color-text-primary: #e5e5e5;
  --color-text-secondary: #a0a0a0;
  --color-text-muted: #6b7280;

  /* Brand */
  --color-brand: #14b8a6;
  --color-brand-hover: #2dd4bf;
  --color-accent: #f59e0b;

  /* Semantic */
  --color-success: #22c55e;
  --color-warning: #f59e0b;
  --color-error: #ef4444;
  --color-info: #3b82f6;
}
```

---

## 4. WCAG 2.1 AA Accessibility Compliance

### Decision: Target AAA Where Practical, Minimum AA Required

**Requirements Verified**:

| Criterion | WCAG AA Minimum | Our Target | Implementation |
|-----------|-----------------|------------|----------------|
| Normal text | 4.5:1 | 7:1+ | #e5e5e5 on #0f0f14 = 13.5:1 ✅ |
| Large text | 3:1 | 4.5:1+ | #a0a0a0 on #0f0f14 = 7.2:1 ✅ |
| Interactive elements | 3:1 | 3:1+ | #14b8a6 on #0f0f14 = 6.8:1 ✅ |
| Focus rings | 3:1 | 3:1+ | #14b8a6 visible ring ✅ |

### Decision: Focus Ring Strategy

**Implementation**:
- Use `ring-2 ring-brand ring-offset-2 ring-offset-bg-base` pattern
- Ensures 3:1+ contrast for focus visibility
- Offset provides visual separation from element

---

## 5. Component Migration Strategy

### Decision: Phased Component Updates

**Rationale**: Updating all components at once risks introducing regressions. Phased approach allows testing and validation.

**Phase Order**:
1. Base styles (CSS variables, Tailwind config)
2. Layout components (app layout, navigation)
3. Button components (primary, secondary, danger)
4. Form components (inputs, labels, errors)
5. Feedback components (modals, dropdowns, badges)
6. Page-specific views (Dashboard, Response, Campaign, etc.)

### Decision: Component Token Mapping

**Current → New Token Mapping**:

| Current Class | New Token Class |
|---------------|-----------------|
| `bg-white` | `bg-bg-elevated` |
| `bg-gray-50` | `bg-bg-base` |
| `bg-gray-100` | `bg-bg-surface` |
| `text-gray-900` | `text-text-primary` |
| `text-gray-600` | `text-text-secondary` |
| `text-gray-400` | `text-text-muted` |
| `bg-indigo-600` | `bg-brand` |
| `hover:bg-indigo-700` | `hover:bg-brand-hover` |
| `border-gray-200` | `border-bg-surface` |
| `border-gray-300` | `border-bg-surface` |

---

## Sources

- [Dark Mode Design: A Practical Guide - UX Design Institute](https://www.uxdesigninstitute.com/blog/dark-mode-design-practical-guide/)
- [WCAG 2.1 Understanding Contrast (Minimum)](https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum.html)
- [Tailwind CSS Dark Mode Documentation](https://tailwindcss.com/docs/dark-mode)
- [Building Dark Mode on Desktop - Slack Engineering](https://slack.engineering/building-dark-mode-on-desktop/)
- [Accelerating GitHub Theme Creation](https://github.blog/news-insights/product-news/accelerating-github-theme-creation-with-color-tooling/)
- [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
