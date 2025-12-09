<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Lead;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportLeadsJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public Campaign $campaign,
        public string $filePath,
        public array $columnMapping,
        public int $offset,
        public int $limit
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $csv = Reader::createFromPath($this->filePath, 'r');
        $csv->setHeaderOffset(0);

        $statement = Statement::create()
            ->offset($this->offset)
            ->limit($this->limit);

        $leadsToUpsert = [];

        foreach ($statement->process($csv) as $record) {
            $mapped = $this->mapRow($record);

            if (! $this->isValidLead($mapped)) {
                continue;
            }

            $leadsToUpsert[] = array_merge($mapped, [
                'campaign_id' => $this->campaign->id,
                'status' => Lead::STATUS_PENDING,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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
    }

    protected function mapRow(array $row): array
    {
        $mapped = [];

        $fields = [
            'email', 'first_name', 'last_name', 'company', 'role', 'linkedin_url',
            'custom_field_1', 'custom_field_2', 'custom_field_3', 'custom_field_4', 'custom_field_5',
        ];

        foreach ($fields as $field) {
            $csvColumn = $this->columnMapping[$field] ?? null;
            if ($csvColumn && isset($row[$csvColumn])) {
                $mapped[$field] = trim($row[$csvColumn]);
            } else {
                $mapped[$field] = null;
            }
        }

        return $mapped;
    }

    protected function isValidLead(array $data): bool
    {
        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'max:255'],
        ]);

        return ! $validator->fails();
    }
}
