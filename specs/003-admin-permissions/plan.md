# Implementation Plan: Admin Permissions System

**Branch**: `003-admin-permissions` | **Date**: 2025-12-09 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/003-admin-permissions/spec.md`

## Summary

Implement a role-based permissions system with two roles (Standard User and Admin). Admin users gain access to an admin area with user management capabilities including user listing, role management, and user session emulation. All admin actions are logged for audit purposes.

## Technical Context

**Language/Version**: PHP 8.3
**Framework**: Laravel 11 + Livewire 3
**Primary Dependencies**: Laravel Breeze (auth), Tailwind CSS (styling), Alpine.js (interactivity)
**Storage**: MySQL 8 (via Laravel Sail)
**Queue/Cache**: Redis
**Testing**: PHPUnit with Laravel feature tests, Livewire component tests
**Target Platform**: Web application (Laravel Sail Docker environment)
**Project Type**: Web application (monolith)
**Performance Goals**: Admin pages load < 2 seconds, impersonation switch < 1 second
**Constraints**: Constitution compliance (no Vue/React, no PostgreSQL, Laravel Sail required)
**Scale/Scope**: ~100 users initially, simple 2-role system

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Tech Stack | PASS | Laravel 11, PHP 8.3, Livewire 3, Tailwind CSS, MySQL 8 |
| II. Local Development | PASS | All development via Laravel Sail |
| III. Architecture Rules | PASS | Eloquent ORM, service class for audit logging, no raw SQL |
| IV. Code Style | PASS | Type hints required, PHPDoc blocks, feature + component tests |
| V. Security | PASS | Middleware for auth, CSRF protection, no credential changes during impersonation |

**Gate Status**: PASSED - No constitution violations

## Project Structure

### Documentation (this feature)

```text
specs/003-admin-permissions/
├── plan.md              # This file
├── research.md          # Phase 0 output - technology decisions
├── data-model.md        # Phase 1 output - entity definitions
├── quickstart.md        # Phase 1 output - implementation guide
├── contracts/           # Phase 1 output - route and component contracts
│   └── admin-routes.md
└── tasks.md             # Phase 2 output (via /speckit.tasks)
```

### Source Code (repository root)

```text
app/
├── Http/
│   └── Middleware/
│       ├── IsAdmin.php                    # Admin route protection
│       └── NotImpersonating.php           # Block during impersonation
├── Livewire/
│   └── Admin/
│       ├── Dashboard.php                  # Admin metrics
│       ├── UserList.php                   # User management
│       └── AuditLog.php                   # Audit viewer
├── Models/
│   ├── User.php                           # MODIFIED: Add role field
│   └── AdminAuditLog.php                  # NEW: Audit log model
├── Services/
│   └── AuditLogService.php                # NEW: Audit logging service
└── Traits/
    └── HandlesImpersonation.php           # NEW: Impersonation logic

database/
├── migrations/
│   ├── *_add_role_to_users_table.php      # NEW: Role column
│   └── *_create_admin_audit_logs_table.php # NEW: Audit logs
└── seeders/
    └── DevSeeder.php                      # MODIFIED: Add admin user

resources/views/
├── components/
│   ├── layouts/
│   │   └── app.blade.php                  # MODIFIED: Add emulation banner
│   └── emulation-banner.blade.php         # NEW: Impersonation indicator
└── livewire/
    ├── admin/
    │   ├── dashboard.blade.php            # NEW
    │   ├── user-list.blade.php            # NEW
    │   └── audit-log.blade.php            # NEW
    └── layout/
        └── navigation.blade.php           # MODIFIED: Add admin nav

routes/
└── web.php                                # MODIFIED: Add admin routes

bootstrap/
└── app.php                                # MODIFIED: Register middleware

tests/
└── Feature/
    └── Admin/
        ├── AdminAccessTest.php            # NEW
        ├── UserImpersonationTest.php      # NEW
        ├── RoleManagementTest.php         # NEW
        └── AuditLogTest.php               # NEW
```

**Structure Decision**: Extends existing Laravel structure with new `Admin` namespace for Livewire components, new middleware, new model, and new service class. Follows existing patterns established in the codebase.

## Complexity Tracking

> No constitution violations requiring justification.

| Aspect | Approach | Reasoning |
|--------|----------|-----------|
| Role Storage | Simple enum column | 2 roles only, no need for complex RBAC |
| Impersonation | Session-based | No persistence needed, clean browser isolation |
| Audit Logging | Service class | Explicit control, matches existing service patterns |

## Phase Outputs

### Phase 0: Research
- [research.md](./research.md) - Technology decisions and patterns

### Phase 1: Design
- [data-model.md](./data-model.md) - Entity definitions and relationships
- [contracts/admin-routes.md](./contracts/admin-routes.md) - Route and component contracts
- [quickstart.md](./quickstart.md) - Implementation guide

### Phase 2: Tasks
- tasks.md - Generated by `/speckit.tasks` command
