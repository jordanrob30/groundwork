<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Exceptions\AiAnalysisException;
use App\Metrics\Collectors\CampaignFlowCollector;
use App\Models\Response;
use App\Services\AiAnalysisService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeResponseJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public Response $response
    ) {}

    public function handle(AiAnalysisService $service): void
    {
        // Add campaign context to logs
        Log::shareContext([
            'campaign_id' => $this->response->campaign_id,
            'response_id' => $this->response->id,
        ]);

        Log::info('AnalyzeResponseJob started');

        if ($this->batch()?->cancelled()) {
            Log::info('AnalyzeResponseJob skipped - batch cancelled');

            return;
        }

        if (! $this->response->needsAnalysis()) {
            Log::info('AnalyzeResponseJob skipped - response does not need analysis');

            return;
        }

        $startTime = microtime(true);

        try {
            $service->analyze($this->response);
            $duration = microtime(true) - $startTime;

            Log::info('AnalyzeResponseJob completed', [
                'duration_ms' => round($duration * 1000, 2),
            ]);

            // Record metrics
            try {
                $collector = app(CampaignFlowCollector::class);
                $collector->incrementAnalysis($this->response->campaign_id, 'completed');
                $collector->recordAnalysisDuration($this->response->campaign_id, $duration);
            } catch (\Throwable $e) {
                Log::debug('Failed to record analysis metrics', ['error' => $e->getMessage()]);
            }
        } catch (AiAnalysisException $e) {
            Log::error('AnalyzeResponseJob failed - AI analysis error', [
                'error' => $e->getMessage(),
            ]);

            // Record failed metric
            try {
                $collector = app(CampaignFlowCollector::class);
                $collector->incrementAnalysis($this->response->campaign_id, 'failed');
            } catch (\Throwable $ex) {
                Log::debug('Failed to record analysis failure metric', ['error' => $ex->getMessage()]);
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Add campaign context to logs
        Log::shareContext([
            'campaign_id' => $this->response->campaign_id,
            'response_id' => $this->response->id,
        ]);

        Log::error('AnalyzeResponseJob failed', [
            'error' => $exception->getMessage(),
            'exception_class' => get_class($exception),
        ]);

        $this->response->markAnalysisFailed();

        // Record failed metric
        try {
            $collector = app(CampaignFlowCollector::class);
            $collector->incrementAnalysis($this->response->campaign_id, 'failed');
        } catch (\Throwable $e) {
            Log::debug('Failed to record analysis failure metric', ['error' => $e->getMessage()]);
        }
    }
}
