# Data Model: Admin Permissions System

**Feature**: 003-admin-permissions
**Date**: 2025-12-09

## Entity Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        USER (modified)                          │
│  + role: enum('user', 'admin')                                 │
│  + isAdmin(): bool                                              │
│  + isUser(): bool                                               │
└──────────────────────────────┬──────────────────────────────────┘
                               │
                               │ 1:N (admin performs actions)
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                      ADMIN_AUDIT_LOG (new)                      │
│  - admin_id: FK → users                                         │
│  - target_user_id: FK → users (nullable)                        │
│  - action: string                                               │
│  - changes: json                                                │
│  - ip_address, user_agent, created_at                          │
└─────────────────────────────────────────────────────────────────┘
```

---

## Entity: User (Modified)

### Changes to Existing Model

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| `role` | enum('user', 'admin') | 'user' | User's permission level |

### Role Values

| Value | Description | Access |
|-------|-------------|--------|
| `user` | Standard user | Dashboard, Mailboxes, Campaigns, Responses |
| `admin` | Administrator | All standard features + Admin area |

### Model Methods

```
User
├── isAdmin(): bool          # Returns true if role === 'admin'
├── isUser(): bool           # Returns true if role === 'user'
├── scopeAdmins($query)      # Query scope for admin users only
└── scopeStandardUsers($query) # Query scope for standard users only
```

### Validation Rules

- Role MUST be one of: 'user', 'admin'
- At least one user with role 'admin' MUST exist (enforced at application level)

---

## Entity: AdminAuditLog (New)

### Purpose

Records all administrative actions for accountability and audit trail.

### Schema

| Field | Type | Nullable | Description |
|-------|------|----------|-------------|
| `id` | bigint (PK) | No | Auto-increment primary key |
| `admin_id` | bigint (FK → users) | No | User who performed the action |
| `target_user_id` | bigint (FK → users) | Yes | User affected by the action |
| `action` | varchar(100) | No | Action type identifier |
| `changes` | json | Yes | Before/after data or action details |
| `ip_address` | varchar(45) | Yes | Request IP address |
| `user_agent` | varchar(500) | Yes | Browser/client user agent |
| `created_at` | timestamp | No | When action occurred |

### Action Types

| Action | Description | target_user_id | changes |
|--------|-------------|----------------|---------|
| `user.impersonate` | Admin started impersonating | Required | `{started_at: timestamp}` |
| `user.stop_impersonate` | Admin stopped impersonating | Required | `{duration_seconds: int}` |
| `user.role_change` | Admin changed user's role | Required | `{from: string, to: string}` |

### Relationships

```
AdminAuditLog
├── belongsTo(User, 'admin_id')      # The admin who performed action
└── belongsTo(User, 'target_user_id') # The user affected (nullable)
```

### Indexes

| Index | Columns | Purpose |
|-------|---------|---------|
| Primary | `id` | Unique identifier |
| Foreign | `admin_id` | Join to users table |
| Foreign | `target_user_id` | Join to users table |
| Composite | `action, created_at` | Filter by action type and time range |

### Query Patterns

```
# Recent admin actions
AdminAuditLog::orderBy('created_at', 'desc')->limit(50)->get()

# Actions by specific admin
AdminAuditLog::where('admin_id', $adminId)->get()

# Impersonation history for user
AdminAuditLog::where('target_user_id', $userId)
    ->where('action', 'user.impersonate')
    ->get()

# Role changes in last 30 days
AdminAuditLog::where('action', 'user.role_change')
    ->where('created_at', '>', now()->subDays(30))
    ->get()
```

---

## Session Data (Runtime Only)

The impersonation feature uses session storage - not persisted to database.

### Session Keys

| Key | Type | Description |
|-----|------|-------------|
| `impersonating` | int | User ID being impersonated |
| `impersonated_by` | int | Original admin's user ID |
| `impersonation_started_at` | Carbon | When impersonation started |

### Session Lifecycle

```
Start Impersonation:
  session(['impersonating' => $targetUserId])
  session(['impersonated_by' => auth()->id()])
  session(['impersonation_started_at' => now()])

During Impersonation:
  getEffectiveUserId() → session('impersonating') ?? auth()->id()

Stop Impersonation:
  session()->forget(['impersonating', 'impersonated_by', 'impersonation_started_at'])
```

---

## State Transitions

### User Role State Machine

```
┌─────────┐                    ┌─────────┐
│  user   │ ←───────────────── │  admin  │
│         │  demote (if not    │         │
│         │  last admin)       │         │
└────┬────┘                    └────┬────┘
     │                              │
     │         promote              │
     └──────────────────────────────┘
```

**Constraints**:
- Any admin can promote a user to admin
- An admin can demote another admin to user
- An admin CANNOT demote themselves if they are the last admin
- System MUST always have at least one admin

### Impersonation State Machine

```
┌────────────────┐     start()      ┌─────────────────┐
│    Normal      │ ───────────────► │  Impersonating  │
│    Session     │                  │  (session keys  │
│                │ ◄─────────────── │   set)          │
└────────────────┘     stop()       └─────────────────┘
```

---

## Data Integrity Rules

1. **Role Constraint**: User role must be 'user' or 'admin'
2. **Last Admin Protection**: Application must prevent removing the last admin
3. **Audit Completeness**: Every impersonation and role change must be logged
4. **Session Isolation**: Impersonation sessions expire with browser session
5. **Cascade Delete**: Audit logs cascade delete when admin user is deleted

---

## Migration Order

1. `add_role_to_users_table` - Add role column to users
2. `create_admin_audit_logs_table` - Create audit log table
3. `seed_admin_user` - Update DevSeeder with admin and standard user

---

## Seeder Data

### Development Users

| Email | Password | Role | Purpose |
|-------|----------|------|---------|
| `admin@example.com` | `password` | admin | Test admin functionality |
| `dev@example.com` | `password` | user | Existing user (keep as standard) |

The existing `dev@example.com` user will remain as a standard user for testing standard user flows. A new admin user will be created for admin testing.
