<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JsErrorRequest;
use App\Http\Requests\LivewireMetricsRequest;
use App\Http\Requests\PageLoadRequest;
use App\Http\Requests\WebVitalsRequest;
use App\Metrics\Collectors\FrontendCollector;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * API controller for frontend observability metrics.
 */
class ObservabilityController extends Controller
{
    public function __construct(
        protected FrontendCollector $collector
    ) {}

    /**
     * Record Web Vitals metrics.
     */
    public function webVitals(WebVitalsRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $this->collector->recordWebVital(
                $validated['name'],
                (float) $validated['value'],
                $validated['page'],
                $validated['device_type'] ?? 'desktop'
            );

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            Log::debug('Failed to record web vitals', ['error' => $e->getMessage()]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Record JavaScript errors.
     */
    public function jsError(JsErrorRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $this->collector->recordJsError(
                $validated['page'],
                $validated['error_type'] ?? 'Error',
                $validated['message']
            );

            // Log the error for centralized logging
            Log::warning('JavaScript error', [
                'page' => $validated['page'],
                'error_type' => $validated['error_type'] ?? 'Error',
                'message' => $validated['message'],
                'stack' => $validated['stack'] ?? null,
            ]);

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            Log::debug('Failed to record JS error', ['error' => $e->getMessage()]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Record Livewire component metrics.
     */
    public function livewireMetrics(LivewireMetricsRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            if ($validated['action'] === 'render') {
                $this->collector->recordLivewireRender(
                    $validated['component'],
                    (float) $validated['duration']
                );
            } else {
                $this->collector->recordLivewireUpdate(
                    $validated['component'],
                    (float) $validated['duration']
                );
            }

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            Log::debug('Failed to record Livewire metrics', ['error' => $e->getMessage()]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Record page load metrics.
     */
    public function pageLoad(PageLoadRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $this->collector->recordPageLoad(
                $validated['page'],
                (float) $validated['load_duration'],
                isset($validated['dom_ready_duration']) ? (float) $validated['dom_ready_duration'] : null
            );

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            Log::debug('Failed to record page load', ['error' => $e->getMessage()]);

            return response()->json(['status' => 'error'], 500);
        }
    }
}
