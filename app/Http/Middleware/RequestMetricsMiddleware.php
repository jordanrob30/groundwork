<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware for collecting HTTP request metrics.
 *
 * Records request duration, status codes, and request/response sizes
 * for all HTTP requests passing through the application.
 */
class RequestMetricsMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = microtime(true) - $startTime;

        try {
            $this->recordMetrics($request, $response, $duration);
        } catch (\Throwable $e) {
            Log::debug('Failed to record request metrics', ['error' => $e->getMessage()]);
        }

        return $response;
    }

    /**
     * Record HTTP request metrics.
     */
    protected function recordMetrics(Request $request, Response $response, float $duration): void
    {
        $collector = app(\App\Metrics\Collectors\HttpRequestCollector::class);

        $method = $request->method();
        $route = $this->getRouteName($request);
        $statusCode = (string) $response->getStatusCode();

        // Record request count
        $collector->incrementRequests($method, $route, $statusCode);

        // Record request duration
        $collector->recordDuration($method, $route, $duration);

        // Record request size
        $requestSize = strlen($request->getContent());
        if ($requestSize > 0) {
            $collector->recordRequestSize($method, $route, $requestSize);
        }

        // Record response size
        $responseSize = strlen($response->getContent());
        if ($responseSize > 0) {
            $collector->recordResponseSize($method, $route, $responseSize);
        }
    }

    /**
     * Get the route name or path for metrics labeling.
     */
    protected function getRouteName(Request $request): string
    {
        $route = $request->route();

        if ($route && $route->getName()) {
            return $route->getName();
        }

        if ($route && $route->uri()) {
            // Normalize URI to avoid high cardinality from path parameters
            return $this->normalizeUri($route->uri());
        }

        return 'unknown';
    }

    /**
     * Normalize URI by replacing dynamic segments.
     */
    protected function normalizeUri(string $uri): string
    {
        // Replace numeric IDs with placeholder
        $normalized = preg_replace('/\/\d+/', '/{id}', $uri);

        // Replace UUIDs with placeholder
        $normalized = preg_replace(
            '/\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i',
            '/{uuid}',
            $normalized
        );

        return $normalized;
    }
}
