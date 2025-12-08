<!--
SYNC IMPACT REPORT
==================
Version change: 0.0.0 → 1.0.0 (MAJOR - initial constitution ratification)

Added Principles:
- I. Tech Stack (Non-Negotiable)
- II. Local Development
- III. Architecture Rules
- IV. Code Style
- V. Security

Added Sections:
- Technology Constraints
- Development Workflow

Templates requiring updates:
- .specify/templates/plan-template.md ✅ (Constitution Check section compatible)
- .specify/templates/spec-template.md ✅ (Requirements section compatible)
- .specify/templates/tasks-template.md ✅ (Phase structure compatible)

Follow-up TODOs: None
==================
-->

# Groundwork Constitution

## Core Principles

### I. Tech Stack (Non-Negotiable)

All development MUST use the following technology stack without exception:

- **Backend**: Laravel 11 with PHP 8.3
- **Frontend Components**: Livewire 3
- **Styling**: Tailwind CSS
- **Database**: MySQL 8
- **Queue & Cache**: Redis
- **AI Integration**: Claude API (claude-sonnet-4-20250514)

**Rationale**: A unified tech stack ensures consistency, reduces onboarding friction, and
enables the team to build deep expertise. Deviations fragment knowledge and introduce
maintenance burden.

### II. Local Development

All local development MUST use Laravel Sail as the Docker development environment.

- Every developer MUST run the application via `./vendor/bin/sail up`
- All CLI commands MUST be prefixed with `sail` (e.g., `sail artisan`, `sail composer`)
- Docker Compose configuration MUST remain compatible with Sail defaults
- Environment setup documentation MUST assume Sail as the only supported method

**Rationale**: Laravel Sail provides a consistent, containerized development environment
that eliminates "works on my machine" issues and ensures parity between developers.

### III. Architecture Rules

All code MUST follow these architectural constraints:

- **Laravel Conventions**: Follow PSR-12 coding standard, use service classes for business
  logic, use Form Requests for validation
- **Database Access**: Use Eloquent ORM for all database operations; no raw SQL unless
  performance-critical and documented
- **Async Processing**: Any operation taking longer than 1 second MUST be dispatched to
  a background job via Redis queue
- **Credential Storage**: All credentials MUST be encrypted at rest using Laravel's
  `encrypt()` helper
- **Integration Boundaries**: No direct LinkedIn API automation; all outreach MUST use
  email channels only

**Rationale**: Consistent architecture patterns make code predictable, reviewable, and
maintainable. The 1-second rule ensures responsive user experiences while the credential
encryption protects sensitive data.

### IV. Code Style

All code MUST adhere to these style requirements:

- **Type Hints**: All method parameters and return types MUST have type declarations
- **Documentation**: All public methods MUST have PHPDoc blocks describing purpose,
  parameters, and return values
- **Feature Tests**: All HTTP endpoints MUST have corresponding feature tests
- **Component Tests**: All Livewire components MUST have component tests covering
  user interactions

**Rationale**: Strong typing catches errors at development time. Documentation enables
team collaboration. Comprehensive tests prevent regressions and serve as executable
specifications.

### V. Security

All code MUST implement these security measures:

- **Input Validation**: All user input MUST be validated via Form Request classes;
  no inline validation in controllers
- **Credential Encryption**: SMTP and IMAP credentials MUST be encrypted using
  Laravel's `encrypt()` function before database storage
- **Rate Limiting**: All API endpoints MUST implement rate limiting via Laravel's
  throttle middleware
- **CSRF Protection**: All forms MUST include CSRF tokens; no exceptions

**Rationale**: Security is not optional. These baseline protections prevent common
attack vectors (injection, credential theft, abuse, CSRF) and MUST be present in
every feature from initial implementation.

## Technology Constraints

| Category | Allowed | Prohibited |
|----------|---------|------------|
| PHP Version | 8.3+ | < 8.3 |
| Framework | Laravel 11 | Other PHP frameworks |
| Frontend | Livewire 3, Blade | Vue, React, Inertia |
| CSS | Tailwind CSS | Bootstrap, custom CSS frameworks |
| Database | MySQL 8 | PostgreSQL, SQLite (except testing) |
| Cache/Queue | Redis | Database driver, file driver |
| AI | Claude API | OpenAI, other LLM providers |
| Dev Environment | Laravel Sail | Valet, Homestead, native PHP |

## Development Workflow

### Required Before Every PR

1. All tests pass: `sail artisan test`
2. Code style verified: `sail pint`
3. Static analysis clean: `sail artisan stan` (if configured)
4. No `dd()`, `dump()`, or `ray()` calls in committed code
5. Database migrations are reversible

### Commit Standards

- Commits MUST reference the feature or fix being implemented
- Commits MUST NOT include unrelated changes
- Large features SHOULD be broken into logical, reviewable commits

## Governance

This constitution supersedes all other development practices for the Groundwork project.

### Amendment Process

1. Proposed changes MUST be documented with rationale
2. Changes to Core Principles require explicit team review
3. All amendments MUST update the version number and Last Amended date
4. Breaking changes (principle removal/redefinition) require MAJOR version bump

### Versioning Policy

- **MAJOR**: Backward-incompatible changes (principle removal, redefinition)
- **MINOR**: New principles or sections added, material expansions
- **PATCH**: Clarifications, wording improvements, typo fixes

### Compliance

- All pull requests MUST be verified against these principles
- Constitution violations MUST be resolved before merge
- Complexity deviating from these rules MUST be justified in writing

**Version**: 1.0.0 | **Ratified**: 2025-12-08 | **Last Amended**: 2025-12-08
