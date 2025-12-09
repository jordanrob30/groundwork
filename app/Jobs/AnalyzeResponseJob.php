<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Exceptions\AiAnalysisException;
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
        if ($this->batch()?->cancelled()) {
            return;
        }

        if (! $this->response->needsAnalysis()) {
            return;
        }

        try {
            $service->analyze($this->response);
            Log::info("Analyzed response {$this->response->id}");
        } catch (AiAnalysisException $e) {
            Log::error("Failed to analyze response {$this->response->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->response->markAnalysisFailed();
    }
}
