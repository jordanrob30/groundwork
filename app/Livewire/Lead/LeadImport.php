<?php

declare(strict_types=1);

namespace App\Livewire\Lead;

use App\Models\Campaign;
use App\Services\LeadImportService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class LeadImport extends Component
{
    use WithFileUploads;

    public Campaign $campaign;

    public $file;

    public array $headers = [];

    public array $columnMapping = [];

    public array $previewData = [];

    public int $step = 1;

    public ?string $batchId = null;

    public int $importProgress = 0;

    public array $importErrors = [];

    public ?array $importResult = null;

    public int $totalRows = 0;

    protected $rules = [
        'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
    ];

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function updatedFile(): void
    {
        $this->validate();

        $service = app(LeadImportService::class);
        $analysis = $service->analyzeFile($this->file->getRealPath());

        $this->headers = $analysis['headers'];
        $this->totalRows = $analysis['total_rows'];
        $this->columnMapping = $analysis['suggested_mapping'];
        $this->previewData = $analysis['preview'];

        $this->step = 2;
    }

    public function mapColumns(): void
    {
        if (empty($this->columnMapping['email'])) {
            $this->addError('columnMapping.email', 'Email column mapping is required.');

            return;
        }

        $this->generatePreview();
        $this->step = 3;
    }

    public function generatePreview(): void
    {
        $service = app(LeadImportService::class);
        $this->previewData = $service->generatePreview(
            $this->file->getRealPath(),
            $this->columnMapping,
            10
        );
    }

    public function startImport(): void
    {
        $this->step = 4;
        $this->importProgress = 0;

        $service = app(LeadImportService::class);

        if ($this->totalRows > 1000) {
            $this->batchId = $service->queueImport(
                $this->campaign,
                $this->file->getRealPath(),
                $this->columnMapping
            );
            $this->dispatch('import-started');
        } else {
            $this->importResult = $service->import(
                $this->campaign,
                $this->file->getRealPath(),
                $this->columnMapping
            );
            $this->importProgress = 100;
            $this->importErrors = $this->importResult['errors'];
            $this->dispatch('import-completed');
        }
    }

    public function checkProgress(): void
    {
        if (! $this->batchId) {
            return;
        }

        $service = app(LeadImportService::class);
        $progress = $service->getBatchProgress($this->batchId);

        if ($progress) {
            $this->importProgress = $progress['progress'];

            if ($progress['finished']) {
                $this->importResult = [
                    'imported' => $progress['processed_jobs'] * 500,
                    'failed_jobs' => $progress['failed_jobs'],
                ];
                $this->dispatch('import-completed');
            }
        }
    }

    public function goBack(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function resetImport(): void
    {
        $this->file = null;
        $this->headers = [];
        $this->columnMapping = [];
        $this->previewData = [];
        $this->step = 1;
        $this->batchId = null;
        $this->importProgress = 0;
        $this->importErrors = [];
        $this->importResult = null;
        $this->totalRows = 0;
    }

    public function render(): View
    {
        return view('livewire.lead.lead-import', [
            'mappableFields' => LeadImportService::MAPPABLE_FIELDS,
        ]);
    }
}
