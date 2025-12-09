# Quickstart: Admin Permissions System

**Feature**: 003-admin-permissions
**Date**: 2025-12-09

## Prerequisites

Before implementing this feature, ensure:

1. Laravel Sail is running: `./vendor/bin/sail up`
2. Database migrations are current: `sail artisan migrate`
3. Development seeder has run: `sail artisan db:seed`

---

## Implementation Overview

### New Files to Create

```
app/
├── Http/
│   └── Middleware/
│       ├── IsAdmin.php                    # Admin route protection
│       └── NotImpersonating.php           # Block sensitive actions during impersonation
├── Livewire/
│   └── Admin/
│       ├── Dashboard.php                  # Admin metrics dashboard
│       ├── UserList.php                   # User management
│       └── AuditLog.php                   # Audit log viewer
├── Models/
│   └── AdminAuditLog.php                  # Audit log model
├── Services/
│   └── AuditLogService.php                # Audit logging service
└── Traits/
    └── HandlesImpersonation.php           # Impersonation trait

database/
└── migrations/
    ├── YYYY_MM_DD_000001_add_role_to_users_table.php
    └── YYYY_MM_DD_000002_create_admin_audit_logs_table.php

resources/views/
├── components/
│   └── emulation-banner.blade.php         # Impersonation indicator
└── livewire/
    └── admin/
        ├── dashboard.blade.php
        ├── user-list.blade.php
        └── audit-log.blade.php
```

### Files to Modify

```
app/Models/User.php                        # Add role field and helpers
app/Providers/AppServiceProvider.php       # Register Gate
bootstrap/app.php                          # Register middleware aliases
database/seeders/DevSeeder.php             # Add admin user
resources/views/livewire/layout/navigation.blade.php  # Add admin nav
resources/views/components/layouts/app.blade.php      # Add emulation banner
routes/web.php                             # Add admin routes
```

---

## Step-by-Step Implementation

### Phase 1: Database Changes

1. **Create migration for user role**:
   ```bash
   sail artisan make:migration add_role_to_users_table
   ```

2. **Create migration for audit logs**:
   ```bash
   sail artisan make:migration create_admin_audit_logs_table
   ```

3. **Run migrations**:
   ```bash
   sail artisan migrate
   ```

### Phase 2: Models and Services

1. **Update User model** with role field and helper methods
2. **Create AdminAuditLog model**
3. **Create AuditLogService** for logging admin actions
4. **Create HandlesImpersonation trait**

### Phase 3: Middleware

1. **Create IsAdmin middleware**
2. **Create NotImpersonating middleware**
3. **Register middleware in bootstrap/app.php**

### Phase 4: Routes and Navigation

1. **Add admin route group** to routes/web.php
2. **Define Gate** for admin access
3. **Update navigation** with conditional admin link
4. **Add emulation banner** to app layout

### Phase 5: Livewire Components

1. **Create Admin\Dashboard** - metrics display
2. **Create Admin\UserList** - user management
3. **Create Admin\AuditLog** - audit history

### Phase 6: Seeder Updates

1. **Update DevSeeder** to create admin user
2. **Re-run seeder** (or create fresh):
   ```bash
   sail artisan migrate:fresh --seed
   ```

---

## Testing the Feature

### Manual Testing Checklist

1. **Role Access**:
   - [ ] Log in as `dev@example.com` (standard user) - cannot access `/admin`
   - [ ] Log in as `admin@example.com` (admin) - can access `/admin`

2. **Admin Dashboard**:
   - [ ] Metrics display correctly
   - [ ] Recent activity shows

3. **User List**:
   - [ ] All users visible
   - [ ] Search filters correctly
   - [ ] Role filter works
   - [ ] Can change user role (not last admin)

4. **User Emulation**:
   - [ ] Click "Emulate" - banner appears
   - [ ] See target user's data
   - [ ] Cannot access admin area while emulating
   - [ ] "Stop Emulating" returns to admin session

5. **Audit Log**:
   - [ ] Impersonation actions logged
   - [ ] Role changes logged
   - [ ] Filterable by action type

### Automated Tests to Write

```bash
# Feature tests
sail artisan make:test Admin/AdminAccessTest
sail artisan make:test Admin/UserImpersonationTest
sail artisan make:test Admin/RoleManagementTest
sail artisan make:test Admin/AuditLogTest

# Run tests
sail artisan test --filter=Admin
```

---

## Key Patterns to Follow

### User Queries During Impersonation

Always use the effective user ID helper:

```php
// In existing Livewire components
public function mount(): void
{
    $userId = session('impersonating', auth()->id());
    $this->campaigns = Campaign::where('user_id', $userId)->get();
}
```

### Audit Logging

Always log admin actions:

```php
use App\Services\AuditLogService;

// In admin Livewire components
AuditLogService::log(
    action: 'user.role_change',
    targetUser: $user,
    changes: ['from' => $oldRole, 'to' => $newRole]
);
```

### Navigation Guards

Use Blade directives:

```blade
@can('access-admin')
    {{-- Admin-only content --}}
@endcan
```

---

## Common Issues

| Issue | Solution |
|-------|----------|
| 403 on admin routes | Check middleware order in bootstrap/app.php |
| User queries show wrong data | Ensure using session-aware user ID |
| Emulation banner not showing | Check session key in layouts/app.blade.php |
| Audit log empty | Verify AuditLogService calls in components |
| Last admin error not triggering | Check admin count query in updateRole() |

---

## Constitution Compliance

This implementation adheres to all constitution requirements:

- **Laravel 11 + PHP 8.3**: All code uses Laravel 11 patterns
- **Livewire 3**: Full-page components for admin UI
- **Tailwind CSS**: All styling via Tailwind classes
- **MySQL 8**: Standard migrations and Eloquent
- **Service classes**: AuditLogService for business logic
- **Feature tests**: Required for all admin endpoints
- **Type hints**: All methods have parameter and return types
