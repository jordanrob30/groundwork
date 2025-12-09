# Research: Admin Permissions System

**Feature**: 003-admin-permissions
**Date**: 2025-12-09

## Research Summary

This document consolidates technical research findings for implementing the admin permissions system in the groundwork Laravel 11 + Livewire 3 application.

---

## 1. Role Storage Approach

### Decision: Simple `role` Column on Users Table

**Rationale**:
- Only two roles needed (admin vs standard) - a full roles/permissions pivot table is unnecessary complexity
- Matches the existing user ownership pattern (`user_id` foreign keys on campaigns, mailboxes, etc.)
- Less query overhead (no joins required for permission checks)
- Easy to extend to 3-4 roles later if needed by changing the enum values

**Alternatives Considered**:

| Approach | Pros | Cons | Rejected Because |
|----------|------|------|------------------|
| Spatie Laravel Permission Package | Full RBAC, granular permissions | Heavy for 2 roles, complex setup, additional tables | Over-engineering for current needs |
| Separate roles table with pivot | Normalized data, users can have multiple roles | Extra joins, more complexity | Multi-role not needed |
| Boolean `is_admin` column | Simplest possible | Limited extensibility | Role column only marginally more complex but far more flexible |

**Implementation Pattern**:
```php
// Migration: Add enum column with default
$table->enum('role', ['user', 'admin'])->default('user');

// Model: Helper methods for role checking
public function isAdmin(): bool { return $this->role === 'admin'; }
public function isUser(): bool { return $this->role === 'user'; }
```

---

## 2. Route Protection Strategy

### Decision: Custom Middleware with Laravel 11 Pattern

**Rationale**:
- Laravel 11 uses `bootstrap/app.php` for middleware registration (not HTTP Kernel)
- Simple middleware checking `auth()->user()->isAdmin()` is sufficient
- Matches existing `verified` middleware usage in the codebase

**Implementation Pattern**:
```php
// bootstrap/app.php - Register alias
$middleware->alias(['admin' => \App\Http\Middleware\IsAdmin::class]);

// Middleware - Check admin role
if (!auth()->user()?->isAdmin()) { abort(403); }

// Routes - Apply to admin group
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(...);
```

---

## 3. User Impersonation Approach

### Decision: Session-Based Impersonation with Trait

**Rationale**:
- Session storage is simple, secure, and doesn't persist across browser sessions
- Storing `impersonated_by` allows tracking the original admin for audit purposes
- Trait pattern makes impersonation logic reusable across components

**Session Keys**:
- `impersonating` - ID of the user being impersonated (affects queries)
- `impersonated_by` - ID of the original admin (for audit trail)
- `impersonation_started_at` - Timestamp for session tracking

**Query Scoping During Impersonation**:
The existing codebase already uses `auth()->id()` for user-scoped queries. During impersonation:
- Override the effective user ID from session when impersonating
- Create a helper method to get the "current effective user ID"
- Update existing queries to use this helper

**Implementation Pattern**:
```php
// Service or Trait approach
public function getEffectiveUserId(): int
{
    return session('impersonating', auth()->id());
}

// In components - use effective user for queries
Campaign::where('user_id', $this->getEffectiveUserId())->get();
```

**Sensitive Action Restrictions**:
- Profile/password changes blocked during impersonation
- Additional middleware `not-impersonating` for sensitive routes
- Blade directive `@notImpersonating` for UI protection

---

## 4. Audit Logging Strategy

### Decision: Service Class with Manual Logging

**Rationale**:
- Event-driven logging adds complexity without significant benefit for admin-only actions
- Service class pattern matches existing service patterns in the codebase
- Manual logging in Livewire methods gives explicit control over what's logged
- Simple table structure captures essential audit data without over-engineering

**Logged Actions**:
- `user.impersonate` - Admin starts impersonating a user
- `user.stop_impersonate` - Admin stops impersonating
- `user.role_change` - Admin changes another user's role

**Table Structure**:
- `admin_id` - Who performed the action
- `action` - Action type string
- `target_user_id` - User affected (if applicable)
- `changes` - JSON field for before/after data
- `ip_address`, `user_agent` - Request context
- `created_at` - Timestamp

**Implementation Pattern**:
```php
// Service call in Livewire component
AuditLogService::log(
    action: 'user.role_change',
    targetUser: $user,
    changes: ['role' => ['from' => 'user', 'to' => 'admin']]
);
```

---

## 5. Admin Navigation Integration

### Decision: Conditional Rendering with `@can` Directive

**Rationale**:
- Laravel Gates provide clean Blade integration
- Single place to define access logic
- Matches Laravel conventions

**Implementation**:
```php
// Define Gate in AppServiceProvider
Gate::define('access-admin', fn($user) => $user->isAdmin());

// In navigation blade
@can('access-admin')
    <x-nav-link href="{{ route('admin.dashboard') }}">Admin</x-nav-link>
@endcan
```

---

## 6. Emulation Banner Implementation

### Decision: Persistent Blade Component in Layout

**Rationale**:
- Banner must appear on every page during emulation
- Layout-level component ensures consistency
- Session check is lightweight

**Implementation**:
```blade
{{-- In layouts/app.blade.php --}}
@if(session()->has('impersonating'))
    <x-emulation-banner :user="App\Models\User::find(session('impersonating'))" />
@endif
```

---

## 7. Admin Dashboard Metrics

### Decision: Real-Time Aggregation Queries

**Rationale**:
- Data already exists in database (users, campaigns, sent_emails, responses)
- No need for separate analytics tables at current scale
- Simple count queries are performant

**Metrics to Display**:
| Metric | Query Pattern |
|--------|--------------|
| Total Users | `User::count()` |
| Active Users (7 days) | `User::where('updated_at', '>', now()->subDays(7))->count()` |
| Total Campaigns | `Campaign::count()` |
| Active Campaigns | `Campaign::where('status', 'active')->count()` |
| Emails Sent (30 days) | `SentEmail::where('sent_at', '>', now()->subDays(30))->count()` |
| Responses (30 days) | `Response::where('received_at', '>', now()->subDays(30))->count()` |

---

## 8. Last Admin Protection

### Decision: Query Check Before Role Change

**Rationale**:
- Simple count query before allowing demotion
- Prevents lockout scenario
- User-friendly error message

**Implementation**:
```php
// Before demoting an admin
$adminCount = User::where('role', 'admin')->count();
if ($adminCount <= 1 && $targetUser->isAdmin()) {
    throw new \Exception('Cannot remove the last administrator');
}
```

---

## Technology Decisions Summary

| Area | Decision | Constitution Compliance |
|------|----------|------------------------|
| Role Storage | `role` enum column on users | Laravel 11, Eloquent ORM |
| Middleware | Custom `IsAdmin` middleware | Laravel conventions |
| Impersonation | Session-based with trait | No external packages |
| Audit Logging | Service class, manual calls | Service class pattern |
| Navigation | Blade `@can` directive | Livewire 3, Blade |
| Dashboard | Real-time count queries | Eloquent ORM |
| Testing | Feature + component tests | PHPUnit, Livewire testing |

All decisions comply with the project constitution (Laravel 11, Livewire 3, Tailwind CSS, MySQL, Redis).
