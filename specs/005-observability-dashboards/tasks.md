# Tasks: Frontend & Backend Observability with Grafana Dashboards

**Input**: Design documents from `/specs/005-observability-dashboards/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: Constitution requires feature tests for HTTP endpoints (IV. Code Style). Test tasks included per constitution compliance.

**Organization**: Tasks grouped by user story (P1-P5) to enable independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: US1-US5 maps to User Stories from spec.md
- Include exact file paths in descriptions

## Path Conventions (Laravel)

- **Backend**: `app/`, `config/`, `routes/`
- **Frontend JS**: `resources/js/`
- **Tests**: `tests/Feature/`, `tests/Unit/`
- **Docker**: `docker/`
- Paths follow plan.md structure

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Install dependencies and configure Docker services for observability stack

- [x] T001 Install spatie/laravel-prometheus package via `sail composer require spatie/laravel-prometheus`
- [x] T002 Install web-vitals npm package via `npm install web-vitals`
- [x] T003 [P] Create docker/prometheus/ directory structure
- [x] T004 [P] Create docker/loki/ directory structure
- [x] T005 [P] Create docker/alloy/ directory structure
- [x] T006 [P] Create docker/grafana/provisioning/datasources/ directory structure
- [x] T007 [P] Create docker/grafana/provisioning/dashboards/ directory structure
- [x] T008 [P] Create docker/grafana/dashboards/ directory structure
- [x] T009 Create Prometheus configuration in docker/prometheus/prometheus.yml
- [x] T010 Create Loki configuration in docker/loki/loki-config.yml
- [x] T011 Create Alloy configuration in docker/alloy/config.alloy
- [x] T012 Create Grafana datasources configuration in docker/grafana/provisioning/datasources/datasources.yaml
- [x] T013 Create Grafana dashboards provisioning in docker/grafana/provisioning/dashboards/dashboards.yaml
- [x] T014 Update compose.yaml to add prometheus, loki, alloy, and grafana services

**Checkpoint**: Docker infrastructure ready - `sail up` should start all observability services

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core observability infrastructure that ALL user stories depend on

**CRITICAL**: No user story work can begin until this phase is complete

- [x] T015 Create config/prometheus.php configuration file
- [x] T016 Create app/Providers/ObservabilityServiceProvider.php with feature bootstrap
- [x] T017 Register ObservabilityServiceProvider in bootstrap/providers.php
- [x] T018 [P] Create app/Logging/JsonFormatter.php for structured JSON log output
- [x] T019 [P] Create app/Logging/PiiRedactionProcessor.php for sensitive data removal
- [x] T020 Update config/logging.php to add JSON channel with PII redaction for Loki
- [x] T021 Create app/Http/Middleware/CorrelationIdMiddleware.php for request tracing
- [x] T022 Register CorrelationIdMiddleware as global middleware in bootstrap/app.php
- [x] T023 Create tests/Feature/Middleware/CorrelationIdMiddlewareTest.php
- [x] T024 Create tests/Unit/Logging/PiiRedactionProcessorTest.php
- [ ] T025 Verify observability stack is running via `sail up` and check http://localhost:3000 (Grafana)

**Checkpoint**: Foundation ready - structured logging with correlation IDs working, Grafana accessible

---

## Phase 3: User Story 1 - Monitor Email Campaign Flow Health (Priority: P1)

**Goal**: Real-time dashboard showing email pipeline: queue → send → poll → analyze

**Independent Test**: Run a test campaign and verify all stages appear in Campaign Flow dashboard with accurate counts

### Tests for User Story 1

- [x] T026 [P] [US1] Create tests/Feature/Observability/MetricsEndpointTest.php for /metrics endpoint

### Implementation for User Story 1

- [x] T027 [P] [US1] Create app/Metrics/Collectors/CampaignFlowCollector.php with queue/send/poll/analyze metrics
- [x] T028 [US1] Register CampaignFlowCollector in app/Providers/ObservabilityServiceProvider.php
- [x] T029 [US1] Add metric instrumentation to app/Jobs/SendEmailJob.php (email_sent_total, send_duration)
- [x] T030 [US1] Add metric instrumentation to app/Jobs/PollMailboxJob.php (replies_detected, polling_duration)
- [x] T031 [US1] Add metric instrumentation to app/Jobs/AnalyzeResponseJob.php (analysis_total, analysis_duration)
- [x] T032 [US1] Add metric instrumentation to app/Services/SendEngineService.php (email_queued_total, queue_depth)
- [x] T033 [US1] Add metric instrumentation to app/Services/ReplyDetectionService.php (polling_messages_total)
- [x] T034 [US1] Create docker/grafana/dashboards/campaign-flow.json with flow visualization panels
- [ ] T035 [US1] Verify Campaign Flow dashboard shows data at http://localhost:3000

**Checkpoint**: User Story 1 complete - Campaign Flow Health dashboard operational with real metrics

---

## Phase 4: User Story 2 - Track Application Performance Metrics (Priority: P2)

**Goal**: Backend performance dashboard with request latency, queue metrics, database performance

**Independent Test**: Generate load on application and verify response times, queue depths appear in Performance dashboard

### Tests for User Story 2

- [x] T036 [P] [US2] Add request metrics tests to tests/Feature/Observability/MetricsEndpointTest.php

### Implementation for User Story 2

- [x] T037 [P] [US2] Create app/Http/Middleware/RequestMetricsMiddleware.php for HTTP metrics
- [x] T038 [US2] Register RequestMetricsMiddleware as global middleware in bootstrap/app.php
- [x] T039 [P] [US2] Create app/Metrics/Collectors/HttpRequestCollector.php for request latency metrics
- [x] T040 [P] [US2] Create app/Metrics/Collectors/QueueJobCollector.php for job processing metrics
- [x] T041 [US2] Register HttpRequestCollector and QueueJobCollector in ObservabilityServiceProvider
- [x] T042 [US2] Add database query listener in ObservabilityServiceProvider for db_queries metrics
- [x] T043 [US2] Create docker/grafana/dashboards/backend-performance.json with latency/queue/db panels
- [ ] T044 [US2] Verify Backend Performance dashboard shows data at http://localhost:3000

**Checkpoint**: User Story 2 complete - Backend Performance dashboard operational

---

## Phase 5: User Story 3 - Monitor Frontend User Experience (Priority: P3)

**Goal**: Frontend dashboard with Core Web Vitals, page load times, JS errors, Livewire metrics

**Independent Test**: Load application pages and verify page metrics, Core Web Vitals appear in Frontend dashboard

### Tests for User Story 3

- [x] T045 [P] [US3] Create tests/Feature/Observability/WebVitalsEndpointTest.php
- [x] T046 [P] [US3] Create tests/Feature/Observability/JsErrorEndpointTest.php

### Implementation for User Story 3

- [x] T047 [P] [US3] Create app/Http/Requests/WebVitalsRequest.php with validation rules
- [x] T048 [P] [US3] Create app/Http/Requests/JsErrorRequest.php with validation rules
- [x] T049 [P] [US3] Create app/Http/Requests/LivewireMetricsRequest.php with validation rules
- [x] T050 [P] [US3] Create app/Http/Requests/PageLoadRequest.php with validation rules
- [x] T051 [US3] Create app/Http/Controllers/Api/ObservabilityController.php with all endpoints
- [x] T052 [US3] Add observability API routes to routes/api.php with rate limiting
- [x] T053 [P] [US3] Create app/Metrics/Collectors/FrontendCollector.php for web vitals aggregation
- [x] T054 [US3] Register FrontendCollector in ObservabilityServiceProvider
- [x] T055 [P] [US3] Create resources/js/observability/web-vitals.js with Core Web Vitals collection
- [x] T056 [P] [US3] Create resources/js/observability/error-tracking.js for JS error capture
- [x] T057 [P] [US3] Create resources/js/observability/livewire-metrics.js for component performance
- [x] T058 [US3] Create resources/js/observability/index.js to export and initialize all modules
- [x] T059 [US3] Update resources/js/app.js to import observability modules
- [x] T060 [US3] Run `npm run build` to compile frontend assets
- [x] T061 [US3] Create docker/grafana/dashboards/frontend-performance.json with Core Web Vitals panels
- [ ] T062 [US3] Verify Frontend Performance dashboard shows data at http://localhost:3000

**Checkpoint**: User Story 3 complete - Frontend Performance dashboard operational with browser metrics

---

## Phase 6: User Story 4 - Review Admin Activity Audit Trail (Priority: P4)

**Goal**: Admin activity dashboard visualizing existing AdminAuditLog data over time

**Independent Test**: Perform admin actions (impersonation, role changes) and verify they appear in Admin Activity dashboard

### Implementation for User Story 4

- [x] T063 [P] [US4] Create app/Metrics/Collectors/AdminActivityCollector.php for admin action metrics
- [x] T064 [US4] Register AdminActivityCollector in ObservabilityServiceProvider
- [x] T065 [US4] Add metric instrumentation to app/Services/AuditLogService.php (admin_actions_total)
- [x] T066 [US4] Add impersonation metric tracking to app/Traits/HandlesImpersonation.php
- [x] T067 [US4] Create docker/grafana/dashboards/admin-activity.json with audit timeline panels
- [ ] T068 [US4] Verify Admin Activity dashboard shows data at http://localhost:3000

**Checkpoint**: User Story 4 complete - Admin Activity dashboard operational

---

## Phase 7: User Story 5 - View Centralized Application Logs (Priority: P5)

**Goal**: Centralized log search in Grafana with filtering by correlation ID, log level, time range

**Independent Test**: Trigger application events and verify logs appear in Grafana Explore with searchability

### Implementation for User Story 5

- [x] T069 [US5] Verify Loki datasource is configured and working in Grafana
- [x] T070 [US5] Add structured logging with correlation ID to app/Http/Middleware/CorrelationIdMiddleware.php for request start/end
- [x] T071 [US5] Add campaign_id context to logs in app/Jobs/SendEmailJob.php
- [x] T072 [US5] Add campaign_id context to logs in app/Jobs/PollMailboxJob.php
- [x] T073 [US5] Add campaign_id context to logs in app/Jobs/AnalyzeResponseJob.php
- [x] T074 [US5] Document LogQL query examples in specs/005-observability-dashboards/quickstart.md
- [ ] T075 [US5] Verify log search works in Grafana Explore at http://localhost:3000/explore

**Checkpoint**: User Story 5 complete - Centralized logging with correlation ID search operational

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Final improvements, documentation, and validation

- [x] T076 [P] Update specs/005-observability-dashboards/quickstart.md with actual endpoint URLs
- [x] T077 [P] Add graceful degradation for observability unavailability in ObservabilityServiceProvider
- [x] T078 Run `sail artisan test` to verify all tests pass
- [x] T079 Run `sail pint` to verify code style
- [x] T080 Verify all dashboards load without errors at http://localhost:3000
- [x] T081 Test graceful degradation by stopping prometheus container and verifying app still works
- [x] T082 Document dashboard access in project README or quickstart.md

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup - BLOCKS all user stories
- **User Stories (Phases 3-7)**: All depend on Foundational (Phase 2)
  - US1-US5 can proceed in parallel after Foundational
  - Or sequentially in priority order (P1 → P2 → P3 → P4 → P5)
- **Polish (Phase 8)**: Depends on all user stories being complete

### User Story Dependencies

| Story | Depends On | Can Start After | Notes |
|-------|------------|-----------------|-------|
| US1 (Campaign Flow) | Foundational | Phase 2 complete | Core flow - MVP |
| US2 (Backend Perf) | Foundational | Phase 2 complete | Independent of US1 |
| US3 (Frontend) | Foundational | Phase 2 complete | Independent - has API endpoints |
| US4 (Admin Activity) | Foundational | Phase 2 complete | Uses existing AuditLogService |
| US5 (Logs) | Foundational | Phase 2 complete | Enhances correlation logging |

### Within Each User Story

1. Tests MUST be written and FAIL before implementation (Constitution IV)
2. Collectors before instrumentation
3. Instrumentation before dashboards
4. Verify dashboard shows data before marking story complete

### Parallel Opportunities

**Phase 1 (Setup)**:
- T003-T008: All directory creation tasks
- T009-T013: Configuration files (different files)

**Phase 2 (Foundational)**:
- T018-T019: JsonFormatter and PiiRedactionProcessor (different files)
- T023-T024: Tests (different files)

**Phase 3 (US1)**:
- T026-T027: Test and collector (different files)

**Phase 4 (US2)**:
- T036, T039-T040: Test and collectors (different files)

**Phase 5 (US3)**:
- T045-T050: All Form Requests (different files)
- T055-T057: All JS modules (different files)

**Phase 6 (US4)**:
- T063: Collector (independent file)

**Cross-Story Parallel**:
- After Foundational (Phase 2), US1-US5 can proceed in parallel with multiple developers

---

## Parallel Example: User Story 3 (Frontend)

```bash
# Launch all tests in parallel:
Task: "Create tests/Feature/Observability/WebVitalsEndpointTest.php"
Task: "Create tests/Feature/Observability/JsErrorEndpointTest.php"

# Launch all Form Requests in parallel:
Task: "Create app/Http/Requests/WebVitalsRequest.php"
Task: "Create app/Http/Requests/JsErrorRequest.php"
Task: "Create app/Http/Requests/LivewireMetricsRequest.php"
Task: "Create app/Http/Requests/PageLoadRequest.php"

# Launch all JS modules in parallel:
Task: "Create resources/js/observability/web-vitals.js"
Task: "Create resources/js/observability/error-tracking.js"
Task: "Create resources/js/observability/livewire-metrics.js"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (Docker infrastructure)
2. Complete Phase 2: Foundational (logging, correlation IDs)
3. Complete Phase 3: User Story 1 (Campaign Flow Health)
4. **STOP and VALIDATE**: Run test campaign, verify dashboard
5. Deploy/demo Campaign Flow dashboard

### Incremental Delivery

1. Setup + Foundational → Observability infrastructure ready
2. Add US1 (Campaign Flow) → **MVP deployed**
3. Add US2 (Backend Perf) → Performance visibility
4. Add US3 (Frontend) → Full-stack observability
5. Add US4 (Admin Activity) → Security monitoring
6. Add US5 (Logs) → Debug capability complete
7. Each story adds dashboards without breaking previous ones

### Parallel Team Strategy

With 3 developers after Foundational:
- Developer A: US1 (Campaign Flow) + US4 (Admin Activity)
- Developer B: US2 (Backend Perf) + US5 (Logs)
- Developer C: US3 (Frontend)

---

## Notes

- [P] tasks = different files, no dependencies on incomplete tasks in same phase
- [US1-US5] label maps task to specific user story
- Each user story produces a complete, functional dashboard
- Constitution requires feature tests for all HTTP endpoints
- Verify Grafana dashboards after each story phase
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
