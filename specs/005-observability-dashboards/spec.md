# Feature Specification: Frontend & Backend Observability with Grafana Dashboards

**Feature Branch**: `005-observability-dashboards`
**Created**: 2025-12-09
**Status**: Draft
**Input**: User description: "Within the application I want to add frontend observability as well as backend observability with grafana dashboards and ingestion of logs etc. The grafana work should create dashboards that contribute towards watching the flows in the way they are designed"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Monitor Email Campaign Flow Health (Priority: P1)

As an operator, I want to view real-time metrics about the email discovery campaign flow so that I can identify when campaigns are processing correctly and quickly detect any issues with email sending, reply detection, or AI analysis.

**Why this priority**: The email campaign discovery engine is the core business value of the application. If emails aren't being sent, replies aren't being detected, or AI analysis is failing, the entire product stops delivering value. This dashboard provides critical visibility into the primary revenue-generating workflow.

**Independent Test**: Can be fully tested by running a test campaign and verifying all stages (queue, send, poll, analyze) appear in the dashboard with accurate counts and latencies.

**Acceptance Scenarios**:

1. **Given** a campaign is actively sending emails, **When** I open the Campaign Flow dashboard, **Then** I see real-time counts of emails queued, sent, failed, and bounced in the last hour/day
2. **Given** the reply detection service is polling mailboxes, **When** I view the dashboard, **Then** I see the number of replies detected, matched, and pending analysis with their average latency
3. **Given** the AI analysis service is processing responses, **When** I check the dashboard, **Then** I see analysis completion rates, average processing time, and any failed analyses
4. **Given** a component in the flow is experiencing errors, **When** the error rate exceeds normal thresholds, **Then** the dashboard visually indicates the problem area with highlighted metrics

---

### User Story 2 - Track Application Performance Metrics (Priority: P2)

As an operator, I want to monitor backend performance including request latency, database query performance, and queue processing rates so that I can ensure the application meets performance expectations and identify bottlenecks.

**Why this priority**: Performance degradation directly impacts user experience and can indicate underlying issues before they become critical failures. This enables proactive maintenance and capacity planning.

**Independent Test**: Can be tested by generating load on the application and verifying response times, queue depths, and database metrics appear accurately in the dashboard.

**Acceptance Scenarios**:

1. **Given** users are interacting with the application, **When** I view the Performance dashboard, **Then** I see average response times by endpoint grouped by route category
2. **Given** background jobs are processing, **When** I check the Queues panel, **Then** I see queue depth, processing rate, wait time, and failure rate for each job type
3. **Given** the database is under load, **When** I view database metrics, **Then** I see query counts, slow query frequency, and connection pool utilization
4. **Given** an endpoint is experiencing slow responses, **When** latency exceeds baseline thresholds, **Then** I can drill down to identify the specific routes affected

---

### User Story 3 - Monitor Frontend User Experience (Priority: P3)

As an operator, I want to see frontend performance metrics including page load times, JavaScript errors, and user interaction patterns so that I can ensure a smooth user experience and quickly identify client-side issues.

**Why this priority**: Frontend issues directly impact user perception and can cause users to abandon the application. Understanding client-side performance complements backend monitoring for full visibility.

**Independent Test**: Can be tested by loading application pages and verifying page load metrics, Core Web Vitals, and any JavaScript errors appear in the dashboard.

**Acceptance Scenarios**:

1. **Given** users are loading application pages, **When** I view the Frontend dashboard, **Then** I see page load times broken down by page type (dashboard, campaign, leads, etc.)
2. **Given** JavaScript errors are occurring in the browser, **When** errors are captured, **Then** they appear in the dashboard with error message, stack trace context, browser, and page URL
3. **Given** users are interacting with Livewire components, **When** I check component metrics, **Then** I see component render times and any failed component updates
4. **Given** the landing page animations are running, **When** I view landing page metrics, **Then** I see frame rate performance and time-to-interactive measurements

---

### User Story 4 - Review Admin Activity Audit Trail (Priority: P4)

As an operator, I want to see admin actions visualized over time so that I can audit security-sensitive operations and identify unusual patterns in admin behavior.

**Why this priority**: Security monitoring is essential for compliance and detecting unauthorized access. The existing AdminAuditLog data should be made visible through dashboards.

**Independent Test**: Can be tested by performing admin actions (impersonation, role changes) and verifying they appear in the audit dashboard with correct details.

**Acceptance Scenarios**:

1. **Given** admins are performing actions in the system, **When** I view the Admin Activity dashboard, **Then** I see a timeline of admin actions with action type, admin user, target, and timestamp
2. **Given** an admin starts impersonating a user, **When** I check the impersonation panel, **Then** I see active impersonation sessions with duration and originating admin
3. **Given** role changes have been made, **When** I filter by role change actions, **Then** I see a history of all privilege escalations and demotions
4. **Given** unusual admin activity patterns occur, **When** action frequency exceeds normal baselines, **Then** the dashboard highlights the anomaly

---

### User Story 5 - View Centralized Application Logs (Priority: P5)

As an operator, I want to search and filter application logs from a central location so that I can troubleshoot issues by correlating logs across different services and time periods.

**Why this priority**: Centralized logging enables efficient debugging and root cause analysis. While lower priority than dashboards, it provides the detailed data needed when investigating issues discovered through dashboards.

**Independent Test**: Can be tested by triggering application events and verifying the logs appear in the centralized log viewer with proper structure and searchability.

**Acceptance Scenarios**:

1. **Given** the application is generating logs, **When** I access the log viewer, **Then** I can search logs by timestamp, log level, message content, or correlation ID
2. **Given** a specific campaign is having issues, **When** I filter logs by campaign ID, **Then** I see all related logs from send, poll, and analysis operations
3. **Given** an error occurred, **When** I search for errors in a time range, **Then** I see error logs with full context including stack traces and request details
4. **Given** I need to trace a request through the system, **When** I search by correlation ID, **Then** I see all logs from that single request across all components

---

### Edge Cases

- What happens when the logging/metrics collection service is unavailable? (Application continues operating without observability, graceful degradation)
- How does the system handle high log volume during traffic spikes? (Rate limiting and sampling strategies for metrics)
- What happens when Grafana is unreachable? (Logs and metrics continue to be collected; dashboards unavailable temporarily)
- How are sensitive data fields handled in logs? (PII redaction for email addresses, passwords never logged)
- What happens when storage for logs reaches capacity? (Retention policies with automatic cleanup of oldest data)

## Requirements *(mandatory)*

### Functional Requirements

**Backend Observability:**

- **FR-001**: System MUST collect and export structured logs from all application components including HTTP requests, background jobs, and scheduled tasks
- **FR-002**: System MUST capture metrics for email campaign flow stages: queued, sent, failed, bounced, reply detected, analysis pending, analysis complete, analysis failed
- **FR-003**: System MUST record request latency metrics for all HTTP endpoints with route-level granularity
- **FR-004**: System MUST track queue metrics including depth, processing rate, wait time, and failure rate per job type
- **FR-005**: System MUST log all admin actions with the same detail captured by existing AdminAuditLog (admin ID, target, action type, IP, user agent, timestamp, changes)
- **FR-006**: System MUST include correlation IDs in all logs to enable request tracing across components
- **FR-007**: System MUST capture error events with full context including stack traces, request parameters, and user context

**Frontend Observability:**

- **FR-008**: System MUST capture page load performance metrics (load time, DOM ready, fully loaded) for all application pages
- **FR-009**: System MUST capture Core Web Vitals (LCP, FID, CLS) for frontend performance monitoring
- **FR-010**: System MUST capture and report JavaScript errors including error message, stack trace, browser information, and page URL
- **FR-011**: System MUST track Livewire component performance including render times and failed updates
- **FR-012**: System MUST capture landing page animation performance (frame rate, time-to-interactive)

**Dashboard Requirements:**

- **FR-013**: System MUST provide a Campaign Flow Health dashboard showing email pipeline metrics aligned with the discovery engine workflow
- **FR-014**: System MUST provide a Backend Performance dashboard showing request latency, queue metrics, and database performance
- **FR-015**: System MUST provide a Frontend Performance dashboard showing page load times, errors, and Core Web Vitals
- **FR-016**: System MUST provide an Admin Activity dashboard visualizing audit log data over time
- **FR-017**: System MUST provide a centralized log search interface with filtering by time range, log level, service, and correlation ID
- **FR-018**: Dashboards MUST support time range selection for viewing historical data

**Data Management:**

- **FR-019**: System MUST redact sensitive data (passwords, API keys) from all logs before storage
- **FR-020**: System MUST implement log retention with automatic cleanup after the retention period
- **FR-021**: System MUST continue operating normally if observability infrastructure is temporarily unavailable

### Key Entities

- **Log Entry**: Structured log record with timestamp, level, message, context fields, correlation ID, and source service
- **Metric**: Time-series data point with name, value, labels/dimensions, and timestamp
- **Dashboard**: Collection of panels visualizing related metrics and logs with configurable time ranges
- **Correlation ID**: Unique identifier that follows a request through all system components for tracing
- **Alert Rule**: Condition definition that triggers notifications when metrics exceed thresholds (future enhancement)

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Operators can identify which stage of the email campaign flow is experiencing issues within 60 seconds of viewing the Campaign Flow dashboard
- **SC-002**: Mean time to identify the root cause of backend performance issues decreases by 50% compared to file-based log searching
- **SC-003**: Frontend errors are detected and visible in dashboards within 5 minutes of occurrence
- **SC-004**: 100% of admin actions appear in the Admin Activity dashboard matching AdminAuditLog records
- **SC-005**: Operators can search and retrieve relevant logs within 30 seconds using the centralized log interface
- **SC-006**: Dashboards accurately reflect the designed application flows as documented in feature specifications
- **SC-007**: Application performance is not degraded by more than 5% due to observability instrumentation overhead
- **SC-008**: Log and metric data is retained for at least 7 days for operational review

## Assumptions

- Grafana will be used as the visualization layer for dashboards
- The infrastructure for log ingestion and metric storage will be provisioned as part of this feature
- Existing AdminAuditLog data will be exported to the observability stack rather than duplicated
- The application already uses Laravel's logging facilities which will be extended with structured logging
- Dashboard access will be limited to operators/admins (not end users)
- The observability infrastructure will run alongside the application (same environment or dedicated observability stack)
- Initial retention period of 7 days is acceptable; longer retention can be configured later
- Standard sampling rates for high-volume metrics are acceptable to manage storage costs
