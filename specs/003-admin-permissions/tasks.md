# Tasks: Admin Permissions System

**Input**: Design documents from `/specs/003-admin-permissions/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/admin-routes.md

**Tests**: Tests included per constitution requirement (feature tests for HTTP endpoints, component tests for Livewire)

**Organization**: Tasks grouped by user story for independent implementation and testing

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, etc.)
- Include exact file paths in descriptions

## Path Conventions

Laravel 11 structure at repository root:
- `app/` - Application code
- `database/migrations/` - Database migrations
- `database/seeders/` - Database seeders
- `resources/views/` - Blade templates
- `routes/` - Route definitions
- `tests/Feature/` - Feature tests

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Database schema and core model changes required by all user stories

- [x] T001 Create migration for role column in database/migrations/YYYY_MM_DD_000001_add_role_to_users_table.php
- [x] T002 Create migration for audit logs table in database/migrations/YYYY_MM_DD_000002_create_admin_audit_logs_table.php
- [ ] T003 Run migrations to update database schema: `sail artisan migrate`
- [x] T004 [P] Add role field and helper methods (isAdmin, isUser, scopes) to app/Models/User.php
- [x] T005 [P] Create AdminAuditLog model with relationships in app/Models/AdminAuditLog.php
- [x] T006 [P] Create AuditLogService for logging admin actions in app/Services/AuditLogService.php
- [x] T007 Update DevSeeder to create admin user (admin@example.com) in database/seeders/DevSeeder.php

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Middleware and routing infrastructure that MUST be complete before user stories

**CRITICAL**: No user story work can begin until this phase is complete

- [x] T008 Create IsAdmin middleware in app/Http/Middleware/IsAdmin.php
- [x] T009 [P] Create NotImpersonating middleware in app/Http/Middleware/NotImpersonating.php
- [x] T010 Register middleware aliases (admin, not-impersonating) in bootstrap/app.php
- [x] T011 Define access-admin Gate in app/Providers/AppServiceProvider.php
- [x] T012 Add admin route group with middleware to routes/web.php

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Admin Views All Users (Priority: P1)

**Goal**: Admins can view all registered users with search/filter capability

**Independent Test**: Log in as admin, access /admin/users, see all users with name, email, role, created_at. Search by name/email filters results.

### Tests for User Story 1

- [x] T013 [P] [US1] Create AdminAccessTest with tests for admin-only route protection in tests/Feature/Admin/AdminAccessTest.php
- [x] T014 [P] [US1] Create UserListTest for user listing and search functionality in tests/Feature/Admin/UserListTest.php

### Implementation for User Story 1

- [x] T015 [US1] Create Admin\UserList Livewire component in app/Livewire/Admin/UserList.php
- [x] T016 [US1] Create user-list Blade view with search, filter, table, pagination in resources/views/livewire/admin/user-list.blade.php
- [x] T017 [US1] Add admin nav link (conditional @can) to resources/views/livewire/layout/navigation.blade.php

**Checkpoint**: User Story 1 complete - admins can view and search users

---

## Phase 4: User Story 2 - Admin Emulates User (Priority: P1)

**Goal**: Admins can emulate any user's session to see their data and experience

**Independent Test**: Click "Emulate" on a user, get redirected to dashboard showing their data, see emulation banner, click "Stop Emulating" to return.

### Tests for User Story 2

- [x] T018 [P] [US2] Create UserImpersonationTest for start/stop emulation and data scoping in tests/Feature/Admin/UserImpersonationTest.php

### Implementation for User Story 2

- [x] T019 [US2] Create HandlesImpersonation trait with session management in app/Traits/HandlesImpersonation.php
- [x] T020 [US2] Add impersonate() and stopImpersonating() methods to UserList component in app/Livewire/Admin/UserList.php
- [x] T021 [US2] Create emulation-banner Blade component in resources/views/components/emulation-banner.blade.php
- [x] T022 [US2] Add emulation banner conditional to layout in resources/views/components/layouts/app.blade.php
- [x] T023 [US2] Add "Emulate" button to user list rows in resources/views/livewire/admin/user-list.blade.php
- [x] T024 [US2] Update existing Livewire components to use getEffectiveUserId() for queries (Dashboard, CampaignList, MailboxList, etc.)

**Checkpoint**: User Story 2 complete - admins can emulate users and see their data

---

## Phase 5: User Story 3 - Role-Based Access Control (Priority: P1)

**Goal**: Standard users cannot access admin areas; admins see admin nav; roles enforced

**Independent Test**: Log in as standard user, try /admin - get 403. Log in as admin - see Admin nav link and can access /admin.

### Tests for User Story 3

- [x] T025 [P] [US3] Add tests for role-based nav visibility and access denial to tests/Feature/Admin/AdminAccessTest.php

### Implementation for User Story 3

- [x] T026 [US3] Verify IsAdmin middleware returns 403 for non-admins (already in T008, verify behavior)
- [x] T027 [US3] Add mobile responsive admin nav link in resources/views/livewire/layout/navigation.blade.php
- [x] T028 [US3] Block sensitive actions (profile password change) during impersonation using NotImpersonating middleware in routes/web.php

**Checkpoint**: User Story 3 complete - role-based access is enforced

---

## Phase 6: User Story 4 - Admin Dashboard with System Metrics (Priority: P2)

**Goal**: Admins see system-wide metrics (user counts, campaign stats, recent activity)

**Independent Test**: Log in as admin, access /admin, see cards with total users, active users, campaign counts, email/response stats.

### Tests for User Story 4

- [x] T029 [P] [US4] Create AdminDashboardTest for metrics display in tests/Feature/Admin/AdminDashboardTest.php

### Implementation for User Story 4

- [x] T030 [US4] Create Admin\Dashboard Livewire component with metric queries in app/Livewire/Admin/Dashboard.php
- [x] T031 [US4] Create dashboard Blade view with stats cards and recent activity in resources/views/livewire/admin/dashboard.blade.php
- [x] T032 [US4] Update admin route group to use Dashboard as default /admin route in routes/web.php

**Checkpoint**: User Story 4 complete - admins have system overview

---

## Phase 7: User Story 5 - Admin Manages User Roles (Priority: P2)

**Goal**: Admins can change other users' roles (promote/demote) with last-admin protection

**Independent Test**: Click role dropdown on a user, select new role, save - role updates. Try to demote last admin - get error.

### Tests for User Story 5

- [x] T033 [P] [US5] Create RoleManagementTest for role changes and last-admin protection in tests/Feature/Admin/RoleManagementTest.php

### Implementation for User Story 5

- [x] T034 [US5] Add updateRole() method with last-admin check to UserList component in app/Livewire/Admin/UserList.php
- [x] T035 [US5] Add role change dropdown to user list rows in resources/views/livewire/admin/user-list.blade.php
- [x] T036 [US5] Add audit logging for role changes in AuditLogService calls within UserList component

**Checkpoint**: User Story 5 complete - admins can manage roles

---

## Phase 8: User Story 6 - Audit Log for Admin Actions (Priority: P3)

**Goal**: All admin actions (emulation, role changes) are logged and viewable

**Independent Test**: Perform admin actions, go to /admin/audit-log, see chronological list with action details.

### Tests for User Story 6

- [x] T037 [P] [US6] Create AuditLogTest for log creation and viewing in tests/Feature/Admin/AuditLogTest.php

### Implementation for User Story 6

- [x] T038 [US6] Create Admin\AuditLog Livewire component with filtering in app/Livewire/Admin/AuditLog.php
- [x] T039 [US6] Create audit-log Blade view with table and filters in resources/views/livewire/admin/audit-log.blade.php
- [x] T040 [US6] Add audit-log route to admin group in routes/web.php
- [x] T041 [US6] Add navigation link to Audit Log from admin dashboard in resources/views/livewire/admin/dashboard.blade.php

**Checkpoint**: User Story 6 complete - full audit trail available

---

## Phase 9: Polish & Cross-Cutting Concerns

**Purpose**: Final validation and code quality

- [x] T042 Run all tests and verify passing: `sail artisan test --filter=Admin`
- [x] T043 Run Pint for code style: `sail pint`
- [x] T044 Verify quickstart.md manual testing checklist
- [x] T045 [P] Add PHPDoc blocks to all new public methods
- [x] T046 [P] Review and optimize N+1 queries in user list and audit log

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phases 3-8)**: All depend on Foundational phase completion
- **Polish (Phase 9)**: Depends on all desired user stories being complete

### User Story Dependencies

| Story | Priority | Dependencies | Can Start After |
|-------|----------|--------------|-----------------|
| US1 (View Users) | P1 | None | Phase 2 |
| US2 (Emulation) | P1 | US1 (uses UserList) | Phase 3 |
| US3 (RBAC) | P1 | None | Phase 2 |
| US4 (Dashboard) | P2 | None | Phase 2 |
| US5 (Role Mgmt) | P2 | US1 (uses UserList) | Phase 3 |
| US6 (Audit Log) | P3 | AuditLogService (Phase 1) | Phase 1 |

### Recommended Execution Order

1. Phase 1 (Setup) → Phase 2 (Foundational) → **Foundation Ready**
2. Phase 3 (US1: View Users) → **MVP User Management**
3. Phase 4 (US2: Emulation) → **Support Tooling Complete**
4. Phase 5 (US3: RBAC) → **Security Complete**
5. Phase 6 (US4: Dashboard) → **Admin Overview Complete**
6. Phase 7 (US5: Role Mgmt) → **Full User Management**
7. Phase 8 (US6: Audit Log) → **Accountability Complete**
8. Phase 9 (Polish) → **Feature Complete**

### Parallel Opportunities

**Within Phase 1 (Setup)**:
```
T004 (User model) || T005 (AuditLog model) || T006 (AuditLogService)
```

**Within Phase 2 (Foundational)**:
```
T008 (IsAdmin middleware) || T009 (NotImpersonating middleware)
```

**Test Writing** (all test tasks marked [P]):
```
T013 || T014 || T018 || T025 || T029 || T033 || T037
```

**After Foundational Complete**:
- US1, US3, US4, US6 can start in parallel (no inter-story dependencies)
- US2 and US5 wait for US1 completion (both modify UserList)

---

## Implementation Strategy

### MVP First (User Stories 1-3)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational
3. Complete Phase 3: US1 - Admin Views Users
4. Complete Phase 4: US2 - User Emulation
5. Complete Phase 5: US3 - Role-Based Access
6. **STOP and VALIDATE**: Core admin functionality working
7. Deploy/demo MVP

### Incremental Delivery

| Milestone | Stories Complete | Value Delivered |
|-----------|------------------|-----------------|
| MVP | US1, US2, US3 | Admins can view users, emulate them, and access is controlled |
| Enhanced | + US4 | System overview and monitoring |
| Full Management | + US5 | Self-service role management |
| Complete | + US6 | Full audit trail for accountability |

---

## Task Summary

| Phase | Task Count | Parallelizable |
|-------|------------|----------------|
| Setup | 7 | 3 |
| Foundational | 5 | 2 |
| US1 (View Users) | 5 | 2 |
| US2 (Emulation) | 7 | 1 |
| US3 (RBAC) | 4 | 1 |
| US4 (Dashboard) | 4 | 1 |
| US5 (Role Mgmt) | 4 | 1 |
| US6 (Audit Log) | 5 | 1 |
| Polish | 5 | 2 |
| **Total** | **46** | **14** |

---

## Notes

- All middleware follows Laravel 11 bootstrap/app.php pattern
- All Livewire components use Volt-style full-page components
- All views use Tailwind CSS per constitution
- Audit logging happens in Livewire component methods via AuditLogService
- Tests follow PHPUnit + Livewire component testing patterns
- Commit after each task or logical group
- Run `sail artisan test` frequently to catch regressions
