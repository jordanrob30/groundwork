# Implementation Plan: Brochure Landing Page

**Branch**: `004-brochure-landing-page` | **Date**: 2025-12-09 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/004-brochure-landing-page/spec.md`

## Summary

Create a single-page brochure landing site for Groundwork on the root URL (`/`), featuring an inventive design with the existing dark mode colour scheme (teal brand, amber accent), p5.js-powered creative animations, in-page navigation, feature highlights, and a pricing section with tiered plans.

## Technical Context

**Language/Version**: PHP 8.3 + JavaScript (ES6+)
**Primary Dependencies**: Laravel 12, Livewire 3, Tailwind CSS 3.1+, p5.js (new dependency)
**Storage**: N/A (static landing page - no database interactions)
**Testing**: PHPUnit (feature tests), Browser tests for accessibility
**Target Platform**: Web - all modern browsers (Chrome, Firefox, Safari, Edge)
**Project Type**: Web application (Laravel monolith with Blade/Livewire)
**Performance Goals**: Page load under 3 seconds, animations at 60fps, Time to Interactive under 4 seconds on 3G
**Constraints**: Must respect prefers-reduced-motion, graceful degradation without JS, responsive 320px-2560px
**Scale/Scope**: Single landing page replacement, ~4 sections (hero, features, pricing, footer)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Tech Stack | ✅ PASS | Laravel 12/PHP 8.3, Livewire 3, Tailwind CSS - all compliant. p5.js is a frontend-only library for animations (user requirement) |
| II. Local Development | ✅ PASS | Uses Laravel Sail, no changes to dev environment needed |
| III. Architecture Rules | ✅ PASS | Frontend-only changes to Blade template, follows Laravel conventions |
| IV. Code Style | ✅ PASS | No new PHP classes needed, JavaScript follows ES6+ standards |
| V. Security | ✅ PASS | CSRF protection maintained, no user input handling, no new endpoints |

**Technology Constraints Check**:
- ✅ PHP 8.3+ (using 8.3)
- ✅ Laravel framework (using Laravel 12)
- ✅ Livewire 3 for navigation component (existing)
- ✅ Tailwind CSS for styling (existing)
- ⚠️ p5.js is a new frontend dependency (justified: user explicitly requested for animations)

**All Gates Pass** - Proceed to Phase 0.

## Project Structure

### Documentation (this feature)

```text
specs/004-brochure-landing-page/
├── plan.md              # This file
├── research.md          # Phase 0 output - p5.js patterns, animation techniques
├── data-model.md        # Phase 1 output - pricing tier structure (display-only)
├── quickstart.md        # Phase 1 output - setup guide
├── contracts/           # Phase 1 output - N/A (no API endpoints)
└── tasks.md             # Phase 2 output (/speckit.tasks command)
```

### Source Code (repository root)

```text
resources/
├── views/
│   └── welcome.blade.php        # Modified - main landing page
├── css/
│   └── app.css                  # May add landing-page specific styles
└── js/
    └── app.js                   # p5.js animation integration

public/
└── build/                       # Vite build output

package.json                     # Add p5.js dependency
```

**Structure Decision**: This feature modifies the existing Laravel monolith structure. No new directories needed - changes are contained to the existing `resources/views/welcome.blade.php` template and associated CSS/JS assets.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| p5.js dependency | User explicitly requested p5.js for creative animations | CSS-only animations cannot achieve the inventive, dynamic effects requested |

## Phase 0: Research Topics

1. **p5.js Integration with Laravel/Vite** - Best practices for including p5.js in a Vite-built Laravel project
2. **p5.js Animation Patterns** - Scroll-triggered animations, performance optimization, canvas positioning
3. **Reduced Motion Handling** - How to detect and respect `prefers-reduced-motion` in p5.js sketches
4. **Landing Page Section Patterns** - Best practices for single-page navigation, smooth scrolling
5. **SaaS Pricing Tier Patterns** - Common tier structures (Free/Pro/Enterprise) for email marketing tools

## Phase 1: Design Artifacts

1. **data-model.md** - Pricing tier structure (name, price, features array, CTA text)
2. **contracts/** - N/A (no API endpoints - static content only)
3. **quickstart.md** - How to add p5.js, modify welcome.blade.php, test animations

---

## Post-Design Constitution Re-Check

*Re-evaluated after Phase 1 design completion.*

| Principle | Status | Post-Design Notes |
|-----------|--------|-------------------|
| I. Tech Stack | ✅ PASS | No changes - Laravel 12, Livewire 3, Tailwind CSS remain core. p5.js is frontend-only |
| II. Local Development | ✅ PASS | No changes - Sail commands documented in quickstart.md |
| III. Architecture Rules | ✅ PASS | No database changes, no backend logic added, follows conventions |
| IV. Code Style | ✅ PASS | JavaScript module structure follows ES6+ patterns |
| V. Security | ✅ PASS | No new attack surface - static content only, existing CSRF maintained |

**Technology Constraints Re-Check**:
- ✅ No prohibited technologies introduced
- ✅ p5.js justified as user requirement (documented in Complexity Tracking)
- ✅ All frontend code bundled via Vite (Laravel-approved)

**Final Status**: All gates pass. Ready for Phase 2 (`/speckit.tasks`).

---

## Generated Artifacts Summary

| Artifact | Path | Status |
|----------|------|--------|
| Research | [research.md](research.md) | ✅ Complete |
| Data Model | [data-model.md](data-model.md) | ✅ Complete |
| Contracts | [contracts/README.md](contracts/README.md) | ✅ N/A (documented) |
| Quickstart | [quickstart.md](quickstart.md) | ✅ Complete |
| Tasks | tasks.md | ⏳ Pending `/speckit.tasks` |
