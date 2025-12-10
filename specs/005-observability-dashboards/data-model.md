# Data Model: Frontend & Backend Observability

**Feature Branch**: `005-observability-dashboards`
**Date**: 2025-12-09

## Overview

This document defines the data entities for the observability feature. Note that most observability data is stored in external systems (Prometheus for metrics, Loki for logs), not in MySQL. This document covers both the external data models and any application-level entities needed.

---

## External Data Models

### Prometheus Metrics (Time-Series)

Metrics are stored in Prometheus as time-series data points. These are not database entities but define the metric structure.

#### Campaign Flow Metrics

```yaml
# Email Queue Metrics
groundwork_email_queue_depth:
  type: gauge
  labels: [campaign_id, mailbox_id]
  description: Current number of emails waiting in queue

groundwork_email_queued_total:
  type: counter
  labels: [campaign_id, mailbox_id]
  description: Total emails added to queue

groundwork_email_sent_total:
  type: counter
  labels: [campaign_id, mailbox_id, status]  # status: success, failed, bounced
  description: Total emails sent with outcome

groundwork_email_send_duration_seconds:
  type: histogram
  labels: [campaign_id, mailbox_id]
  buckets: [0.1, 0.5, 1, 2, 5, 10, 30]
  description: Email send duration distribution

# Reply Detection Metrics
groundwork_replies_detected_total:
  type: counter
  labels: [campaign_id, matched]  # matched: true, false
  description: Total replies detected from mailbox polling

groundwork_polling_duration_seconds:
  type: histogram
  labels: [mailbox_id]
  buckets: [1, 5, 10, 30, 60, 120]
  description: Mailbox polling duration distribution

# AI Analysis Metrics
groundwork_analysis_total:
  type: counter
  labels: [campaign_id, status]  # status: completed, failed
  description: Total AI analysis jobs

groundwork_analysis_duration_seconds:
  type: histogram
  labels: [campaign_id]
  buckets: [1, 5, 10, 30, 60]
  description: AI analysis duration distribution
```

#### HTTP Request Metrics

```yaml
groundwork_http_requests_total:
  type: counter
  labels: [method, route, status_code]
  description: Total HTTP requests

groundwork_http_request_duration_seconds:
  type: histogram
  labels: [method, route]
  buckets: [0.01, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5, 10]
  description: HTTP request duration distribution
```

#### Queue Job Metrics

```yaml
groundwork_queue_jobs_total:
  type: counter
  labels: [job, queue, status]  # status: processed, failed
  description: Total queue jobs processed

groundwork_queue_job_duration_seconds:
  type: histogram
  labels: [job, queue]
  buckets: [0.1, 0.5, 1, 5, 10, 30, 60, 300]
  description: Queue job duration distribution

groundwork_queue_depth:
  type: gauge
  labels: [queue]
  description: Current queue depth by queue name
```

#### Frontend Metrics

```yaml
groundwork_web_vitals_lcp_seconds:
  type: histogram
  labels: [page, device_type]
  buckets: [0.5, 1, 1.5, 2, 2.5, 3, 4, 5]
  description: Largest Contentful Paint distribution

groundwork_web_vitals_fid_seconds:
  type: histogram
  labels: [page, device_type]
  buckets: [0.01, 0.05, 0.1, 0.2, 0.3, 0.5]
  description: First Input Delay distribution

groundwork_web_vitals_cls:
  type: histogram
  labels: [page, device_type]
  buckets: [0.01, 0.05, 0.1, 0.15, 0.2, 0.25]
  description: Cumulative Layout Shift distribution

groundwork_js_errors_total:
  type: counter
  labels: [page, error_type]
  description: Total JavaScript errors captured

groundwork_page_load_duration_seconds:
  type: histogram
  labels: [page]
  buckets: [0.5, 1, 2, 3, 5, 10]
  description: Full page load duration distribution
```

---

### Loki Log Schema

Logs are stored in Loki as structured JSON with indexed labels.

#### Log Entry Structure

```json
{
  "timestamp": "2025-12-09T14:30:00.000Z",
  "level": "info|warning|error|debug",
  "message": "Human-readable log message",
  "context": {
    "correlation_id": "uuid-v4",
    "user_id": 123,
    "campaign_id": "uuid-v4",
    "mailbox_id": "uuid-v4",
    "job_id": "uuid-v4",
    "request_id": "uuid-v4",
    "route": "campaigns.show",
    "method": "GET",
    "url": "/campaigns/123",
    "ip": "192.168.1.1",
    "user_agent": "Mozilla/5.0...",
    "duration_ms": 150,
    "memory_mb": 32.5
  },
  "exception": {
    "class": "App\\Exceptions\\MailboxConnectionException",
    "message": "Failed to connect to IMAP server",
    "code": 500,
    "file": "/var/www/html/app/Services/MailboxService.php",
    "line": 145,
    "trace": ["...truncated stack trace..."]
  }
}
```

#### Loki Labels (Indexed)

| Label | Description | Values |
|-------|-------------|--------|
| `job` | Log source identifier | `laravel`, `worker`, `scheduler` |
| `level` | Log severity | `debug`, `info`, `warning`, `error` |
| `service` | Application service | `groundwork` |
| `environment` | Deployment environment | `local`, `staging`, `production` |

---

## Application Data Models

### WebVitalsMetric (Value Object)

Transient object for receiving frontend metrics. Not persisted to database.

```php
class WebVitalsMetric
{
    public string $name;        // 'LCP', 'FID', 'CLS', 'INP', 'TTFB'
    public float $value;        // Metric value
    public string $page;        // Page identifier
    public string $deviceType;  // 'desktop', 'mobile', 'tablet'
    public ?string $userId;     // Optional authenticated user
    public string $sessionId;   // Browser session identifier
    public string $timestamp;   // ISO 8601 timestamp
}
```

### JsError (Value Object)

Transient object for receiving JavaScript errors. Not persisted to database.

```php
class JsError
{
    public string $message;     // Error message
    public string $source;      // Source file URL
    public int $lineno;         // Line number
    public int $colno;          // Column number
    public ?string $stack;      // Stack trace if available
    public string $page;        // Page URL
    public string $browser;     // Browser user agent
    public ?string $userId;     // Optional authenticated user
    public string $timestamp;   // ISO 8601 timestamp
}
```

---

## Entity Relationships

```
┌─────────────────────────────────────────────────────────────────────┐
│                     EXTERNAL STORAGE                                 │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌─────────────┐         ┌─────────────┐         ┌─────────────┐   │
│  │ Prometheus  │         │    Loki     │         │   Grafana   │   │
│  │  (Metrics)  │◄────────│   (Logs)    │────────►│ (Dashboards)│   │
│  └──────▲──────┘         └──────▲──────┘         └─────────────┘   │
│         │                       │                                    │
└─────────┼───────────────────────┼────────────────────────────────────┘
          │                       │
          │ HTTP /metrics         │ Log shipping (Alloy)
          │                       │
┌─────────┼───────────────────────┼────────────────────────────────────┐
│         │    LARAVEL APPLICATION                                     │
├─────────┼───────────────────────┼────────────────────────────────────┤
│         │                       │                                    │
│  ┌──────┴──────┐         ┌──────┴──────┐                            │
│  │ Prometheus  │         │  Monolog    │                            │
│  │  Exporter   │         │  (JSON)     │                            │
│  │   (Redis)   │         │             │                            │
│  └──────▲──────┘         └──────▲──────┘                            │
│         │                       │                                    │
│  ┌──────┴──────┐         ┌──────┴──────┐         ┌─────────────┐   │
│  │ Collectors  │         │ Correlation │         │   Existing  │   │
│  │  (Custom)   │◄────────│ Middleware  │────────►│   Models    │   │
│  └─────────────┘         └─────────────┘         │ (Campaign,  │   │
│                                                   │  Mailbox,   │   │
│                                                   │  Response,  │   │
│                                                   │  etc.)      │   │
│                                                   └─────────────┘   │
└──────────────────────────────────────────────────────────────────────┘
```

---

## Validation Rules

### WebVitalsMetric

| Field | Rule |
|-------|------|
| `name` | Required, in: LCP, FID, CLS, INP, TTFB |
| `value` | Required, numeric, >= 0 |
| `page` | Required, string, max: 255 |
| `deviceType` | Required, in: desktop, mobile, tablet |
| `sessionId` | Required, string, max: 64 |
| `timestamp` | Required, ISO 8601 date |

### JsError

| Field | Rule |
|-------|------|
| `message` | Required, string, max: 1000 |
| `source` | Required, url |
| `lineno` | Required, integer, >= 0 |
| `colno` | Required, integer, >= 0 |
| `page` | Required, url |
| `browser` | Required, string, max: 500 |
| `timestamp` | Required, ISO 8601 date |

---

## Data Retention

| Data Type | Storage | Retention | Rationale |
|-----------|---------|-----------|-----------|
| Prometheus Metrics | Prometheus TSDB | 15 days | Local development; configurable for production |
| Loki Logs | Loki filesystem | 7 days | Per spec requirement SC-008 |
| Grafana Dashboards | Grafana DB + JSON | Permanent | Version controlled in repository |

---

## Privacy Considerations

### PII Redaction

The following fields MUST be redacted before logging:

| Field | Redaction Method |
|-------|------------------|
| Password | Never log |
| API keys | Never log |
| Email addresses | Hash or mask: `j***@example.com` |
| SMTP/IMAP credentials | Never log |
| Session tokens | Never log |
| IP addresses | Optional: anonymize last octet |

### Implementation

```php
// Redaction processor for Monolog
class PiiRedactionProcessor
{
    private array $sensitiveKeys = [
        'password', 'api_key', 'secret', 'token',
        'smtp_password', 'imap_password', 'credential'
    ];

    public function __invoke(array $record): array
    {
        $record['context'] = $this->redact($record['context']);
        return $record;
    }
}
```

---

## Index Strategy

### Loki Label Cardinality

Keep label cardinality low for Loki performance:

| Label | Cardinality | Index |
|-------|-------------|-------|
| `job` | ~3 | Yes |
| `level` | 4 | Yes |
| `environment` | ~3 | Yes |
| `campaign_id` | High | No (use LogQL filter) |
| `user_id` | High | No (use LogQL filter) |
| `correlation_id` | Very High | No (use LogQL filter) |

### Prometheus Label Cardinality

| Metric | Max Labels | Strategy |
|--------|------------|----------|
| HTTP requests | route (~50), method (5), status (10) | Acceptable |
| Queue jobs | job (~10), queue (3), status (2) | Acceptable |
| Campaign metrics | campaign_id (unbounded) | Use recording rules for aggregation |
