# Quickstart: Frontend & Backend Observability

**Feature Branch**: `005-observability-dashboards`
**Date**: 2025-12-09

## Prerequisites

- Docker Desktop running
- Laravel Sail configured (`./vendor/bin/sail` available)
- Node.js 18+ (for frontend packages)
- Existing Groundwork application running

---

## Quick Setup (5 minutes)

### 1. Start the Observability Stack

```bash
# From project root
./vendor/bin/sail up -d
```

The updated `compose.yaml` includes:
- **Prometheus** (metrics): http://localhost:9090
- **Loki** (logs): http://localhost:3100
- **Alloy** (log collector): http://localhost:12345
- **Grafana** (dashboards): http://localhost:3000

### 2. Access Grafana

1. Open http://localhost:3000
2. Login with `admin` / `admin` (change on first login)
3. Navigate to **Dashboards** > **Email Campaigns**

### 3. Verify Metrics Collection

```bash
# Check Prometheus targets
curl http://localhost:9090/api/v1/targets

# Check Laravel metrics endpoint
curl http://localhost/metrics

# Check Loki is receiving logs
curl http://localhost:3100/ready
```

---

## Key URLs

| Service | URL | Purpose |
|---------|-----|---------|
| Grafana | http://localhost:3000 | Dashboards and visualization |
| Prometheus | http://localhost:9090 | Metrics queries and targets |
| Loki | http://localhost:3100 | Log queries (via Grafana) |
| Alloy UI | http://localhost:12345 | Log collector status |
| Laravel Metrics | http://localhost/metrics | Prometheus scrape endpoint |

---

## Available Dashboards

### 1. Campaign Flow Health (P1)
**Path**: Dashboards > Email Campaigns > Campaign Flow Health

Shows:
- Email queue depth and processing rate
- Send success/failure rates
- Reply detection latency
- AI analysis completion rate
- Flow stage progression

### 2. Backend Performance (P2)
**Path**: Dashboards > Email Campaigns > Backend Performance

Shows:
- HTTP request latency by route
- Queue job processing rates
- Database query performance
- Redis cache hit rates

### 3. Frontend Performance (P3)
**Path**: Dashboards > Email Campaigns > Frontend Performance

Shows:
- Core Web Vitals (LCP, FID, CLS)
- Page load times
- JavaScript error counts
- Livewire component performance

### 4. Admin Activity (P4)
**Path**: Dashboards > Email Campaigns > Admin Activity

Shows:
- Admin action timeline
- Impersonation sessions
- Role change history
- Action frequency patterns

### 5. Log Explorer (P5)
**Path**: Explore > Loki

Use for:
- Full-text log search
- Filtering by correlation ID
- Error investigation
- Request tracing

---

## Common Tasks

### View Campaign Flow Metrics

1. Open **Campaign Flow Health** dashboard
2. Select campaign from dropdown (or "All")
3. Adjust time range as needed
4. Check each stage: Queue → Send → Poll → Analyze

### Investigate an Error

1. Go to **Explore** > Select **Loki** datasource
2. Enter LogQL query:
   ```logql
   {job="laravel"} |= "error" | json
   ```
3. Filter by time range when error occurred
4. Click on log line to see full context

### Trace a Request

1. Find the correlation ID from error log or frontend
2. Search in Loki:
   ```logql
   {job="laravel"} | json | correlation_id="abc123"
   ```
3. View all logs for that request across components

---

## LogQL Query Reference

### Basic Queries

```logql
# All application logs
{job="laravel"}

# Filter by log level
{job="laravel"} | json | level="error"
{job="laravel"} | json | level="warning"
{job="laravel"} | json | level=~"error|warning"

# Text search
{job="laravel"} |= "SendEmailJob"
{job="laravel"} |= "failed"
{job="laravel"} |~ "(?i)error"  # Case insensitive
```

### Correlation ID Tracing

```logql
# Find all logs for a specific correlation ID
{job="laravel"} | json | correlation_id="550e8400-e29b-41d4-a716-446655440000"

# Find all errors with correlation IDs
{job="laravel"} | json | level="error" | line_format "{{.correlation_id}}: {{.message}}"
```

### Campaign Flow Queries

```logql
# All logs for a specific campaign
{job="laravel"} | json | campaign_id="123"

# Email sending jobs
{job="laravel"} |= "SendEmailJob"

# Email sending failures
{job="laravel"} | json | level="error" |= "SendEmailJob"

# Mailbox polling
{job="laravel"} |= "PollMailboxJob"

# AI analysis jobs
{job="laravel"} |= "AnalyzeResponseJob"

# All job starts and completions for a campaign
{job="laravel"} | json | campaign_id="123" |~ "Job (started|completed)"
```

### Performance Analysis

```logql
# Slow requests (duration > 1000ms)
{job="laravel"} | json | message="Request completed" | duration_ms > 1000

# Request latency distribution
{job="laravel"} | json | message="Request completed"
  | line_format "{{.path}}: {{.duration_ms}}ms"

# Slow database queries
{job="laravel"} | json |= "slow query"
```

### Error Investigation

```logql
# All errors in the last hour
{job="laravel"} | json | level="error"

# Errors by exception class
{job="laravel"} | json | level="error"
  | line_format "{{.exception_class}}: {{.error}}"

# Job failures
{job="laravel"} | json |= "Job failed"

# Group errors by type (use Grafana's Logs panel with "Extract labels")
{job="laravel"} | json | level="error" | label_format error_type="{{.exception_class}}"
```

### Admin Activity

```logql
# All admin actions
{job="laravel"} | json |= "admin_action"

# Impersonation events
{job="laravel"} | json |= "impersonat"

# Role changes
{job="laravel"} | json |= "role_change"
```

### HTTP Request Analysis

```logql
# All HTTP requests
{job="laravel"} | json | message="Request completed"

# Failed requests (5xx status)
{job="laravel"} | json | message="Request completed" | status_code >= 500

# Requests to specific path
{job="laravel"} | json | message="Request completed" | path="/api/campaigns"

# Authenticated user requests
{job="laravel"} | json | message="Request completed" | user_id != ""
```

### Rate and Aggregation

```logql
# Error rate per minute (use in Grafana panel)
sum(rate({job="laravel"} | json | level="error" [1m]))

# Request count per path
sum by (path) (count_over_time({job="laravel"} | json | message="Request completed" [5m]))

# Log volume over time
sum(rate({job="laravel"}[5m])) by (level)
```

### Tips for Efficient Queries

1. **Use label filters first**: `{job="laravel"}` before pipe operators
2. **Filter early**: Put most restrictive filters first
3. **Avoid regex when possible**: `|= "text"` is faster than `|~ "text"`
4. **Use json parser once**: Parse JSON once, then filter on fields
5. **Limit time range**: Use shorter ranges for faster queries

---

### Check Frontend Performance

1. Open **Frontend Performance** dashboard
2. Review Core Web Vitals gauges
3. Check for JavaScript errors
4. Compare desktop vs mobile metrics

---

## Troubleshooting

### No metrics appearing in Grafana

```bash
# Check Prometheus is scraping Laravel
curl http://localhost:9090/api/v1/targets | jq '.data.activeTargets'

# Verify metrics endpoint is accessible
curl http://localhost/metrics | head -20

# Check for scrape errors
curl http://localhost:9090/api/v1/targets | jq '.data.activeTargets[].lastError'
```

### No logs appearing in Loki

```bash
# Check Alloy is running
docker compose ps alloy

# View Alloy logs
docker compose logs alloy

# Verify Laravel logs exist
ls -la storage/logs/

# Check Loki is ready
curl http://localhost:3100/ready
```

### Grafana datasource errors

1. Go to **Configuration** > **Data Sources**
2. Click on Prometheus or Loki
3. Click **Test** button
4. Check connection URL matches Docker network name

### High memory usage

```bash
# Check container resource usage
docker stats

# Reduce Prometheus retention (edit docker/prometheus/prometheus.yml)
# --storage.tsdb.retention.time=3d

# Reduce Loki retention (edit docker/loki/loki-config.yml)
# retention_period: 72h
```

---

## Development Workflow

### Adding New Metrics

1. Define metric in `app/Metrics/` collector class
2. Register collector in `PrometheusServiceProvider`
3. Add to Grafana dashboard JSON
4. Test: `curl http://localhost/metrics | grep your_metric`

### Adding Dashboard Panels

1. Edit dashboard in Grafana UI
2. Export JSON: Dashboard Settings > JSON Model
3. Save to `docker/grafana/dashboards/`
4. Restart Grafana to reload

### Testing Log Queries

1. Use Grafana Explore
2. Write LogQL query
3. Once working, add to dashboard as Logs panel

---

## Environment Variables

Add to `.env`:

```env
# Prometheus metrics
PROMETHEUS_STORAGE_ADAPTER=redis
PROMETHEUS_REDIS_PREFIX=metrics_

# Observability endpoints
OBSERVABILITY_ENABLED=true
OBSERVABILITY_SAMPLING_RATE=1.0

# Log correlation
LOG_CORRELATION_HEADER=X-Correlation-ID
```

---

## Architecture Overview

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   Browser   │────►│   Laravel   │────►│    Redis    │
│ (web-vitals)│     │   (App)     │     │  (Metrics)  │
└─────────────┘     └──────┬──────┘     └──────┬──────┘
                           │                    │
                    Logs   │            Scrape  │
                           ▼                    ▼
                    ┌─────────────┐     ┌─────────────┐
                    │    Alloy    │     │ Prometheus  │
                    │(Log Shipper)│     │  (Metrics)  │
                    └──────┬──────┘     └──────┬──────┘
                           │                    │
                           ▼                    │
                    ┌─────────────┐             │
                    │    Loki     │             │
                    │   (Logs)    │             │
                    └──────┬──────┘             │
                           │                    │
                           └────────┬───────────┘
                                    ▼
                            ┌─────────────┐
                            │   Grafana   │
                            │ (Dashboards)│
                            └─────────────┘
```

---

## Next Steps

1. **Customize dashboards** for your specific campaign metrics
2. **Add alerts** (future enhancement) for critical thresholds
3. **Tune retention** based on storage requirements
4. **Configure sampling** for high-traffic production environments
