# Prometheus Metrics Contract

**Feature Branch**: `005-observability-dashboards`
**Date**: 2025-12-09

## Overview

This document defines the Prometheus metrics exposed by the Groundwork application at the `/metrics` endpoint. All metrics follow Prometheus naming conventions and are prefixed with `groundwork_`.

---

## Metric Naming Convention

```
groundwork_<component>_<metric>_<unit>
```

- **component**: Logical area (email, http, queue, frontend, etc.)
- **metric**: What is being measured (requests, duration, depth, etc.)
- **unit**: Unit of measurement (seconds, bytes, total for counters)

---

## Campaign Flow Metrics

### Email Queue

| Metric | Type | Labels | Description |
|--------|------|--------|-------------|
| `groundwork_email_queue_depth` | Gauge | `campaign_id`, `mailbox_id` | Current emails waiting in queue |
| `groundwork_email_queued_total` | Counter | `campaign_id`, `mailbox_id` | Total emails added to queue |

### Email Sending

| Metric | Type | Labels | Description |
|--------|------|--------|-------------|
| `groundwork_email_sent_total` | Counter | `campaign_id`, `mailbox_id`, `status` | Emails sent (status: success, failed, bounced) |
| `groundwork_email_send_duration_seconds` | Histogram | `campaign_id`, `mailbox_id` | Send operation duration |

**Histogram Buckets (send duration)**: `[0.1, 0.5, 1, 2, 5, 10, 30]` seconds

### Reply Detection

| Metric | Type | Labels | Description |
|--------|------|--------|-------------|
| `groundwork_replies_detected_total` | Counter | `campaign_id`, `matched` | Replies found (matched: true/false) |
| `groundwork_polling_duration_seconds` | Histogram | `mailbox_id` | Mailbox polling duration |
| `groundwork_polling_messages_total` | Counter | `mailbox_id` | Total messages retrieved from mailboxes |

**Histogram Buckets (polling duration)**: `[1, 5, 10, 30, 60, 120]` seconds

### AI Analysis

| Metric | Type | Labels | Description |
|--------|------|--------|-------------|
| `groundwork_analysis_total` | Counter | `campaign_id`, `status` | Analysis jobs (status: completed, failed) |
| `groundwork_analysis_duration_seconds` | Histogram | `campaign_id` | AI analysis duration |
| `groundwork_analysis_queue_depth` | Gauge | - | Responses pending analysis |

**Histogram Buckets (analysis duration)**: `[1, 5, 10, 30, 60]` seconds

---

## HTTP Request Metrics

| Metric | Type | Labels | Description |
|--------|------|--------|-------------|
| `groundwork_http_requests_total` | Counter | `method`, `route`, `status_code` | Total HTTP requests |
| `groundwork_http_request_duration_seconds` | Histogram | `method`, `route` | Request processing time |
| `groundwork_http_request_size_bytes` | Histogram | `method`, `route` | Request body size |
| `groundwork_http_response_size_bytes` | Histogram | `method`, `route` | Response body size |

**Histogram Buckets (duration)**: `[0.01, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5, 10]` seconds
**Histogram Buckets (size)**: `[100, 1000, 10000, 100000, 1000000]` bytes

---

## Queue Job Metrics

| Metric | Type | Labels | Description |
|--------|------|--------|-------------|
| `groundwork_queue_jobs_total` | Counter | `job`, `queue`, `status` | Jobs processed (status: processed, failed) |
| `groundwork_queue_job_duration_seconds` | Histogram | `job`, `queue` | Job execution time |
| `groundwork_queue_depth` | Gauge | `queue` | Current queue depth |
| `groundwork_queue_wait_seconds` | Histogram | `queue` | Time job waited in queue |

**Job Classes**:
- `SendEmailJob`
- `PollMailboxJob`
- `AnalyzeResponseJob`
- `ImportLeadsJob`

**Queue Names**:
- `default`
- `emails`

**Histogram Buckets (duration)**: `[0.1, 0.5, 1, 5, 10, 30, 60, 300]` seconds

---

## Database Metrics

| Metric | Type | Labels | Description |
|--------|------|--------|-------------|
| `groundwork_db_queries_total` | Counter | `operation` | Database queries (operation: select, insert, update, delete) |
| `groundwork_db_query_duration_seconds` | Histogram | `operation` | Query execution time |
| `groundwork_db_connections_active` | Gauge | - | Active database connections |

**Histogram Buckets (query duration)**: `[0.001, 0.005, 0.01, 0.05, 0.1, 0.5, 1]` seconds

---

## Frontend Metrics

### Core Web Vitals

| Metric | Type | Labels | Description |
|--------|------|--------|-------------|
| `groundwork_web_vitals_lcp_seconds` | Histogram | `page`, `device_type` | Largest Contentful Paint |
| `groundwork_web_vitals_fid_seconds` | Histogram | `page`, `device_type` | First Input Delay |
| `groundwork_web_vitals_cls` | Histogram | `page`, `device_type` | Cumulative Layout Shift |
| `groundwork_web_vitals_inp_seconds` | Histogram | `page`, `device_type` | Interaction to Next Paint |
| `groundwork_web_vitals_ttfb_seconds` | Histogram | `page`, `device_type` | Time to First Byte |

**Histogram Buckets (LCP)**: `[0.5, 1, 1.5, 2, 2.5, 3, 4, 5]` seconds
**Histogram Buckets (FID/INP)**: `[0.01, 0.05, 0.1, 0.2, 0.3, 0.5]` seconds
**Histogram Buckets (CLS)**: `[0.01, 0.05, 0.1, 0.15, 0.2, 0.25]`
**Histogram Buckets (TTFB)**: `[0.1, 0.2, 0.4, 0.6, 0.8, 1, 2]` seconds

### Page Load

| Metric | Type | Labels | Description |
|--------|------|--------|-------------|
| `groundwork_page_load_duration_seconds` | Histogram | `page` | Full page load time |
| `groundwork_page_dom_ready_seconds` | Histogram | `page` | DOM ready time |

**Histogram Buckets**: `[0.5, 1, 2, 3, 5, 10]` seconds

### JavaScript Errors

| Metric | Type | Labels | Description |
|--------|------|--------|-------------|
| `groundwork_js_errors_total` | Counter | `page`, `error_type` | JavaScript errors captured |

### Livewire Components

| Metric | Type | Labels | Description |
|--------|------|--------|-------------|
| `groundwork_livewire_render_duration_seconds` | Histogram | `component` | Component render time |
| `groundwork_livewire_update_duration_seconds` | Histogram | `component` | Component update time |
| `groundwork_livewire_errors_total` | Counter | `component` | Livewire component errors |

**Histogram Buckets**: `[0.01, 0.05, 0.1, 0.25, 0.5, 1]` seconds

---

## Admin Activity Metrics

| Metric | Type | Labels | Description |
|--------|------|--------|-------------|
| `groundwork_admin_actions_total` | Counter | `action`, `admin_id` | Admin actions performed |
| `groundwork_admin_impersonations_active` | Gauge | - | Currently active impersonation sessions |

**Action Types**:
- `impersonate_start`
- `impersonate_end`
- `role_change`
- `user_create`
- `user_delete`

---

## System Metrics

| Metric | Type | Labels | Description |
|--------|------|--------|-------------|
| `groundwork_cache_hits_total` | Counter | `store` | Cache hits |
| `groundwork_cache_misses_total` | Counter | `store` | Cache misses |
| `groundwork_redis_connections_active` | Gauge | - | Active Redis connections |

---

## Label Values

### Status Labels

| Label | Possible Values |
|-------|-----------------|
| `status` (email) | `success`, `failed`, `bounced` |
| `status` (job) | `processed`, `failed` |
| `status` (analysis) | `completed`, `failed` |
| `matched` | `true`, `false` |

### Device Type Labels

| Label | Possible Values |
|-------|-----------------|
| `device_type` | `desktop`, `mobile`, `tablet` |

### HTTP Labels

| Label | Example Values |
|-------|----------------|
| `method` | `GET`, `POST`, `PUT`, `DELETE` |
| `route` | `campaigns.index`, `api.leads.store`, `dashboard` |
| `status_code` | `200`, `201`, `400`, `401`, `403`, `404`, `500` |

---

## Example Prometheus Queries

### Campaign Flow Health

```promql
# Email send success rate (last 5 minutes)
sum(rate(groundwork_email_sent_total{status="success"}[5m]))
/ sum(rate(groundwork_email_sent_total[5m])) * 100

# Queue processing rate
rate(groundwork_queue_jobs_total{job="SendEmailJob",status="processed"}[5m])

# P95 email send latency
histogram_quantile(0.95, rate(groundwork_email_send_duration_seconds_bucket[5m]))

# Analysis backlog
groundwork_analysis_queue_depth
```

### HTTP Performance

```promql
# Request rate by route
sum by (route)(rate(groundwork_http_requests_total[5m]))

# P95 latency by route
histogram_quantile(0.95, sum by (route, le)(rate(groundwork_http_request_duration_seconds_bucket[5m])))

# Error rate
sum(rate(groundwork_http_requests_total{status_code=~"5.."}[5m]))
/ sum(rate(groundwork_http_requests_total[5m])) * 100
```

### Frontend Performance

```promql
# Average LCP by page
histogram_quantile(0.5, sum by (page, le)(rate(groundwork_web_vitals_lcp_seconds_bucket[1h])))

# JavaScript error rate
sum(rate(groundwork_js_errors_total[5m]))

# CLS score distribution
histogram_quantile(0.75, rate(groundwork_web_vitals_cls_bucket[1h]))
```

---

## Scrape Configuration

```yaml
# prometheus.yml
scrape_configs:
  - job_name: 'groundwork'
    scrape_interval: 15s
    static_configs:
      - targets: ['laravel.test:80']
    metrics_path: /metrics
```
