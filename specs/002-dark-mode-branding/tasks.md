# Tasks: Dark Mode Branding & UI/UX Improvements

**Input**: Design documents from `/specs/002-dark-mode-branding/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: No automated tests requested in spec. Visual verification through manual testing.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2)
- Include exact file paths in descriptions

## Path Conventions

- **Laravel monolith**: `resources/`, `tailwind.config.js` at repository root
- Blade components: `resources/views/components/`
- Livewire views: `resources/views/livewire/`
- CSS: `resources/css/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: CSS variables and Tailwind configuration that all user stories depend on

- [X] T001 Add CSS custom properties for dark mode design tokens in resources/css/app.css
- [X] T002 Extend Tailwind color palette with semantic tokens in tailwind.config.js
- [X] T003 Add base HTML/body dark mode styles in resources/css/app.css
- [X] T004 [P] Add focus ring CSS variables in resources/css/app.css
- [X] T005 [P] Add selection color styles in resources/css/app.css

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core layout that MUST be complete before page-specific styling

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T006 Update app layout with dark mode base classes in resources/views/components/layouts/app.blade.php
- [X] T007 Update dev layout with dark mode base classes in resources/views/components/layouts/dev.blade.php
- [X] T008 Update navigation component with dark mode styling in resources/views/livewire/layout/navigation.blade.php
- [X] T009 [P] Update welcome navigation with dark mode styling in resources/views/livewire/welcome/navigation.blade.php
- [ ] T010 Rebuild CSS with Vite to verify tokens are working (sail npm run build)

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 & 2 - Dark Mode Visual Experience & Brand Identity (Priority: P1) 🎯 MVP

**Goal**: Transform app to dark backgrounds with cohesive brand colors (teal primary, amber accent)

**Independent Test**: Open any page and verify dark backgrounds (#0f0f14), light text (#e5e5e5), and teal (#14b8a6) interactive elements

### Implementation for User Stories 1 & 2 (Combined - both P1)

#### Button Components

- [X] T011 [P] [US1] Update primary-button with brand colors in resources/views/components/primary-button.blade.php
- [X] T012 [P] [US1] Update secondary-button with dark mode styling in resources/views/components/secondary-button.blade.php
- [X] T013 [P] [US1] Update danger-button with error color styling in resources/views/components/danger-button.blade.php

#### Navigation Components

- [X] T014 [P] [US2] Update nav-link with brand/text colors in resources/views/components/nav-link.blade.php
- [X] T015 [P] [US2] Update responsive-nav-link with dark mode styling in resources/views/components/responsive-nav-link.blade.php
- [X] T016 [P] [US2] Update dropdown with elevated background in resources/views/components/dropdown.blade.php
- [X] T017 [P] [US2] Update dropdown-link with hover states in resources/views/components/dropdown-link.blade.php

#### Logo & Branding

- [X] T018 [US2] Update application-logo with brand-compatible colors in resources/views/components/application-logo.blade.php

#### Dashboard & Main Pages

- [X] T019 [US1] Update dashboard page with dark mode styling in resources/views/livewire/dashboard/dashboard.blade.php
- [X] T020 [P] [US1] Update dashboard wrapper with dark bg in resources/views/dashboard.blade.php
- [X] T021 [P] [US1] Update welcome page with dark mode styling in resources/views/welcome.blade.php
- [X] T022 [P] [US1] Update profile page wrapper in resources/views/profile.blade.php

**Checkpoint**: At this point, core navigation and dashboard should have dark mode styling with brand colors

---

## Phase 4: User Story 3 - Improved Visual Hierarchy & Typography (Priority: P2)

**Goal**: Clear visual hierarchy with readable typography on dark backgrounds

**Independent Test**: Navigate to Response Inbox and Dashboard, verify headings are distinct from body text, primary info is prominent

### Implementation for User Story 3

#### Response Views (Primary Workflow)

- [X] T023 [US3] Update response-inbox with visual hierarchy in resources/views/livewire/response/response-inbox.blade.php
- [X] T024 [US3] Update response-view with dark mode styling in resources/views/livewire/response/response-view.blade.php

#### Campaign Views

- [X] T025 [P] [US3] Update campaign-list with visual hierarchy in resources/views/livewire/campaign/campaign-list.blade.php
- [X] T026 [P] [US3] Update campaign-form with dark mode styling in resources/views/livewire/campaign/campaign-form.blade.php
- [X] T027 [P] [US3] Update campaign-insights with dark mode styling in resources/views/livewire/campaign/campaign-insights.blade.php

#### Lead Views

- [X] T028 [P] [US3] Update lead-list with visual hierarchy in resources/views/livewire/lead/lead-list.blade.php
- [X] T029 [P] [US3] Update lead-form with dark mode styling in resources/views/livewire/lead/lead-form.blade.php
- [X] T030 [P] [US3] Update lead-import with dark mode styling in resources/views/livewire/lead/lead-import.blade.php

**Checkpoint**: Primary workflows (Response, Campaign, Lead) should have proper visual hierarchy

---

## Phase 5: User Story 4 - Enhanced Interactive Elements (Priority: P2)

**Goal**: Clear hover, focus, and active states for all interactive elements

**Independent Test**: Tab through page elements, verify focus rings are visible (teal); hover over buttons/links and verify state changes

### Implementation for User Story 4

#### Form Components

- [X] T031 [P] [US4] Update text-input with focus/hover states in resources/views/components/text-input.blade.php
- [X] T032 [P] [US4] Update input-label with dark mode text color in resources/views/components/input-label.blade.php
- [X] T033 [P] [US4] Update input-error with error color styling in resources/views/components/input-error.blade.php

#### Feedback Components

- [X] T034 [P] [US4] Update action-message with dark mode styling in resources/views/components/action-message.blade.php
- [X] T035 [P] [US4] Update auth-session-status with dark mode styling in resources/views/components/auth-session-status.blade.php
- [X] T036 [US4] Update modal with dark mode styling in resources/views/components/modal.blade.php

#### Mailbox Views

- [X] T037 [P] [US4] Update mailbox-list with hover states in resources/views/livewire/mailbox/mailbox-list.blade.php
- [X] T038 [P] [US4] Update mailbox-form with dark mode styling in resources/views/livewire/mailbox/mailbox-form.blade.php
- [X] T039 [P] [US4] Update mailbox-health with dark mode styling in resources/views/livewire/mailbox/mailbox-health.blade.php

**Checkpoint**: All form and interactive elements should have clear focus/hover states

---

## Phase 6: User Story 5 - Accessible Color Contrast (Priority: P2)

**Goal**: All text and interactive elements meet WCAG 2.1 AA contrast requirements

**Independent Test**: Run contrast checker on primary text (#e5e5e5 on #0f0f14 = 13.5:1) and verify 4.5:1+ for all text

### Implementation for User Story 5

#### Lead Interest Level Badges (Critical for Accessibility)

- [X] T040 [US5] Update response-inbox lead badges with accessible dark mode colors in resources/views/livewire/response/response-inbox.blade.php
- [X] T041 [US5] Update response-view lead badges with accessible dark mode colors in resources/views/livewire/response/response-view.blade.php
- [X] T042 [P] [US5] Update lead-list badges with accessible dark mode colors in resources/views/livewire/lead/lead-list.blade.php

#### Status Badges (Success, Warning, Error, Info)

- [X] T043 [US5] Update dashboard status indicators with accessible colors in resources/views/livewire/dashboard/dashboard.blade.php
- [X] T044 [P] [US5] Update campaign-list status badges with accessible colors in resources/views/livewire/campaign/campaign-list.blade.php
- [X] T045 [P] [US5] Update mailbox-health status indicators with accessible colors in resources/views/livewire/mailbox/mailbox-health.blade.php

**Checkpoint**: All color-coded elements should meet WCAG 2.1 AA contrast requirements

---

## Phase 7: User Story 6 - Consistent Component Styling (Priority: P3)

**Goal**: All UI components (modals, dropdowns, forms) have consistent dark mode styling

**Independent Test**: Open modals, dropdowns, forms across different pages and verify consistent dark styling

### Implementation for User Story 6

#### Template Views

- [X] T046 [P] [US6] Update template-editor with dark mode styling in resources/views/livewire/template/template-editor.blade.php
- [X] T047 [P] [US6] Update sequence-builder with dark mode styling in resources/views/livewire/template/sequence-builder.blade.php

#### Profile Views

- [X] T048 [P] [US6] Update update-profile-information-form in resources/views/livewire/profile/update-profile-information-form.blade.php
- [X] T049 [P] [US6] Update update-password-form in resources/views/livewire/profile/update-password-form.blade.php
- [X] T050 [P] [US6] Update delete-user-form in resources/views/livewire/profile/delete-user-form.blade.php

#### Auth Views

- [X] T051 [P] [US6] Update login page in resources/views/livewire/pages/auth/login.blade.php
- [X] T052 [P] [US6] Update register page in resources/views/livewire/pages/auth/register.blade.php
- [X] T053 [P] [US6] Update forgot-password page in resources/views/livewire/pages/auth/forgot-password.blade.php
- [X] T054 [P] [US6] Update reset-password page in resources/views/livewire/pages/auth/reset-password.blade.php
- [X] T055 [P] [US6] Update verify-email page in resources/views/livewire/pages/auth/verify-email.blade.php
- [X] T056 [P] [US6] Update confirm-password page in resources/views/livewire/pages/auth/confirm-password.blade.php

#### Dev Tools

- [X] T057 [P] [US6] Update dev-mail-tool in resources/views/livewire/dev-mail-tool.blade.php

**Checkpoint**: All pages and components should have consistent dark mode styling

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Final verification and cleanup

- [X] T058 Run full visual inspection across all pages for consistency
- [X] T059 Verify all contrast ratios meet WCAG 2.1 AA (4.5:1 text, 3:1 interactive)
- [X] T060 [P] Run Vite production build and verify no CSS errors (sail npm run build)
- [X] T061 [P] Verify mobile responsive styling maintains dark mode
- [X] T062 Update quickstart.md with any implementation notes

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories 1&2 (Phase 3)**: Depends on Foundational - MVP milestone
- **User Story 3 (Phase 4)**: Depends on Foundational - builds on Phase 3 patterns
- **User Story 4 (Phase 5)**: Depends on Foundational - builds on Phase 3 patterns
- **User Story 5 (Phase 6)**: Depends on Foundational - can run parallel to Phases 4-5
- **User Story 6 (Phase 7)**: Depends on Foundational - can run parallel to Phases 4-6
- **Polish (Phase 8)**: Depends on all user stories being complete

### User Story Dependencies

- **User Stories 1&2 (P1)**: Foundation only - these are the MVP
- **User Story 3 (P2)**: Foundation only - independent of US1&2
- **User Story 4 (P2)**: Foundation only - independent of US1&2&3
- **User Story 5 (P2)**: Foundation + may refine US3 badge colors
- **User Story 6 (P3)**: Foundation only - independent but lower priority

### Within Each User Story

- Components can be updated in parallel if marked [P]
- Complete each story's components before checkpoint
- Verify checkpoint criteria before proceeding

### Parallel Opportunities

- All Setup tasks T001-T005 work on same file but different sections
- Foundational layout updates T006-T009 can run in parallel
- All button components (T011-T013) can run in parallel
- All navigation components (T014-T017) can run in parallel
- Most view updates within a phase can run in parallel

---

## Parallel Example: User Story 1 Button Components

```bash
# Launch all button updates together:
Task: "Update primary-button with brand colors in resources/views/components/primary-button.blade.php"
Task: "Update secondary-button with dark mode styling in resources/views/components/secondary-button.blade.php"
Task: "Update danger-button with error color styling in resources/views/components/danger-button.blade.php"
```

---

## Implementation Strategy

### MVP First (User Stories 1&2 Only)

1. Complete Phase 1: Setup (CSS tokens, Tailwind config)
2. Complete Phase 2: Foundational (layouts, navigation)
3. Complete Phase 3: User Stories 1&2 (dark mode + brand colors)
4. **STOP and VALIDATE**: Navigate through app, verify dark backgrounds and teal interactive elements
5. Deploy/demo if ready - this is the MVP

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Stories 1&2 → Validate → Deploy (MVP!)
3. Add User Story 3 (Visual Hierarchy) → Validate → Deploy
4. Add User Story 4 (Interactive States) → Validate → Deploy
5. Add User Story 5 (Accessibility) → Validate → Deploy
6. Add User Story 6 (Consistency) → Validate → Deploy
7. Polish → Final Deploy

### Single Developer Strategy

Complete phases in order:
1. Setup + Foundational (required)
2. User Stories 1&2 together (both P1, foundation for all)
3. User Stories 3-5 in priority order (all P2)
4. User Story 6 (P3)
5. Polish

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Reference specs/002-dark-mode-branding/contracts/css-variables.css for exact color values
- Reference specs/002-dark-mode-branding/quickstart.md for class mapping patterns
