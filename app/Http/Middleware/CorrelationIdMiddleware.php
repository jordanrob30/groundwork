<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that generates and propagates correlation IDs for request tracing.
 *
 * The correlation ID follows a request through all components of the system,
 * enabling end-to-end tracing in logs and metrics. If a correlation ID is
 * provided in the request header, it will be used; otherwise, a new one is generated.
 */
class CorrelationIdMiddleware
{
    /**
     * The header name for the correlation ID.
     */
    public const HEADER_NAME = 'X-Correlation-ID';

    /**
     * The context key for the correlation ID in logs.
     */
    public const CONTEXT_KEY = 'correlation_id';

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Closure  $next  The next middleware in the pipeline
     * @return Response The HTTP response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get existing correlation ID from header or generate a new one
        $correlationId = $request->header(self::HEADER_NAME) ?? $this->generateCorrelationId();

        // Store in request for later use
        $request->attributes->set(self::CONTEXT_KEY, $correlationId);

        // Add to log context so all logs include the correlation ID
        Log::shareContext([
            self::CONTEXT_KEY => $correlationId,
            'request_id' => $request->attributes->get('request_id', $correlationId),
        ]);

        // Log request start
        $startTime = microtime(true);
        Log::info('Request started', [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
        ]);

        // Process the request
        $response = $next($request);

        // Log request end
        $duration = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        Log::info('Request completed', [
            'method' => $request->method(),
            'path' => $request->path(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => round($duration, 2),
            'user_id' => $request->user()?->id,
        ]);

        // Add correlation ID to response header
        $response->headers->set(self::HEADER_NAME, $correlationId);

        return $response;
    }

    /**
     * Generate a new correlation ID.
     *
     * Uses UUID v4 format for globally unique identifiers.
     *
     * @return string The generated correlation ID
     */
    protected function generateCorrelationId(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Get the correlation ID for the current request.
     *
     * @param  Request|null  $request  The request instance (optional)
     * @return string|null The correlation ID or null if not set
     */
    public static function getCorrelationId(?Request $request = null): ?string
    {
        $request = $request ?? request();

        return $request->attributes->get(self::CONTEXT_KEY);
    }
}
