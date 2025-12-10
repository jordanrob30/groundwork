# Implementation Plan: Frontend & Backend Observability with Grafana Dashboards

**Branch**: `005-observability-dashboards` | **Date**: 2025-12-09 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/005-observability-dashboards/spec.md`

## Summary

Implement comprehensive observability for the Groundwork application including:
- **Backend**: Structured logging with correlation IDs, Prometheus metrics for campaign flow and HTTP requests
- **Frontend**: Core Web Vitals collection, JavaScript error tracking, Livewire component performance
- **Infrastructure**: Grafana dashboards with Prometheus and Loki datasources, provisioned via Docker
- **Flow Monitoring**: Dashboards aligned with email discovery engine workflow (queue → send → poll → analyze)

## Technical Context

**Language/Version**: PHP 8.3 + JavaScript (ES6+)
**Primary Dependencies**: Laravel 11, Livewire 3, Tailwind CSS, spatie/laravel-prometheus, web-vitals (npm)
**Storage**: MySQL 8 (existing), Prometheus (metrics), Loki (logs), Redis (metric aggregation)
**Testing**: PHPUnit (sail artisan test), Browser tests for frontend metrics
**Target Platform**: Docker (Laravel Sail), local development + production
**Project Type**: Web application (Laravel monolith)
**Performance Goals**: <5% overhead from observability instrumentation, metrics visible within 15s scrape interval
**Constraints**: Must use Laravel Sail, no external SaaS dependencies, graceful degradation if observability stack unavailable
**Scale/Scope**: Single application, 4 Grafana dashboards, ~50 custom metrics

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|-----------|--------|-------|
| **I. Tech Stack** | PASS | Uses Laravel 11, PHP 8.3, Redis, MySQL 8 per constitution |
| **II. Local Development** | PASS | All services added to Laravel Sail compose.yaml |
| **III. Architecture Rules** | PASS | Service classes for metrics collection, no raw SQL, Redis for metric storage |
| **IV. Code Style** | PASS | Type hints required, PHPDoc blocks, feature tests for endpoints |
| **V. Security** | PASS | Form Request validation for API endpoints, rate limiting on observability endpoints |

### Technology Additions (Constitution Compliant)

| New Component | Justification |
|---------------|---------------|
| Prometheus | Open-source metrics storage, standard for observability |
| Grafana Loki | Open-source log aggregation, lightweight for Docker |
| Grafana | Open-source visualization, industry standard |
| Grafana Alloy | Log shipper replacing deprecated Promtail |
| spatie/laravel-prometheus | Laravel-native Prometheus metrics package |
| web-vitals (npm) | Google's official Core Web Vitals library |

## Project Structure

### Documentation (this feature)

```text
specs/005-observability-dashboards/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Technology decisions and rationale
├── data-model.md        # Metric schemas and log structures
├── quickstart.md        # Developer setup guide
├── contracts/
│   ├── openapi.yaml     # Frontend API contracts
│   └── prometheus-metrics.md  # Metric naming and queries
└── tasks.md             # Implementation tasks (via /speckit.tasks)
```

### Source Code (repository root)

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── ObservabilityController.php    # Frontend metrics endpoint
│   ├── Middleware/
│   │   ├── CorrelationIdMiddleware.php        # Request tracing
│   │   └── RequestMetricsMiddleware.php       # HTTP metrics collection
│   └── Requests/
│       ├── WebVitalsRequest.php               # Validation
│       └── JsErrorRequest.php                 # Validation
├── Logging/
│   ├── JsonFormatter.php                      # Structured log format
│   └── PiiRedactionProcessor.php              # Sensitive data removal
├── Metrics/
│   ├── Collectors/
│   │   ├── CampaignFlowCollector.php          # Email pipeline metrics
│   │   ├── HttpRequestCollector.php           # Request latency
│   │   ├── QueueJobCollector.php              # Job processing
│   │   └── FrontendCollector.php              # Web vitals aggregation
│   └── PrometheusServiceProvider.php          # Collector registration
└── Providers/
    └── ObservabilityServiceProvider.php       # Feature bootstrap

config/
├── logging.php                                # Updated for JSON/Loki
└── prometheus.php                             # Metrics configuration

docker/
├── prometheus/
│   └── prometheus.yml                         # Scrape configuration
├── loki/
│   └── loki-config.yml                        # Log storage config
├── alloy/
│   └── config.alloy                           # Log collection pipeline
└── grafana/
    ├── provisioning/
    │   ├── datasources/
    │   │   └── datasources.yaml               # Prometheus + Loki
    │   └── dashboards/
    │       └── dashboards.yaml                # Dashboard provisioning
    └── dashboards/
        ├── campaign-flow.json                 # P1: Campaign Flow Health
        ├── backend-performance.json           # P2: Backend Performance
        ├── frontend-performance.json          # P3: Frontend Performance
        └── admin-activity.json                # P4: Admin Activity

resources/
└── js/
    └── observability/
        ├── web-vitals.js                      # Core Web Vitals collection
        ├── error-tracking.js                  # JS error capture
        └── livewire-metrics.js                # Component performance

tests/
├── Feature/
│   ├── Observability/
│   │   ├── WebVitalsEndpointTest.php
│   │   ├── JsErrorEndpointTest.php
│   │   └── MetricsEndpointTest.php
│   └── Middleware/
│       └── CorrelationIdMiddlewareTest.php
└── Unit/
    └── Logging/
        └── PiiRedactionProcessorTest.php
```

**Structure Decision**: Single Laravel application with observability components integrated. Docker infrastructure services (Prometheus, Loki, Grafana, Alloy) added to existing Laravel Sail compose.yaml.

## Phase 1 Artifacts

| Artifact | Status | Path |
|----------|--------|------|
| research.md | Complete | [research.md](./research.md) |
| data-model.md | Complete | [data-model.md](./data-model.md) |
| contracts/openapi.yaml | Complete | [contracts/openapi.yaml](./contracts/openapi.yaml) |
| contracts/prometheus-metrics.md | Complete | [contracts/prometheus-metrics.md](./contracts/prometheus-metrics.md) |
| quickstart.md | Complete | [quickstart.md](./quickstart.md) |

## Key Design Decisions

### 1. Metrics Storage
- **Decision**: Prometheus with Redis storage adapter for PHP metrics
- **Rationale**: PHP's share-nothing architecture requires persistent storage; Redis is already in the stack

### 2. Log Aggregation
- **Decision**: Grafana Loki with Alloy collector
- **Rationale**: Lightweight, native Grafana integration, Promtail is deprecated

### 3. Frontend Metrics
- **Decision**: web-vitals npm package with beacon endpoint
- **Rationale**: Official Google library, stable and production-ready

### 4. Dashboard Provisioning
- **Decision**: File-based provisioning via Docker volumes
- **Rationale**: Version-controlled, reproducible across environments

## Complexity Tracking

No constitution violations requiring justification. All additions are compliant with established patterns.

## Next Steps

1. Run `/speckit.tasks` to generate implementation tasks
2. Implementation will follow user story priority (P1 → P5)
3. Each dashboard is independently deployable per spec requirements
