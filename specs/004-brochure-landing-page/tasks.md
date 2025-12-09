# Tasks: Brochure Landing Page

**Input**: Design documents from `/specs/004-brochure-landing-page/`
**Prerequisites**: plan.md (required), spec.md (required), research.md, data-model.md, quickstart.md

**Tests**: Not explicitly requested in spec - tests omitted (can be added via `/speckit.checklist`)

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3, US4, US5)
- Include exact file paths in descriptions

## Path Conventions

- **Laravel monolith**: `resources/`, `public/` at repository root
- JavaScript assets: `resources/js/`
- CSS assets: `resources/css/`
- Blade templates: `resources/views/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project dependencies and base structure for landing page

- [x] T001 Install p5.js dependency via `./vendor/bin/sail npm install p5` in package.json
- [x] T002 [P] Create landing-animations.js module file at resources/js/landing-animations.js with base exports
- [x] T003 [P] Create landing-page.css partial at resources/css/landing-page.css for landing-specific styles
- [x] T004 Import landing-animations.js module in resources/js/app.js with conditional initialization

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure and base template structure that all user stories depend on

**⚠️ CRITICAL**: Page structure must be in place before content sections can be implemented

- [x] T005 Create base landing page skeleton in resources/views/welcome.blade.php with section anchors (hero, features, pricing, footer)
- [x] T006 Implement in-page navigation component with smooth scroll behavior in resources/views/welcome.blade.php
- [x] T007 [P] Add CSS smooth scroll behavior and navigation styles in resources/css/landing-page.css
- [x] T008 Import landing-page.css in resources/css/app.css
- [x] T009 Rebuild assets with `./vendor/bin/sail npm run build` to verify base setup

**Checkpoint**: Navigation skeleton ready - section content can now be implemented in parallel

---

## Phase 3: User Story 1 - First-Time Visitor Explores Product (Priority: P1) 🎯 MVP

**Goal**: Visitor lands on homepage and immediately understands product value through hero section with compelling headline, tagline, and CTA

**Independent Test**: Load root URL, verify hero section displays within 3 seconds with product name, tagline, and primary CTA visible

### Implementation for User Story 1

- [x] T010 [US1] Define hero content data (headline, subheadline, CTAs) at top of resources/views/welcome.blade.php
- [x] T011 [US1] Implement hero section HTML structure with canvas container in resources/views/welcome.blade.php
- [x] T012 [US1] Style hero section with dark theme colors (bg-bg-base, text-text-primary) in resources/views/welcome.blade.php
- [x] T013 [US1] Add hero CTA buttons with brand styling linking to /register in resources/views/welcome.blade.php
- [x] T014 [US1] Ensure existing Livewire navigation component (Login/Register/Dashboard) remains functional in header

**Checkpoint**: Hero section complete - visitor sees product value proposition immediately

---

## Phase 4: User Story 2 - Prospect Evaluates Pricing Options (Priority: P1)

**Goal**: Visitor can view and compare 3 pricing tiers (Starter $49, Professional $99, Enterprise $249) with clear features and CTAs

**Independent Test**: Navigate to #pricing, verify all 3 tiers display with prices, features list, and working CTA buttons

### Implementation for User Story 2

- [x] T015 [US2] Define pricing tiers data array ($pricingTiers) per data-model.md in resources/views/welcome.blade.php
- [x] T016 [US2] Implement pricing section container with id="pricing" anchor in resources/views/welcome.blade.php
- [x] T017 [US2] Create pricing card component with tier name, price, tagline, features list in resources/views/welcome.blade.php
- [x] T018 [US2] Style pricing cards with dark theme (bg-bg-elevated, border-border-default) in resources/views/welcome.blade.php
- [x] T019 [US2] Highlight "Professional" tier with brand color border and "Most Popular" badge in resources/views/welcome.blade.php
- [x] T020 [US2] Add annual/monthly pricing toggle with Alpine.js state in resources/views/welcome.blade.php
- [x] T021 [US2] Style CTA buttons with brand color and proper hover states in resources/views/welcome.blade.php

**Checkpoint**: Pricing section complete - visitor can compare plans and click to register

---

## Phase 5: User Story 3 - Visitor Experiences Engaging Animations (Priority: P2)

**Goal**: Creative p5.js particle network animation runs in hero background, respects reduced motion preference

**Independent Test**: Load page, verify animation plays smoothly at 30fps; enable OS reduced motion, verify animation simplifies/stops

### Implementation for User Story 3

- [x] T022 [US3] Implement reduced motion detection function in resources/js/landing-animations.js
- [x] T023 [US3] Create heroSketch p5.js instance mode sketch with particle array in resources/js/landing-animations.js
- [x] T024 [US3] Implement particle network rendering with teal (#14b8a6) particles on dark background in resources/js/landing-animations.js
- [x] T025 [US3] Add particle connections (lines between nearby particles) in resources/js/landing-animations.js
- [x] T026 [US3] Implement windowResized handler for responsive canvas in resources/js/landing-animations.js
- [x] T027 [US3] Add reduced motion fallback (static particles, no movement) in resources/js/landing-animations.js
- [x] T028 [US3] Set frameRate(30) for performance optimization in resources/js/landing-animations.js
- [x] T029 [US3] Export and initialize heroSketch in initAnimations function in resources/js/landing-animations.js
- [x] T030 [US3] Style hero-canvas container with absolute positioning and z-index in resources/css/landing-page.css

**Checkpoint**: Animations complete - hero has engaging visual background that respects accessibility

---

## Phase 6: User Story 4 - Visitor Learns About Product Features (Priority: P2)

**Goal**: Features section displays 4 key product capabilities (Cold Email, AI Analysis, Insights, Mailbox Management) with icons and descriptions

**Independent Test**: Scroll to #features, verify all 4 features display with title, description, and relevant icon

### Implementation for User Story 4

- [x] T031 [US4] Define features data array ($features) per data-model.md in resources/views/welcome.blade.php
- [x] T032 [US4] Implement features section container with id="features" anchor in resources/views/welcome.blade.php
- [x] T033 [US4] Create feature card component with icon, title, description in resources/views/welcome.blade.php
- [x] T034 [US4] Add SVG icons for each feature (mail, sparkles, chart-bar, inbox) in resources/views/welcome.blade.php
- [x] T035 [US4] Style feature cards with grid layout and dark theme in resources/views/welcome.blade.php
- [x] T036 [US4] Add subtle hover effects on feature cards in resources/css/landing-page.css

**Checkpoint**: Features section complete - visitor understands product capabilities

---

## Phase 7: User Story 5 - Mobile Visitor Browses Landing Page (Priority: P2)

**Goal**: All content is readable and usable on screens from 320px to 2560px without horizontal scrolling

**Independent Test**: Load page at 375px, 768px, 1280px widths; verify no horizontal scroll, pricing cards stack, text readable

### Implementation for User Story 5

- [x] T037 [US5] Add responsive breakpoint classes to hero section (text sizes, spacing) in resources/views/welcome.blade.php
- [x] T038 [US5] Make pricing grid responsive (3-col desktop, 1-col mobile) in resources/views/welcome.blade.php
- [x] T039 [US5] Add responsive classes to features grid (2-col tablet, 1-col mobile) in resources/views/welcome.blade.php
- [x] T040 [US5] Adjust navigation for mobile (hamburger menu if needed) in resources/views/welcome.blade.php
- [x] T041 [US5] Test and fix canvas sizing on mobile viewports in resources/js/landing-animations.js
- [x] T042 [US5] Add mobile-specific spacing utilities in resources/css/landing-page.css

**Checkpoint**: Responsive design complete - page works on all devices

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Footer, edge cases, performance, and final validation

- [x] T043 Implement footer section with copyright, links in resources/views/welcome.blade.php
- [x] T044 Add noscript fallback content for users with JavaScript disabled in resources/views/welcome.blade.php
- [x] T045 Verify all navigation links use correct href anchors (#features, #pricing) in resources/views/welcome.blade.php
- [x] T046 Add aria-labels and keyboard navigation support for accessibility in resources/views/welcome.blade.php
- [x] T047 [P] Run final asset build with `./vendor/bin/sail npm run build`
- [ ] T048 [P] Test page load performance (target: <3s on average connection)
- [ ] T049 Run quickstart.md validation checklist
- [ ] T050 Visual QA across Chrome, Firefox, Safari at multiple viewport sizes

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phases 3-7)**: All depend on Foundational phase completion
  - US1 and US2 (both P1) can proceed in parallel after Foundational
  - US3, US4, US5 (all P2) can proceed in parallel after Foundational
- **Polish (Phase 8)**: Depends on all user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational - No dependencies on other stories
- **User Story 2 (P1)**: Can start after Foundational - No dependencies on other stories
- **User Story 3 (P2)**: Can start after Foundational - Needs hero-canvas container from US1
- **User Story 4 (P2)**: Can start after Foundational - No dependencies on other stories
- **User Story 5 (P2)**: Should run after US1-4 to test all content - Touches all sections

### Within Each User Story

- Data definitions before HTML structure
- HTML structure before styling
- Core content before enhancements
- Story complete before moving to next priority

### Parallel Opportunities

**Phase 1 (Setup)**:
- T002 and T003 can run in parallel (different files)

**Phase 2 (Foundational)**:
- T007 can run in parallel with T005/T006 (CSS vs Blade)

**After Foundational completes**:
- US1 (hero) and US2 (pricing) can run in parallel
- US4 (features) can run in parallel with US1/US2
- US3 (animations) depends on hero-canvas from US1

**Phase 8 (Polish)**:
- T047 and T048 can run in parallel (build vs test)

---

## Parallel Example: After Foundational Phase

```bash
# Launch P1 stories in parallel:
Task: "US1 - Hero section implementation" (T010-T014)
Task: "US2 - Pricing section implementation" (T015-T021)

# Launch US4 in parallel with P1 stories:
Task: "US4 - Features section implementation" (T031-T036)

# After US1 hero-canvas is ready, launch US3:
Task: "US3 - p5.js animations" (T022-T030)
```

---

## Implementation Strategy

### MVP First (User Stories 1 + 2 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational
3. Complete Phase 3: User Story 1 (Hero)
4. Complete Phase 4: User Story 2 (Pricing)
5. **STOP and VALIDATE**: Test hero + pricing independently
6. Deploy/demo if ready - visitor can see value prop and pricing

### Incremental Delivery

1. Setup + Foundational → Navigation skeleton ready
2. Add US1 (Hero) → Test → Deploy (Hero visible)
3. Add US2 (Pricing) → Test → Deploy (Pricing visible)
4. Add US4 (Features) → Test → Deploy (Features visible)
5. Add US3 (Animations) → Test → Deploy (Animations running)
6. Add US5 (Responsive) → Test → Deploy (Mobile ready)
7. Complete Polish → Final deploy

### Single Developer Strategy

Execute in priority order:
1. Phases 1-2 (Setup + Foundational)
2. Phase 3 (US1 - Hero) ← MVP milestone
3. Phase 4 (US2 - Pricing) ← MVP complete
4. Phase 6 (US4 - Features) ← before animations
5. Phase 5 (US3 - Animations) ← needs hero structure
6. Phase 7 (US5 - Responsive)
7. Phase 8 (Polish)

---

## Notes

- All tasks modify the same Blade file (welcome.blade.php) - avoid parallel edits within same file
- CSS and JS files can be edited in parallel with Blade
- Use Tailwind classes extensively - minimize custom CSS
- Test reduced motion in OS settings before marking US3 complete
- Pricing tier data matches data-model.md exactly
- Animation patterns from research.md (particle network with connections)
