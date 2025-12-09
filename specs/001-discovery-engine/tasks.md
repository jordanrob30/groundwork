# Tasks: Discovery Engine

**Input**: Design documents from `/specs/001-discovery-engine/`
**Prerequisites**: plan.md ✓, spec.md ✓, research.md ✓, data-model.md ✓, contracts/ ✓

**Tests**: Tests are OPTIONAL - include feature tests for endpoints and component tests for Livewire components per constitution requirements.

**Organization**: Tasks grouped by user story to enable independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and Laravel structure

- [X] T001 Create feature branch `001-discovery-engine` and update `.env.example` with required keys
- [X] T002 [P] Install required packages: `webklex/laravel-imap`, `claude-php/claude-php-sdk-laravel`, `league/csv`, `zbateson/mail-mime-parser`
- [X] T003 [P] Publish package configs: IMAP config and Claude SDK config
- [X] T004 [P] Configure `config/services.php` with Claude API settings
- [X] T005 [P] Configure `config/queue.php` for Redis queue connection

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T006 Create base migrations framework and run `sail artisan migrate:fresh`
- [X] T007 [P] Create `app/Models/User.php` relationships for mailboxes and campaigns
- [X] T008 [P] Create `app/Exceptions/AiAnalysisException.php` custom exception
- [X] T009 [P] Create `app/Exceptions/MailboxConnectionException.php` custom exception
- [X] T010 Configure scheduler in `app/Console/Kernel.php` for mailbox polling, warmup, email scheduling, insights
- [X] T011 [P] Create base Livewire layout component `resources/views/layouts/app.blade.php`
- [X] T012 [P] Setup Tailwind CSS configuration and compile assets

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Connect Email Account (Priority: P1) 🎯 MVP

**Goal**: Enable users to connect email accounts via SMTP/IMAP credentials with validation and warm-up support

**Independent Test**: Connect an email account, verify credentials, confirm system can send/receive emails

### Database Layer

- [X] T013 [US1] Create migration `database/migrations/*_create_mailboxes_table.php` with all fields from data-model.md
- [X] T014 [US1] Create migration `database/migrations/*_create_mailbox_sending_stats_table.php` for daily stats tracking
- [X] T015 [US1] Create `app/Models/Mailbox.php` with encrypted credential mutators and relationships

### Service Layer

- [X] T016 [US1] Create `app/Services/MailboxService.php` implementing `MailboxServiceInterface` from contracts
- [X] T017 [US1] Implement `validateSmtpCredentials()` using Symfony Mailer transport test
- [X] T018 [US1] Implement `validateImapCredentials()` using Webklex IMAP connection test
- [X] T019 [US1] Implement `getCurrentDailyLimit()` with warm-up schedule calculation
- [X] T020 [US1] Implement `hasReachedDailyLimit()` checking `mailbox_sending_stats`

### Livewire Components

- [X] T021 [P] [US1] Create `app/Livewire/Mailbox/MailboxList.php` with pause/resume/delete actions
- [X] T022 [P] [US1] Create `resources/views/livewire/mailbox/mailbox-list.blade.php` view
- [X] T023 [US1] Create `app/Livewire/Mailbox/MailboxForm.php` with credential validation and save
- [X] T024 [US1] Create `resources/views/livewire/mailbox/mailbox-form.blade.php` with SMTP/IMAP fields
- [X] T025 [P] [US1] Create `app/Livewire/Mailbox/MailboxHealth.php` displaying status and warmup progress
- [X] T026 [P] [US1] Create `resources/views/livewire/mailbox/mailbox-health.blade.php` view

### Events

- [X] T027 [P] [US1] Create `app/Events/MailboxCreated.php`
- [X] T028 [P] [US1] Create `app/Events/MailboxUpdated.php`
- [X] T029 [P] [US1] Create `app/Events/MailboxErrorOccurred.php`

### Commands

- [X] T030 [US1] Create `app/Console/Commands/WarmupMailboxCommand.php` for daily warm-up limit updates

### Routes

- [X] T031 [US1] Add mailbox routes in `routes/web.php` for list, create, edit, health views

**Checkpoint**: User Story 1 complete - users can connect and manage email accounts

---

## Phase 4: User Story 2 - Create and Launch Discovery Campaign (Priority: P1) 🎯 MVP

**Goal**: Enable users to create hypothesis-driven campaigns and manage their lifecycle

**Independent Test**: Create a campaign with hypothesis, activate it, pause it, archive it

### Database Layer

- [X] T032 [US2] Create migration `database/migrations/*_create_campaigns_table.php` with status enum and hypothesis fields
- [X] T033 [US2] Create `app/Models/Campaign.php` with status constants, relationships, and scopes

### Service Layer

- [X] T034 [US2] Create `app/Services/CampaignService.php` implementing `CampaignServiceInterface`
- [X] T035 [US2] Implement `activate()` with validation (requires leads and templates)
- [X] T036 [US2] Implement `pause()`, `complete()`, `archive()` status transitions
- [X] T037 [US2] Implement `duplicate()` copying settings, templates (not leads)
- [X] T038 [US2] Implement `calculateMetrics()` for response rate, interest breakdown
- [X] T039 [US2] Implement `calculateDecisionScore()` with weighted factors

### Livewire Components

- [X] T040 [P] [US2] Create `app/Livewire/Campaign/CampaignList.php` with filtering and sorting
- [X] T041 [P] [US2] Create `resources/views/livewire/campaign/campaign-list.blade.php` view
- [X] T042 [US2] Create `app/Livewire/Campaign/CampaignForm.php` with hypothesis fields
- [X] T043 [US2] Create `resources/views/livewire/campaign/campaign-form.blade.php` view

### Events

- [X] T044 [P] [US2] Create `app/Events/CampaignActivated.php`
- [X] T045 [P] [US2] Create `app/Events/CampaignPaused.php`
- [X] T046 [P] [US2] Create `app/Events/CampaignCompleted.php`

### Routes

- [X] T047 [US2] Add campaign routes in `routes/web.php` for list, create, edit, insights views

**Checkpoint**: User Story 2 complete - users can create and manage discovery campaigns

---

## Phase 5: User Story 3 - Import Leads for Outreach (Priority: P1) 🎯 MVP

**Goal**: Enable bulk lead import via CSV with column mapping

**Independent Test**: Upload CSV, map columns, verify leads appear in campaign

### Database Layer

- [X] T048 [US3] Create migration `database/migrations/*_create_leads_table.php` with status enum and custom fields JSON
- [X] T049 [US3] Create `app/Models/Lead.php` with status constants, custom field casting, relationships

### Service Layer

- [X] T050 [US3] Create `app/Services/LeadImportService.php` implementing `LeadImportServiceInterface`
- [X] T051 [US3] Implement `analyzeFile()` using League CSV Reader
- [X] T052 [US3] Implement `generatePreview()` with column mapping transformation
- [X] T053 [US3] Implement `import()` using Laravel LazyCollections and upsert for deduplication
- [X] T054 [US3] Implement `queueImport()` using Laravel Bus Batch for large files
- [X] T055 [US3] Implement `validateLead()` with email validation

### Jobs

- [X] T056 [US3] Create `app/Jobs/ImportLeadsJob.php` using Batchable trait

### Livewire Components

- [X] T057 [P] [US3] Create `app/Livewire/Lead/LeadList.php` with search, filter, bulk actions
- [X] T058 [P] [US3] Create `resources/views/livewire/lead/lead-list.blade.php` view
- [X] T059 [US3] Create `app/Livewire/Lead/LeadImport.php` with multi-step wizard (upload, map, preview, import)
- [X] T060 [US3] Create `resources/views/livewire/lead/lead-import.blade.php` view
- [X] T061 [P] [US3] Create `app/Livewire/Lead/LeadForm.php` for manual lead entry
- [X] T062 [P] [US3] Create `resources/views/livewire/lead/lead-form.blade.php` view

### Events

- [X] T063 [P] [US3] Create `app/Events/LeadsImported.php`
- [X] T064 [P] [US3] Create `app/Events/LeadStatusChanged.php`

### Routes

- [X] T065 [US3] Add lead routes in `routes/web.php` for list, import, create, edit views

**Checkpoint**: User Story 3 complete - users can import and manage leads

---

## Phase 6: User Story 4 - Create Email Sequence Templates (Priority: P1) 🎯 MVP

**Goal**: Enable multi-email sequences with personalization variables

**Independent Test**: Create 3-email sequence with variables, preview with sample data

### Database Layer

- [X] T066 [US4] Create migration `database/migrations/*_create_email_templates_table.php` with sequence order and delay fields
- [X] T067 [US4] Create `app/Models/EmailTemplate.php` with variable detection helper and relationships

### Livewire Components

- [X] T068 [US4] Create `app/Livewire/Template/TemplateEditor.php` with variable insertion and preview
- [X] T069 [US4] Create `resources/views/livewire/template/template-editor.blade.php` with rich text editor
- [X] T070 [US4] Create `app/Livewire/Template/SequenceBuilder.php` with drag-drop reordering
- [X] T071 [US4] Create `resources/views/livewire/template/sequence-builder.blade.php` view

### Events

- [X] T072 [P] [US4] Create `app/Events/TemplateCreated.php`
- [X] T073 [P] [US4] Create `app/Events/SequenceUpdated.php`

### Routes

- [X] T074 [US4] Add template routes in `routes/web.php` for editor and sequence builder views

**Checkpoint**: User Story 4 complete - users can create email sequences

---

## Phase 7: User Story 5 - Track and Match Reply Detection (Priority: P1) 🎯 MVP

**Goal**: Automatically detect and match replies to sent emails

**Independent Test**: Send email, receive reply, verify it matches to correct lead within 10 minutes

### Database Layer

- [X] T075 [US5] Create migration `database/migrations/*_create_sent_emails_table.php` with message_id, status, scheduled_at
- [X] T076 [US5] Create migration `database/migrations/*_create_responses_table.php` with raw content and AI fields
- [X] T077 [US5] Create migration `database/migrations/*_create_message_references_table.php` for threading
- [X] T078 [P] [US5] Create `app/Models/SentEmail.php` with status constants and relationships
- [X] T079 [P] [US5] Create `app/Models/Response.php` with AI field casting and relationships
- [X] T080 [P] [US5] Create `app/Models/MessageReference.php` for JWZ threading

### Service Layer

- [X] T081 [US5] Create `app/Services/SendEngineService.php` implementing `SendEngineServiceInterface`
- [X] T082 [US5] Implement `renderTemplate()` with variable interpolation using regex
- [X] T083 [US5] Implement `send()` using dynamic Symfony Mailer transport with Message-ID tracking
- [X] T084 [US5] Implement `queueCampaignEmails()` and `queueNextSequenceEmail()`
- [X] T085 [US5] Implement `scheduleEmailsForDay()` spreading sends through window
- [X] T086 [US5] Implement `cancelPendingEmails()` and `handleBounce()`
- [X] T087 [US5] Create `app/Services/ReplyDetectionService.php` implementing `ReplyDetectionServiceInterface`
- [X] T088 [US5] Implement `poll()` using Webklex IMAP with runtime config
- [X] T089 [US5] Implement `matchToSentEmail()` with In-Reply-To, References, subject fallback
- [X] T090 [US5] Implement `isAutoReply()` detecting OOO via headers and subject patterns
- [X] T091 [US5] Implement `isBounce()` detecting bounce notifications
- [X] T092 [US5] Implement `parseEmail()` using zbateson/mail-mime-parser
- [X] T093 [US5] Implement `getConversationThread()` for threaded display

### Jobs

- [X] T094 [P] [US5] Create `app/Jobs/SendEmailJob.php` with rate limiting middleware
- [X] T095 [P] [US5] Create `app/Jobs/PollMailboxJob.php` for IMAP polling

### Events

- [X] T096 [P] [US5] Create `app/Events/EmailSent.php`
- [X] T097 [P] [US5] Create `app/Events/EmailBounced.php`
- [X] T098 [P] [US5] Create `app/Events/EmailFailed.php`
- [X] T099 [P] [US5] Create `app/Events/ResponseReceived.php`

### Commands

- [X] T100 [US5] Create `app/Console/Commands/PollMailboxCommand.php` dispatching PollMailboxJob for all active mailboxes
- [X] T101 [US5] Create `app/Console/Commands/ScheduleEmailsCommand.php` for daily email scheduling

**Checkpoint**: User Story 5 complete - system detects and matches replies automatically

---

## Phase 8: User Story 6 - Review AI-Analyzed Responses (Priority: P2)

**Goal**: AI analyzes responses and extracts structured insights

**Independent Test**: Receive response, view AI-extracted fields (interest, problem confirmation, quotes)

### Service Layer

- [X] T102 [US6] Create `app/Services/AiAnalysisService.php` implementing `AiAnalysisServiceInterface`
- [X] T103 [US6] Implement `buildPrompt()` with response context and campaign hypothesis
- [X] T104 [US6] Implement `getAnalysisSchema()` returning JSON schema for structured output
- [X] T105 [US6] Implement `analyze()` calling Claude API with structured output
- [X] T106 [US6] Implement `queueAnalysis()` dispatching AnalyzeResponseJob
- [X] T107 [US6] Implement `batchReanalyze()` using Laravel Bus Batch

### Jobs

- [X] T108 [US6] Create `app/Jobs/AnalyzeResponseJob.php` with retry backoff

### Livewire Components

- [X] T109 [P] [US6] Create `app/Livewire/Response/ResponseInbox.php` with interest/status filters
- [X] T110 [P] [US6] Create `resources/views/livewire/response/response-inbox.blade.php` view
- [X] T111 [US6] Create `app/Livewire/Response/ResponseView.php` with AI fields and manual override
- [X] T112 [US6] Create `resources/views/livewire/response/response-view.blade.php` with thread display

### Events

- [X] T113 [P] [US6] Create `app/Events/ResponseAnalyzed.php`
- [X] T114 [P] [US6] Create `app/Events/AnalysisFailed.php`

### Event Listeners

- [X] T115 [US6] Create listener to dispatch AnalyzeResponseJob on ResponseReceived event

### Routes

- [X] T116 [US6] Add response routes in `routes/web.php` for inbox and detail views

**Checkpoint**: User Story 6 complete - AI analyzes responses with editable fields

---

## Phase 9: User Story 7 - View Campaign Insights and Decision Score (Priority: P2)

**Goal**: Aggregate insights with patterns, themes, quotes, and decision score

**Independent Test**: View campaign with 10+ responses, see metrics, patterns, decision score

### Database Layer

- [X] T117 [US7] Create migration `database/migrations/*_create_insights_table.php` with type enum and data JSON
- [X] T118 [US7] Create `app/Models/Insight.php` with type constants and data casting

### Service Layer

- [X] T119 [US7] Create `app/Services/InsightService.php` implementing `InsightServiceInterface`
- [X] T120 [US7] Implement `generateInsights()` orchestrating pattern, theme, quote detection
- [X] T121 [US7] Implement `detectPatterns()` using AI batch analysis
- [X] T122 [US7] Implement `detectThemes()` clustering similar responses
- [X] T123 [US7] Implement `detectObjections()` extracting negative patterns
- [X] T124 [US7] Implement `extractQuotes()` selecting notable quotes
- [X] T125 [US7] Implement `pinQuote()` and `unpinQuote()` for quote board

### Jobs

- [X] T126 [US7] Create `app/Jobs/GenerateInsightsJob.php`

### Livewire Components

- [X] T127 [US7] Create `app/Livewire/Campaign/CampaignInsights.php` with metrics, patterns, decision score
- [X] T128 [US7] Create `resources/views/livewire/campaign/campaign-insights.blade.php` view
- [X] T129 [P] [US7] Create `app/Livewire/Response/QuoteBoard.php` with pinned quotes
- [X] T130 [P] [US7] Create `resources/views/livewire/response/quote-board.blade.php` view

### Events

- [X] T131 [US7] Create `app/Events/InsightsGenerated.php`

### Commands

- [X] T132 [US7] Create `app/Console/Commands/GenerateInsightsCommand.php` for hourly insight generation

**Checkpoint**: User Story 7 complete - users see aggregated insights and decision scores

---

## Phase 10: User Story 8 - Book Discovery Calls (Priority: P3)

**Goal**: Convert interested respondents into scheduled calls

**Independent Test**: Click "Book Call" from response, insert scheduling link, track outcome

### Database Layer

- [X] T133 [US8] Create migration `database/migrations/*_create_call_bookings_table.php` with outcome enum
- [X] T134 [US8] Create `app/Models/CallBooking.php` with outcome constants and relationships

### Livewire Components

- [X] T135 [US8] Add `bookCall()` method to `app/Livewire/Response/ResponseView.php`
- [X] T136 [US8] Create call booking modal in `resources/views/livewire/response/response-view.blade.php`
- [X] T137 [US8] Add call tracking UI to lead detail view

### Routes

- [X] T138 [US8] Add call booking routes in `routes/web.php`

**Checkpoint**: User Story 8 complete - users can book and track discovery calls

---

## Phase 11: User Story 9 - Reply to Responses from Platform (Priority: P3)

**Goal**: Send replies directly from the platform maintaining conversation context

**Independent Test**: Compose reply from response view, send, verify thread updates

### Livewire Components

- [X] T139 [US9] Add reply composer to `app/Livewire/Response/ResponseView.php`
- [X] T140 [US9] Add reply form UI to `resources/views/livewire/response/response-view.blade.php`
- [X] T141 [US9] Implement send reply using SendEngineService with proper threading headers

### Service Layer Updates

- [X] T142 [US9] Add `sendReply()` method to `app/Services/SendEngineService.php` handling In-Reply-To and References

**Checkpoint**: User Story 9 complete - users can reply to responses from platform

---

## Phase 12: User Story 10 - Monitor Dashboard and Mailbox Health (Priority: P3)

**Goal**: Central dashboard with campaign summaries, activity, and mailbox health

**Independent Test**: Log in, view active campaigns, recent responses, metrics, mailbox status

### Livewire Components

- [X] T143 [US10] Create `app/Livewire/Dashboard/Dashboard.php` with active campaigns, responses, metrics
- [X] T144 [US10] Create `resources/views/livewire/dashboard/dashboard.blade.php` view
- [X] T145 [US10] Add weekly metrics aggregation (contacts, replies, response rate trend, calls booked)
- [X] T146 [US10] Add mailbox health summary with warnings

### Routes

- [X] T147 [US10] Add dashboard route in `routes/web.php` as home/default view

**Checkpoint**: User Story 10 complete - users have operational dashboard

---

## Phase 13: Polish & Cross-Cutting Concerns

**Purpose**: Improvements affecting multiple user stories

- [X] T148 [P] Run code style fixes with `sail pint`
- [X] T149 [P] Add feature tests for Mailbox CRUD in `tests/Feature/Mailbox/`
- [X] T150 [P] Add feature tests for Campaign CRUD in `tests/Feature/Campaign/`
- [X] T151 [P] Add feature tests for Lead import in `tests/Feature/Lead/`
- [X] T152 [P] Add component tests for Livewire components in `tests/Feature/Livewire/`
- [X] T153 [P] Add unit tests for services in `tests/Unit/Services/`
- [X] T154 Run `sail artisan test` and fix any failures
- [X] T155 Run quickstart.md validation - verify all setup steps work
- [X] T156 Performance review: ensure dashboard loads <2s, imports 1000 leads <30s

**Checkpoint**: Phase 13 complete - Discovery Engine implementation complete

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3-12)**: All depend on Foundational phase completion
  - P1 stories (US1-US5) can proceed in parallel after Foundation
  - P2 stories (US6-US7) can proceed after Foundation (independent of P1)
  - P3 stories (US8-US10) can proceed after Foundation
- **Polish (Phase 13)**: Depends on all user stories being complete

### User Story Dependencies

- **US1 (Mailbox)**: Foundation only - no story dependencies
- **US2 (Campaign)**: Foundation only - uses Mailbox relationship but can be implemented independently
- **US3 (Leads)**: Foundation + Campaign model exists
- **US4 (Templates)**: Foundation + Campaign model exists
- **US5 (Reply Detection)**: Foundation + Mailbox + Campaign + Lead + Template models exist
- **US6 (AI Analysis)**: Foundation + Response model from US5
- **US7 (Insights)**: Foundation + Response model + AI fields from US6
- **US8 (Call Booking)**: Foundation + Response model from US5
- **US9 (Reply)**: Foundation + SendEngineService from US5
- **US10 (Dashboard)**: Foundation + All models for metrics aggregation

### Within Each User Story

- Migrations before models
- Models before services
- Services before Livewire components
- Events can be created in parallel
- Views after their Livewire components

### Parallel Opportunities

- All tasks marked [P] within a phase can run in parallel
- After Foundation, different developers can work on different user stories:
  - Developer A: US1 (Mailbox) → US5 (Reply Detection)
  - Developer B: US2 (Campaign) → US7 (Insights)
  - Developer C: US3 (Leads) → US4 (Templates)

---

## Parallel Example: Phase 3 (US1 - Mailbox)

```bash
# Launch all parallel model/event tasks together:
Task: "Create MailboxList.php"
Task: "Create MailboxHealth.php"
Task: "Create MailboxCreated event"
Task: "Create MailboxUpdated event"
Task: "Create MailboxErrorOccurred event"
```

---

## Implementation Strategy

### MVP First (P1 Stories Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL)
3. Complete Phase 3: US1 - Connect Email Account
4. Complete Phase 4: US2 - Create Campaign
5. Complete Phase 5: US3 - Import Leads
6. Complete Phase 6: US4 - Email Templates
7. Complete Phase 7: US5 - Reply Detection
8. **STOP and VALIDATE**: Test P1 stories independently
9. Deploy/demo MVP

### Incremental Delivery

1. Setup + Foundational → Foundation ready
2. Add US1 → Users can connect mailboxes (Demo 1)
3. Add US2-US4 → Users can create campaigns with leads and templates (Demo 2)
4. Add US5 → System sends emails and detects replies (Demo 3 - MVP Complete)
5. Add US6-US7 → AI analysis and insights (Demo 4)
6. Add US8-US10 → Polish features (Demo 5 - Full Feature)

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story
- Each user story should be independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- All credentials MUST use `encrypt()` before storage (Constitution V)
- All async operations MUST use Redis queues (Constitution III)
