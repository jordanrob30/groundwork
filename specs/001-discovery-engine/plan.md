# Implementation Plan: Discovery Engine

**Branch**: `001-discovery-engine` | **Date**: 2025-12-08 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/001-discovery-engine/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

Customer discovery automation platform for founders to validate business ideas through systematic cold email outreach with AI-powered response analysis. The platform enables users to connect email accounts, create hypothesis-driven campaigns, import leads, send automated email sequences, detect and match replies, analyze responses with AI, and generate insights with decision scores.

## Technical Context

**Language/Version**: PHP 8.3 with Laravel 11
**Primary Dependencies**: Livewire 3, Tailwind CSS, Redis, Claude API (claude-sonnet-4-20250514)
**Storage**: MySQL 8 (encrypted credentials via Laravel encrypt())
**Testing**: PHPUnit via `sail artisan test` (feature tests for endpoints, component tests for Livewire)
**Target Platform**: Web application (Docker via Laravel Sail)
**Project Type**: Web application (Laravel monolith with Livewire components)
**Performance Goals**: Dashboard load <2s, Reply matching within 10 min, AI analysis within 30s, Lead import 1000 records in <30s
**Constraints**: Daily sending limits enforced 100% accuracy, 95%+ reply matching accuracy, Rate limiting on all API endpoints
**Scale/Scope**: Campaigns with 100-500 leads, 5-20% response rates expected, Multi-mailbox support per user

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Requirement | Compliance | Notes |
|-----------|-------------|------------|-------|
| I. Tech Stack | Laravel 11, PHP 8.3, Livewire 3, Tailwind CSS, MySQL 8, Redis, Claude API | ✅ PASS | All technologies align with constitution |
| II. Local Development | Laravel Sail for Docker environment | ✅ PASS | All commands via `sail` prefix |
| III. Architecture Rules | PSR-12, Service classes, Form Requests, Eloquent ORM, Encrypted credentials, Email-only outreach | ✅ PASS | No LinkedIn automation, credentials encrypted at rest |
| IV. Code Style | Type hints, PHPDoc blocks, Feature tests, Component tests | ✅ PASS | All endpoints and Livewire components will have tests |
| V. Security | Form Request validation, Credential encryption, Rate limiting, CSRF protection | ✅ PASS | SMTP/IMAP credentials encrypted via `encrypt()` |

**Gate Status**: ✅ ALL GATES PASS - Proceed to Phase 0

**Key Compliance Notes**:
- SMTP/IMAP credentials MUST use `encrypt()` before storage (Constitution V)
- AI analysis jobs MUST use Redis queue as they exceed 1 second (Constitution III)
- Email sending MUST be queued to Redis, not synchronous (Constitution III)
- All Livewire components MUST have component tests (Constitution IV)
- All HTTP endpoints MUST have feature tests (Constitution IV)
- No direct LinkedIn API integration allowed (Constitution III)

## Project Structure

### Documentation (this feature)

```text
specs/001-discovery-engine/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
app/
├── Models/
│   ├── User.php
│   ├── Mailbox.php
│   ├── Campaign.php
│   ├── Lead.php
│   ├── EmailTemplate.php
│   ├── SentEmail.php
│   ├── Response.php
│   ├── Insight.php
│   └── CallBooking.php
├── Services/
│   ├── MailboxService.php          # SMTP/IMAP connection management
│   ├── CampaignService.php         # Campaign lifecycle management
│   ├── LeadImportService.php       # CSV parsing and bulk import
│   ├── SendEngineService.php       # Email queue management
│   ├── ReplyDetectionService.php   # IMAP polling and matching
│   ├── AiAnalysisService.php       # Claude API integration
│   └── InsightService.php          # Pattern detection and scoring
├── Jobs/
│   ├── SendEmailJob.php
│   ├── PollMailboxJob.php
│   ├── AnalyzeResponseJob.php
│   └── GenerateInsightsJob.php
├── Http/
│   ├── Controllers/
│   │   └── Api/                    # API endpoints if needed
│   └── Requests/
│       ├── Mailbox/
│       ├── Campaign/
│       ├── Lead/
│       └── Template/
└── Livewire/
    ├── Dashboard/
    │   └── Dashboard.php
    ├── Mailbox/
    │   ├── MailboxList.php
    │   ├── MailboxForm.php
    │   └── MailboxHealth.php
    ├── Campaign/
    │   ├── CampaignList.php
    │   ├── CampaignForm.php
    │   └── CampaignInsights.php
    ├── Lead/
    │   ├── LeadList.php
    │   ├── LeadImport.php
    │   └── LeadForm.php
    ├── Template/
    │   ├── TemplateEditor.php
    │   └── SequenceBuilder.php
    └── Response/
        ├── ResponseInbox.php
        ├── ResponseView.php
        └── QuoteBoard.php

database/
├── migrations/
│   ├── *_create_mailboxes_table.php
│   ├── *_create_campaigns_table.php
│   ├── *_create_leads_table.php
│   ├── *_create_email_templates_table.php
│   ├── *_create_sent_emails_table.php
│   ├── *_create_responses_table.php
│   ├── *_create_insights_table.php
│   └── *_create_call_bookings_table.php
└── factories/

resources/views/
├── livewire/
│   ├── dashboard/
│   ├── mailbox/
│   ├── campaign/
│   ├── lead/
│   ├── template/
│   └── response/
└── components/

tests/
├── Feature/
│   ├── Mailbox/
│   ├── Campaign/
│   ├── Lead/
│   ├── Template/
│   └── Response/
└── Unit/
    └── Services/
```

**Structure Decision**: Laravel 11 monolith with Livewire 3 components. Following Laravel conventions with Models, Services (business logic), Jobs (async processing), Form Requests (validation), and Livewire components (UI). All async operations (email sending, IMAP polling, AI analysis) dispatched to Redis queues via Jobs.

## Post-Design Constitution Re-Check

*Re-evaluated after Phase 1 design completion.*

| Principle | Requirement | Compliance | Post-Design Notes |
|-----------|-------------|------------|-------------------|
| I. Tech Stack | Laravel 11, PHP 8.3, Livewire 3, Tailwind CSS, MySQL 8, Redis, Claude API | ✅ PASS | Added packages: webklex/laravel-imap, claude-php/claude-php-sdk-laravel, league/csv, zbateson/mail-mime-parser - all PHP/Laravel compatible |
| II. Local Development | Laravel Sail for Docker environment | ✅ PASS | quickstart.md documents all `sail` commands |
| III. Architecture Rules | PSR-12, Service classes, Form Requests, Eloquent ORM, Encrypted credentials, Email-only outreach | ✅ PASS | 7 Service classes defined, all async ops use Redis Jobs, credentials encrypted via Eloquent mutators |
| IV. Code Style | Type hints, PHPDoc blocks, Feature tests, Component tests | ✅ PASS | Service contracts include full type hints and PHPDoc, test structure defined |
| V. Security | Form Request validation, Credential encryption, Rate limiting, CSRF protection | ✅ PASS | Livewire component contracts include validation rules, credential encryption documented in data-model.md |

**Post-Design Gate Status**: ✅ ALL GATES PASS

**Verification Summary**:
1. **Credential Encryption**: Mailbox model uses `setSmtpPasswordAttribute()` mutators with `encrypt()` - documented in data-model.md
2. **Async Processing**: SendEmailJob, PollMailboxJob, AnalyzeResponseJob, GenerateInsightsJob all use Redis queues - documented in services.md
3. **Form Requests**: Livewire components define `$rules` arrays for validation - documented in livewire-components.md
4. **Test Coverage**: Test directory structure mirrors feature structure - documented in plan.md and quickstart.md
5. **No LinkedIn Integration**: All outreach is email-only via SMTP - no LinkedIn API anywhere in design

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

*No violations. All design decisions align with constitution requirements.*
