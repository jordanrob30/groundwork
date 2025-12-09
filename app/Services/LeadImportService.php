<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\LeadsImported;
use App\Jobs\ImportLeadsJob;
use App\Models\Campaign;
use App\Models\Lead;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\LazyCollection;
use League\Csv\Reader;
use League\Csv\Statement;

class LeadImportService
{
    public const REQUIRED_FIELD = 'email';

    public const MAPPABLE_FIELDS = [
        'email' => 'Email Address',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'company' => 'Company',
        'role' => 'Role/Title',
        'linkedin_url' => 'LinkedIn URL',
        'custom_field_1' => 'Custom Field 1',
        'custom_field_2' => 'Custom Field 2',
        'custom_field_3' => 'Custom Field 3',
        'custom_field_4' => 'Custom Field 4',
        'custom_field_5' => 'Custom Field 5',
    ];

    public function analyzeFile(string $filePath): array
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $headers = $csv->getHeader();
        $totalRows = count($csv);

        $preview = [];
        $statement = Statement::create()->offset(0)->limit(5);
        foreach ($statement->process($csv) as $record) {
            $preview[] = $record;
        }

        return [
            'headers' => $headers,
            'total_rows' => $totalRows,
            'preview' => $preview,
            'suggested_mapping' => $this->suggestMapping($headers),
        ];
    }

    protected function suggestMapping(array $headers): array
    {
        $mapping = [];
        $normalizedHeaders = array_map(fn ($h) => strtolower(trim($h)), $headers);

        $patterns = [
            'email' => ['email', 'e-mail', 'email_address', 'emailaddress', 'mail'],
            'first_name' => ['first_name', 'firstname', 'first', 'fname', 'given_name'],
            'last_name' => ['last_name', 'lastname', 'last', 'lname', 'surname', 'family_name'],
            'company' => ['company', 'company_name', 'companyname', 'organization', 'org'],
            'role' => ['role', 'title', 'job_title', 'jobtitle', 'position'],
            'linkedin_url' => ['linkedin', 'linkedin_url', 'linkedinurl', 'li_url', 'linkedin_profile'],
        ];

        foreach ($patterns as $field => $possibleNames) {
            foreach ($possibleNames as $name) {
                $index = array_search($name, $normalizedHeaders);
                if ($index !== false) {
                    $mapping[$field] = $headers[$index];
                    break;
                }
            }
        }

        return $mapping;
    }

    public function generatePreview(string $filePath, array $columnMapping, int $limit = 10): array
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $statement = Statement::create()->offset(0)->limit($limit);
        $preview = [];

        foreach ($statement->process($csv) as $record) {
            $mapped = $this->mapRow($record, $columnMapping);
            $validation = $this->validateLead($mapped);
            $preview[] = [
                'data' => $mapped,
                'valid' => $validation['valid'],
                'errors' => $validation['errors'],
            ];
        }

        return $preview;
    }

    protected function mapRow(array $row, array $columnMapping): array
    {
        $mapped = [];

        foreach (self::MAPPABLE_FIELDS as $field => $label) {
            $csvColumn = $columnMapping[$field] ?? null;
            if ($csvColumn && isset($row[$csvColumn])) {
                $mapped[$field] = trim($row[$csvColumn]);
            } else {
                $mapped[$field] = null;
            }
        }

        return $mapped;
    }

    public function validateLead(array $data): array
    {
        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'custom_field_1' => ['nullable', 'string', 'max:255'],
            'custom_field_2' => ['nullable', 'string', 'max:255'],
            'custom_field_3' => ['nullable', 'string', 'max:255'],
            'custom_field_4' => ['nullable', 'string', 'max:255'],
            'custom_field_5' => ['nullable', 'string', 'max:255'],
        ]);

        return [
            'valid' => ! $validator->fails(),
            'errors' => $validator->errors()->toArray(),
        ];
    }

    public function import(Campaign $campaign, string $filePath, array $columnMapping): array
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        $records = LazyCollection::make(function () use ($csv) {
            foreach ($csv as $record) {
                yield $record;
            }
        });

        $records->chunk(100)->each(function ($chunk) use ($campaign, $columnMapping, &$imported, &$skipped, &$errors) {
            $leadsToUpsert = [];

            foreach ($chunk as $index => $record) {
                $mapped = $this->mapRow($record, $columnMapping);
                $validation = $this->validateLead($mapped);

                if (! $validation['valid']) {
                    $skipped++;
                    $errors[] = [
                        'row' => $index + 2,
                        'data' => $mapped,
                        'errors' => $validation['errors'],
                    ];

                    continue;
                }

                $leadsToUpsert[] = array_merge($mapped, [
                    'campaign_id' => $campaign->id,
                    'status' => Lead::STATUS_PENDING,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $imported++;
            }

            if (! empty($leadsToUpsert)) {
                Lead::upsert(
                    $leadsToUpsert,
                    ['email', 'campaign_id'],
                    ['first_name', 'last_name', 'company', 'role', 'linkedin_url',
                        'custom_field_1', 'custom_field_2', 'custom_field_3', 'custom_field_4', 'custom_field_5',
                        'updated_at']
                );
            }
        });

        event(new LeadsImported($campaign, $imported));

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 100),
        ];
    }

    public function queueImport(Campaign $campaign, string $filePath, array $columnMapping): string
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);
        $totalRows = count($csv);

        $jobs = [];
        $chunkSize = 500;

        for ($offset = 0; $offset < $totalRows; $offset += $chunkSize) {
            $jobs[] = new ImportLeadsJob(
                $campaign,
                $filePath,
                $columnMapping,
                $offset,
                $chunkSize
            );
        }

        $batch = Bus::batch($jobs)
            ->name("Import leads for campaign {$campaign->id}")
            ->allowFailures()
            ->dispatch();

        return $batch->id;
    }

    public function getBatchProgress(string $batchId): ?array
    {
        $batch = Bus::findBatch($batchId);

        if (! $batch) {
            return null;
        }

        return [
            'id' => $batch->id,
            'name' => $batch->name,
            'total_jobs' => $batch->totalJobs,
            'pending_jobs' => $batch->pendingJobs,
            'processed_jobs' => $batch->processedJobs(),
            'failed_jobs' => $batch->failedJobs,
            'progress' => $batch->progress(),
            'finished' => $batch->finished(),
            'cancelled' => $batch->cancelled(),
        ];
    }
}
