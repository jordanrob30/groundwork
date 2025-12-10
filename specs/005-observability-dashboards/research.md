# Research: Frontend & Backend Observability with Grafana Dashboards

**Feature Branch**: `005-observability-dashboards`
**Date**: 2025-12-09

## Summary

This document captures research decisions for implementing full-stack observability in the Groundwork Laravel application, including log aggregation, metrics collection, frontend monitoring, and Grafana dashboard provisioning.

---

## Decision 1: Log Aggregation Approach

### Decision
Use **Grafana Loki** with **Grafana Alloy** for log collection and aggregation.

### Rationale
- **Loki** only indexes metadata/labels (not full log content), making it significantly lighter than Elasticsearch
- Lower storage and compute requirements - ideal for Docker/Sail environments
- Native integration with Grafana for unified observability
- **Alloy** is Grafana's distribution of the OpenTelemetry Collector, replacing deprecated Promtail (EOL March 2026)
- Alloy supports metrics, logs, traces, and profiles in one agent

### Alternatives Considered
| Alternative | Why Rejected |
|-------------|--------------|
| Elasticsearch/ELK Stack | Higher resource usage (2-3x CPU/memory), more complex setup for Docker environment |
| Promtail | Deprecated in February 2025, EOL March 2026; Alloy is the official successor |
| Fluentd/Fluent Bit | Additional complexity; Alloy provides native Loki integration |

### Implementation Notes
- Configure Laravel to output JSON-structured logs
- Mount Laravel log directory to Alloy container
- Configure Alloy to ship logs to Loki with appropriate labels (service, level, environment)

---

## Decision 2: Metrics Collection Approach

### Decision
Use **Prometheus** with **spatie/laravel-prometheus** package and Redis storage adapter.

### Rationale
- PHP's share-nothing request lifecycle causes metrics like counters to reset between requests
- Prometheus libraries support APCu/Redis storage to persist metrics across requests
- spatie/laravel-prometheus is actively maintained (2024+) with built-in collectors for queues, HTTP requests, database queries
- Redis is already in the stack (Laravel Sail default)
- Prometheus provides excellent PromQL for querying and native Grafana integration

### Alternatives Considered
| Alternative | Why Rejected |
|-------------|--------------|
| OpenTelemetry SDK alone | PHP SDK lacks storage adapter; metrics reset between requests |
| StatsD/Graphite | Less ecosystem support, Prometheus is the modern standard |
| Datadog/New Relic | Paid SaaS; requirement is for self-hosted open-source solution |

### Implementation Notes
- Install `spatie/laravel-prometheus` via Composer
- Configure Redis storage adapter (`PROMETHEUS_STORAGE_ADAPTER=redis`)
- Expose `/metrics` endpoint for Prometheus scraping
- Add custom collectors for campaign flow metrics

---

## Decision 3: Frontend Observability Approach

### Decision
Use **web-vitals** npm package with a Laravel beacon endpoint for metrics collection.

### Rationale
- web-vitals is the official Google library (~2KB gzipped, modular)
- Measures all Core Web Vitals (LCP, INP, CLS) exactly as Chrome measures them
- Uses non-blocking sendBeacon API for reliable delivery
- Stable and production-ready (unlike experimental OpenTelemetry browser instrumentation)

### Alternatives Considered
| Alternative | Why Rejected |
|-------------|--------------|
| OpenTelemetry Browser SDK | Still experimental for Core Web Vitals; not production-ready |
| Third-party RUM (Datadog, etc.) | Paid SaaS; requirement is self-hosted |
| Custom implementation | web-vitals provides standardized, battle-tested measurement |

### Implementation Notes
- Install web-vitals via npm
- Create `/api/observability/web-vitals` POST endpoint
- Aggregate metrics in Redis, export to Prometheus
- Consider sampling (10-20%) for high-traffic scenarios

---

## Decision 4: Structured Logging Package

### Decision
Use Laravel's built-in Monolog with custom JSON formatter and correlation ID middleware.

### Rationale
- Laravel's logging is already built on Monolog with excellent configurability
- No need for additional package dependency
- Custom middleware can inject correlation IDs consistently
- JSON formatter is straightforward to implement

### Alternatives Considered
| Alternative | Why Rejected |
|-------------|--------------|
| lfffd/laravel-logging | Feature-rich but adds complexity; built-in Monolog sufficient |
| befuturein/laravel-log-enhancer | Good for simple cases, but custom solution provides more control |

### Implementation Notes
- Create custom JSON log formatter
- Add middleware to generate/propagate correlation IDs
- Configure log channels for structured output
- Implement PII redaction processor

---

## Decision 5: Docker Infrastructure Services

### Decision
Add Prometheus, Loki, Alloy, and Grafana to the Laravel Sail docker-compose configuration.

### Services Added
```yaml
prometheus:    # Metrics storage and querying
loki:          # Log aggregation
alloy:         # Log/metrics collection agent
grafana:       # Visualization dashboards
```

### Rationale
- All services run alongside existing Sail infrastructure
- Consistent with Laravel Sail Docker-based development approach
- Grafana provides unified interface for metrics, logs, and dashboards
- Prometheus and Loki are lightweight for local development

### Implementation Notes
- Add services to compose.yaml
- Create configuration directories: `docker/prometheus/`, `docker/loki/`, `docker/alloy/`, `docker/grafana/`
- Provision dashboards via Grafana's file-based provisioning
- Configure datasources automatically

---

## Decision 6: Grafana Dashboard Structure

### Decision
Implement a 3-tier dashboard hierarchy with JSON provisioning.

### Dashboard Structure
```
Email Campaigns/
├── Overview (Executive metrics)
├── Campaign Flow Health (P1 - queue/send/poll/analyze pipeline)
├── Backend Performance (P2 - latency, queues, database)
├── Frontend Performance (P3 - Core Web Vitals, JS errors)
├── Admin Activity (P4 - audit trail visualization)
└── Log Explorer (P5 - centralized log search)
```

### Rationale
- Aligns with user stories in specification (P1-P5 priority order)
- Flow dashboard directly maps to email discovery engine workflow
- Provisioned dashboards enable version control and reproducible deployments
- Template variables allow filtering by environment, campaign, time range

### Implementation Notes
- Store dashboard JSON in `docker/grafana/dashboards/`
- Configure provisioning via `docker/grafana/provisioning/dashboards/dashboards.yaml`
- Use consistent naming: `[Area] - [Purpose]`
- Include template variables for datasource, environment, campaign_id

---

## Decision 7: Metric Naming and Labels

### Decision
Use Prometheus naming conventions with consistent label taxonomy.

### Naming Pattern
```
groundwork_<component>_<metric>_<unit>
```

### Label Taxonomy
| Label | Description | Example Values |
|-------|-------------|----------------|
| `service` | Application service | `laravel`, `worker`, `scheduler` |
| `environment` | Deployment environment | `local`, `staging`, `production` |
| `campaign_id` | Campaign identifier | UUID |
| `stage` | Pipeline stage | `queue`, `send`, `poll`, `analyze` |
| `status` | Outcome status | `success`, `failure`, `pending` |
| `route` | HTTP route name | `campaigns.index`, `api.leads.store` |
| `job` | Queue job class | `SendEmailJob`, `AnalyzeResponseJob` |

### Rationale
- Prometheus naming conventions ensure consistency
- Labels enable powerful PromQL aggregation and filtering
- Consistent taxonomy across all metrics simplifies dashboard creation

---

## Decision 8: Alert Thresholds

### Decision
Define baseline alert thresholds with configurable escalation.

### Baseline Thresholds
| Metric | Warning | Critical | For Duration |
|--------|---------|----------|--------------|
| Email send success rate | < 98% | < 95% | 5m |
| Queue depth | > 5,000 | > 10,000 | 5m |
| P95 latency | > 2s | > 5s | 10m |
| Polling jobs stalled | - | 0 completions/hour | 5m |
| AI analysis failure rate | > 5% | > 10% | 10m |

### Rationale
- Thresholds based on industry standards and application SLAs
- Warning tier allows proactive intervention
- Critical tier triggers immediate attention
- "For duration" prevents alert flapping

### Implementation Notes
- Alerting rules stored in `docker/grafana/provisioning/alerting/`
- Configure notification channels (email, Slack, etc.) as extension point
- Alerts are out of scope for initial implementation (future enhancement per spec)

---

## Technology Stack Summary

### Backend Observability
| Component | Technology | Package/Image |
|-----------|------------|---------------|
| Metrics | Prometheus | `prom/prometheus:latest` |
| Metrics Export | Laravel Prometheus | `spatie/laravel-prometheus` |
| Log Aggregation | Grafana Loki | `grafana/loki:3.4` |
| Log Collection | Grafana Alloy | `grafana/alloy:latest` |
| Structured Logging | Laravel Monolog | Built-in (custom formatter) |

### Frontend Observability
| Component | Technology | Package |
|-----------|------------|---------|
| Core Web Vitals | web-vitals | `npm:web-vitals` |
| Error Tracking | Custom | Blade/JS integration |
| Livewire Metrics | Custom | Livewire event hooks |

### Infrastructure
| Component | Technology | Image |
|-----------|------------|-------|
| Visualization | Grafana | `grafana/grafana:latest` |
| Time-series DB | Prometheus | `prom/prometheus:latest` |
| Log Storage | Loki | `grafana/loki:3.4` |

---

## Sources

- [Loki vs Elasticsearch - SigNoz](https://signoz.io/blog/loki-vs-elasticsearch/)
- [Migration From Promtail to Alloy](https://developer-friendly.blog/blog/2025/03/17/migration-from-promtail-to-alloy/)
- [spatie/laravel-prometheus - GitHub](https://github.com/spatie/laravel-prometheus)
- [web-vitals - npm](https://www.npmjs.com/package/web-vitals)
- [Grafana Provisioning Documentation](https://grafana.com/docs/grafana/latest/administration/provisioning/)
- [Grafana Dashboard Best Practices](https://grafana.com/docs/grafana/latest/dashboards/build-dashboards/best-practices/)
- [PromQL Rate Function Guide](https://promlabs.com/blog/2021/01/29/how-exactly-does-promql-calculate-rates/)
