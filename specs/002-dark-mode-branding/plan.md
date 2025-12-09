# Implementation Plan: Dark Mode Branding & UI/UX Improvements

**Branch**: `002-dark-mode-branding` | **Date**: 2025-12-09 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/002-dark-mode-branding/spec.md`

## Summary

Transform the application's visual design from a light-mode default to a cohesive dark-mode-first interface with a distinctive brand identity. This involves defining a dark-optimized color palette, updating all Blade components and Livewire views to use dark backgrounds with appropriate contrast, and ensuring WCAG 2.1 AA accessibility compliance across all UI elements.

## Technical Context

**Language/Version**: PHP 8.3, JavaScript (ES6+)
**Primary Dependencies**: Laravel 11, Livewire 3, Tailwind CSS 3.1+, Alpine.js
**Storage**: N/A (styling changes only, no data model changes)
**Testing**: PHPUnit (feature tests), Browser tests for visual consistency
**Target Platform**: Web (modern evergreen browsers - Chrome, Firefox, Safari, Edge)
**Project Type**: Web application (Laravel monolith)
**Performance Goals**: No performance regression; page load times maintained
**Constraints**: WCAG 2.1 AA compliance (4.5:1 text contrast, 3:1 interactive elements)
**Scale/Scope**: ~15 Blade components, ~12 Livewire views, 1 Tailwind config, 1 CSS entry point

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Requirement | Status | Notes |
|-----------|-------------|--------|-------|
| I. Tech Stack | Use Laravel 11, Livewire 3, Tailwind CSS | ✅ PASS | Feature uses existing stack; styling only |
| II. Local Development | Use Laravel Sail | ✅ PASS | No changes to dev environment |
| III. Architecture | Follow Laravel conventions | ✅ PASS | Blade components, no new business logic |
| IV. Code Style | Type hints, PHPDoc, tests | ✅ PASS | Will add browser/visual tests for styling |
| V. Security | CSRF, validation, encryption | ✅ PASS | No form or security changes |
| Frontend Constraint | Livewire 3, Blade only | ✅ PASS | No Vue/React/Inertia introduced |
| CSS Constraint | Tailwind CSS only | ✅ PASS | No Bootstrap or custom CSS frameworks |

**Gate Result**: ✅ ALL GATES PASS - Proceed to Phase 0

## Project Structure

### Documentation (this feature)

```text
specs/002-dark-mode-branding/
├── plan.md              # This file
├── research.md          # Phase 0 output: dark mode patterns, color research
├── data-model.md        # Phase 1 output: color palette definition
├── quickstart.md        # Phase 1 output: implementation guide
├── contracts/           # Phase 1 output: design tokens specification
└── tasks.md             # Phase 2 output (created by /speckit.tasks)
```

### Source Code (repository root)

```text
# Laravel 11 Web Application Structure (existing)
app/
├── Http/
│   └── Controllers/
├── Livewire/           # Livewire 3 components (styling updates)
│   ├── Campaign/
│   ├── Dashboard/
│   ├── Lead/
│   ├── Layout/
│   ├── Mailbox/
│   ├── Response/
│   └── Template/
├── Models/
├── Providers/
└── Services/

resources/
├── css/
│   └── app.css         # Tailwind entry point (update for dark mode)
├── js/
│   └── app.js
└── views/
    ├── components/     # Blade components (update for dark mode)
    │   ├── layouts/
    │   ├── primary-button.blade.php
    │   ├── secondary-button.blade.php
    │   ├── danger-button.blade.php
    │   ├── text-input.blade.php
    │   ├── modal.blade.php
    │   ├── dropdown.blade.php
    │   └── ...
    ├── livewire/       # Livewire views (update for dark mode)
    │   ├── campaign/
    │   ├── dashboard/
    │   ├── lead/
    │   ├── mailbox/
    │   ├── response/
    │   └── template/
    └── layouts/

config/
tailwind.config.js      # Extend with brand colors
tests/
├── Feature/            # HTTP endpoint tests
└── Browser/            # Visual/styling tests (to be added)
```

**Structure Decision**: Using existing Laravel 11 monolith structure. All changes are to view layer (Blade/Livewire templates) and Tailwind configuration. No new directories required.

## Complexity Tracking

> No constitution violations requiring justification.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| N/A | N/A | N/A |

---

## Post-Design Constitution Re-Check

*Re-evaluated after Phase 1 design completion.*

| Principle | Requirement | Status | Notes |
|-----------|-------------|--------|-------|
| I. Tech Stack | Use Laravel 11, Livewire 3, Tailwind CSS | ✅ PASS | CSS custom properties + Tailwind extension only |
| II. Local Development | Use Laravel Sail | ✅ PASS | No environment changes; Vite dev server unchanged |
| III. Architecture | Follow Laravel conventions | ✅ PASS | Blade component updates; no new patterns |
| IV. Code Style | Type hints, PHPDoc, tests | ✅ PASS | No PHP logic changes; visual testing recommended |
| V. Security | CSRF, validation, encryption | ✅ PASS | No security-relevant changes |
| Frontend Constraint | Livewire 3, Blade only | ✅ PASS | Pure Tailwind CSS styling |
| CSS Constraint | Tailwind CSS only | ✅ PASS | Uses Tailwind theme extension pattern |

**Post-Design Gate Result**: ✅ ALL GATES PASS

---

## Generated Artifacts

| Artifact | Path | Status |
|----------|------|--------|
| Research | [research.md](./research.md) | ✅ Complete |
| Data Model | [data-model.md](./data-model.md) | ✅ Complete |
| Design Tokens (JSON) | [contracts/design-tokens.json](./contracts/design-tokens.json) | ✅ Complete |
| Tailwind Theme | [contracts/tailwind-theme.js](./contracts/tailwind-theme.js) | ✅ Complete |
| CSS Variables | [contracts/css-variables.css](./contracts/css-variables.css) | ✅ Complete |
| Quickstart Guide | [quickstart.md](./quickstart.md) | ✅ Complete |

---

## Next Steps

1. Run `/speckit.tasks` to generate task breakdown for implementation
2. Tasks will cover:
   - Base configuration (CSS variables, Tailwind config)
   - Layout component updates
   - Button component updates
   - Form component updates
   - Page view updates (Dashboard, Response, Campaign, etc.)
   - Visual testing and accessibility verification
