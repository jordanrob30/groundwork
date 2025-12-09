# Admin Routes Contract

**Feature**: 003-admin-permissions
**Date**: 2025-12-09

## Overview

This document defines the routes and Livewire component contracts for the admin permissions system. Since this is a Livewire 3 application, routes map to full-page Livewire components rather than traditional REST endpoints.

---

## Route Definitions

### Admin Routes (Protected by `admin` middleware)

All routes require: `auth`, `verified`, `admin` middleware

| Method | URI | Livewire Component | Name | Description |
|--------|-----|-------------------|------|-------------|
| GET | `/admin` | `Admin\Dashboard` | `admin.dashboard` | Admin dashboard with metrics |
| GET | `/admin/users` | `Admin\UserList` | `admin.users.index` | List all users |
| GET | `/admin/audit-log` | `Admin\AuditLog` | `admin.audit-log` | View admin action history |
| POST | `/admin/impersonate/{user}` | - | `admin.impersonate` | Start impersonation (action) |
| POST | `/admin/stop-impersonate` | - | `admin.stop-impersonate` | Stop impersonation (action) |

### Route Group Structure

```php
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');
        Route::get('/users', UserList::class)->name('users.index');
        Route::get('/audit-log', AuditLog::class)->name('audit-log');
    });
```

---

## Livewire Component Contracts

### Admin\Dashboard

**Purpose**: Display system-wide metrics and recent activity

**Public Properties**:
```
- $totalUsers: int
- $activeUsers: int (logged in within 7 days)
- $totalCampaigns: int
- $activeCampaigns: int
- $emailsSentThisMonth: int
- $responsesThisMonth: int
- $recentActivity: Collection
```

**Methods**:
```
mount(): void
  - Loads all metrics via count queries
  - Loads recent activity (last 10 events)

render(): View
```

**View Requirements**:
- Stats cards showing each metric
- Recent activity list with timestamps
- Navigation to Users and Audit Log

---

### Admin\UserList

**Purpose**: Display all users with search/filter and management actions

**Public Properties**:
```
- $search: string (wire:model for search input)
- $roleFilter: string|null ('admin', 'user', or null for all)
- $users: LengthAwarePaginator
```

**Methods**:
```
mount(): void
  - Initialize with empty search

updatedSearch(): void
  - Reset pagination on search change

updateRole(int $userId, string $newRole): void
  - Validate new role
  - Check not removing last admin
  - Update user role
  - Log to audit log
  - Flash success message

impersonate(int $userId): void
  - Set session keys
  - Log to audit log
  - Redirect to dashboard

render(): View
  - Query users with search filter
  - Apply role filter if set
  - Paginate results
```

**View Requirements**:
- Search input field
- Role filter dropdown
- Table with columns: Name, Email, Role, Created At, Actions
- Per-row actions: Change Role (dropdown), Emulate (button)
- Pagination controls

**Business Rules**:
- Cannot demote last admin (show error message)
- Cannot emulate self (hide button)

---

### Admin\AuditLog

**Purpose**: Display chronological list of admin actions

**Public Properties**:
```
- $actionFilter: string|null (filter by action type)
- $logs: LengthAwarePaginator
```

**Methods**:
```
mount(): void
  - Initialize filters

render(): View
  - Query audit logs with relationships
  - Apply action filter if set
  - Order by created_at desc
  - Paginate results
```

**View Requirements**:
- Action type filter dropdown
- Table with columns: Date/Time, Admin, Action, Target User, Details
- Pagination controls
- Human-readable action descriptions

---

## Impersonation Actions

### Start Impersonation

**Trigger**: Button click in UserList component

**Flow**:
1. Admin clicks "Emulate" on user row
2. `impersonate($userId)` method called
3. Session keys set:
   - `impersonating` = target user ID
   - `impersonated_by` = current admin ID
   - `impersonation_started_at` = now()
4. Audit log entry created
5. Redirect to `/dashboard`

**Validation**:
- Target user must exist
- Cannot emulate self
- Must be admin

---

### Stop Impersonation

**Trigger**: Button in emulation banner (visible on all pages)

**Flow**:
1. Admin clicks "Stop Emulating"
2. Session keys cleared
3. Audit log entry created (with duration)
4. Redirect to `/admin/users`

---

## Emulation Banner Component

**Component**: `EmulationBanner` (Blade component, not full-page Livewire)

**Location**: Included in `layouts/app.blade.php`

**Props**:
```
- $impersonatedUser: User model
```

**Display Conditions**:
```php
@if(session()->has('impersonating'))
    <x-emulation-banner :user="App\Models\User::find(session('impersonating'))" />
@endif
```

**View Requirements**:
- Fixed position banner (top of page, below nav)
- Text: "You are viewing as {user name} ({user email})"
- "Stop Emulating" button
- Distinct styling (warning color) to prevent confusion

---

## Navigation Integration

### Admin Nav Item

**Condition**: Only visible to admin users

```blade
@can('access-admin')
    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
        Admin
    </x-nav-link>
@endcan
```

**Gate Definition**:
```php
Gate::define('access-admin', fn($user) => $user->isAdmin());
```

---

## Error Responses

| Scenario | Response |
|----------|----------|
| Non-admin accessing admin route | 403 Forbidden, redirect to dashboard |
| Invalid user ID for impersonation | 404 Not Found |
| Attempting to remove last admin | Flash error: "Cannot remove the last administrator" |
| Attempting to emulate self | Flash error: "Cannot emulate yourself" |

---

## Session Contract

### Impersonation Session Keys

| Key | Type | Set By | Cleared By |
|-----|------|--------|------------|
| `impersonating` | int | `impersonate()` | `stopImpersonating()` |
| `impersonated_by` | int | `impersonate()` | `stopImpersonating()` |
| `impersonation_started_at` | Carbon | `impersonate()` | `stopImpersonating()` |

### Helper Methods

```php
// Get effective user ID for queries (respects impersonation)
public function getEffectiveUserId(): int
{
    return session('impersonating', auth()->id());
}

// Check if currently impersonating
public function isImpersonating(): bool
{
    return session()->has('impersonating');
}
```
