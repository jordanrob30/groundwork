# Research: Discovery Engine

**Branch**: `001-discovery-engine` | **Date**: 2025-12-08

## Overview

This document consolidates research findings for implementing the Discovery Engine platform. All technical decisions align with the project constitution (Laravel 11, PHP 8.3, Livewire 3, MySQL 8, Redis, Claude API).

---

## 1. SMTP/IMAP Integration

### Decision: Symfony Mailer with Dynamic Transport + Webklex/laravel-imap

**Rationale:**
- Laravel 11 uses Symfony Mailer (Swift Mailer deprecated)
- Symfony Mailer supports runtime transport creation with user-specific credentials
- Webklex/laravel-imap is the most popular Laravel IMAP package (1.2M+ downloads) with OAuth2 support

**Alternatives Considered:**
- Config-based mailer switching: Requires pre-configured mailers, not runtime credentials
- Native PHP IMAP extension: Lacks Laravel integration conveniences
- DirectoryTree/ImapEngine: Newer but less battle-tested

**Implementation Notes:**

```php
// Dynamic SMTP Transport (Symfony Mailer)
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Mailer;

$transport = new EsmtpTransport(
    $mailbox->smtp_host,
    $mailbox->smtp_port,
    $mailbox->smtp_encryption === 'ssl'
);
$transport->setUsername($mailbox->smtp_username);
$transport->setPassword(decrypt($mailbox->smtp_password));

$mailer = new Mailer($transport);
```

```php
// IMAP Polling (Webklex)
use Webklex\PHPIMAP\Client;

$client = Client::make([
    'host' => $mailbox->imap_host,
    'port' => $mailbox->imap_port,
    'encryption' => 'ssl',
    'username' => $mailbox->imap_username,
    'password' => decrypt($mailbox->imap_password),
]);

$client->connect();
$folder = $client->getFolder('INBOX');
$messages = $folder->query()->unseen()->get();
```

**Key Considerations:**
- Gmail/Outlook require OAuth2 (password auth deprecated)
- Store refresh tokens encrypted for token regeneration
- Validate both SMTP and IMAP before saving credentials
- Use Laravel's `encrypt()` for all credential storage (Constitution V)

---

## 2. Claude API Integration

### Decision: claude-php/claude-php-sdk-laravel with Structured Outputs

**Rationale:**
- Official Laravel integration with service provider and facade
- Structured outputs (beta) guarantee 100% schema-compliant JSON responses
- Eliminates parsing errors and retry logic for malformed responses
- Supports prompt caching for 90% cost reduction on repeated context

**Alternatives Considered:**
- Direct Guzzle HTTP calls: Requires manual implementation of all features
- Prefilling responses: Less reliable than structured outputs
- Tool use for JSON: More complex, designed for different use case

**Implementation Notes:**

```php
// Installation
composer require claude-php/claude-php-sdk-laravel

// .env
ANTHROPIC_API_KEY=your_api_key_here
```

```php
// Response Analysis with Structured Output
use ClaudePHP\Facades\Claude;

$analysisSchema = [
    'type' => 'object',
    'properties' => [
        'interest_level' => [
            'type' => 'string',
            'enum' => ['hot', 'warm', 'cold', 'negative']
        ],
        'problem_confirmation' => [
            'type' => 'string',
            'enum' => ['yes', 'no', 'different', 'unclear']
        ],
        'pain_severity' => [
            'type' => 'integer',
            'minimum' => 1,
            'maximum' => 5
        ],
        'current_solution' => ['type' => 'string'],
        'call_interest' => ['type' => 'boolean'],
        'key_quotes' => [
            'type' => 'array',
            'items' => ['type' => 'string'],
            'maxItems' => 3
        ],
        'summary' => ['type' => 'string', 'maxLength' => 200],
        'confidence' => ['type' => 'number', 'minimum' => 0, 'maximum' => 1]
    ],
    'required' => ['interest_level', 'problem_confirmation', 'pain_severity',
                   'call_interest', 'summary', 'confidence']
];

$response = Claude::messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 2048,
    'messages' => [['role' => 'user', 'content' => $prompt]],
    'output_format' => [
        'type' => 'json_schema',
        'json_schema' => [
            'name' => 'email_analysis',
            'strict' => true,
            'schema' => $analysisSchema
        ]
    ]
]);

$analysis = json_decode($response['content'][0]['text'], true);
```

**Cost Optimization:**
- Use prompt caching for system prompts (90% savings after first request)
- Batch API for bulk re-analysis (50% discount)
- Limit output tokens with schema constraints
- Skip AI for very short responses (<100 chars)

**Error Handling:**
- Exponential backoff with jitter for rate limits
- Circuit breaker pattern for API outages
- Respect `retry-after` headers from 429 responses

---

## 3. Email Threading & Reply Detection

### Decision: Message-ID + References + In-Reply-To Headers with JWZ Algorithm

**Rationale:**
- Industry-standard approach per RFC 5322
- References header contains full thread history (most reliable)
- JWZ algorithm is battle-tested (6+ years, millions of users)
- Subject matching only as fallback (unreliable due to international variations)

**Alternatives Considered:**
- Subject-only matching: Unreliable (Re:, AW:, RES:, Fwd: variations)
- In-Reply-To only: Missing in many clients, provides only immediate parent
- Pure subject line: Too many false positives

**Implementation Notes:**

```php
// Matching Priority
class EmailThreadMatcher
{
    public function matchReply(IncomingEmail $email): ?int
    {
        // 1. Check References header (most reliable)
        if ($references = $email->getReferences()) {
            foreach (array_reverse($references) as $ref) {
                if ($match = $this->findByMessageId($ref)) {
                    return $match->conversation_id;
                }
            }
        }

        // 2. Check In-Reply-To header
        if ($inReplyTo = $email->getInReplyTo()) {
            if ($match = $this->findByMessageId($inReplyTo)) {
                return $match->conversation_id;
            }
        }

        // 3. Subject-based matching (last resort, time-constrained)
        return $this->matchBySubject($email);
    }

    private function normalizeSubject(string $subject): string
    {
        // Remove Re:, Fwd:, AW:, RES: prefixes
        return strtolower(preg_replace(
            '/^(Re|RE|re|AW|Aw|RES|Res|FW|Fw|Fwd|FWD):\s*/i',
            '',
            trim($subject)
        ));
    }
}
```

**Auto-Reply Detection (RFC 3834):**

```php
public function isAutoReply(array $headers): bool
{
    // 1. Auto-Submitted header (RFC 3834)
    if (($headers['Auto-Submitted'] ?? 'no') !== 'no') {
        return true;
    }

    // 2. Microsoft Exchange header
    if (isset($headers['X-Auto-Response-Suppress'])) {
        return true;
    }

    // 3. Common auto-reply headers
    $autoHeaders = ['X-Autoreply', 'X-Autorespond', 'X-Out-of-Office'];
    foreach ($autoHeaders as $header) {
        if (isset($headers[$header])) return true;
    }

    // 4. Subject patterns
    $patterns = ['out of office', 'automatic reply', 'vacation'];
    foreach ($patterns as $pattern) {
        if (stripos($headers['Subject'] ?? '', $pattern) !== false) {
            return true;
        }
    }

    return false;
}
```

**Database Schema for Threading:**

```sql
-- Store Message-ID for matching
ALTER TABLE sent_emails ADD COLUMN message_id VARCHAR(255) UNIQUE;
ALTER TABLE sent_emails ADD INDEX idx_message_id (message_id);

-- Store references for reply matching
CREATE TABLE message_references (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    sent_email_id BIGINT UNSIGNED,
    reference_message_id VARCHAR(255),
    position INT,
    INDEX idx_reference (reference_message_id(100))
);
```

---

## 4. CSV Import with Column Mapping

### Decision: League CSV + LazyCollections + Custom Livewire Component

**Rationale:**
- League CSV is the most recommended library in Laravel ecosystem
- PHP Generators with LazyCollections process line-by-line (constant memory)
- Custom Livewire component provides full control over UI and validation
- upsert() is 95% faster than updateOrCreate() for bulk operations

**Alternatives Considered:**
- coderflex/laravel-csv: Good package but less flexibility
- Native fgetcsv(): Works but lacks object-oriented API
- Spatie Simple Excel: Heavier due to PhpSpreadsheet dependency

**Implementation Notes:**

```php
// Installation
composer require league/csv

// Livewire Component for Column Mapping
class LeadImport extends Component
{
    use WithFileUploads;

    public $file;
    public $headers = [];
    public $columnMapping = [];
    public $previewData = [];

    public function analyzeFile()
    {
        $csv = Reader::createFromPath($this->file->getRealPath());
        $csv->setHeaderOffset(0);
        $this->headers = $csv->getHeader();
    }

    public function generatePreview()
    {
        $csv = Reader::createFromPath($this->file->getRealPath());
        $csv->setHeaderOffset(0);

        $this->previewData = collect($csv->getRecords())
            ->take(10)
            ->map(fn($row) => $this->mapRow($row))
            ->toArray();
    }

    public function import()
    {
        // Queue the import job
        ImportLeadsJob::dispatch(
            $this->file->store('imports'),
            $this->columnMapping,
            $this->campaign->id
        );
    }
}
```

```php
// Bulk Import with Duplicate Detection
public function handle()
{
    $csv = Reader::createFromPath(storage_path('app/' . $this->filePath));
    $csv->setHeaderOffset(0);

    DB::statement('SET autocommit=0');
    DB::statement('SET unique_checks=0');

    LazyCollection::make($csv->getRecords())
        ->chunk(500)
        ->each(function($chunk) {
            Lead::upsert(
                $chunk->map(fn($row) => $this->mapRow($row))->toArray(),
                ['email', 'campaign_id'], // Unique keys
                ['first_name', 'last_name', 'company', 'updated_at']
            );
        });

    DB::statement('SET unique_checks=1');
    DB::statement('SET autocommit=1');
}
```

**Migration for Duplicate Detection:**

```php
Schema::table('leads', function (Blueprint $table) {
    $table->unique(['email', 'campaign_id'], 'unique_email_per_campaign');
});
```

---

## 5. Email Warm-up & Scheduling

### Decision: Progressive Volume Increase + Redis Rate Limiting + Business Day Scheduling

**Rationale:**
- Industry standard: 2-4 weeks warm-up starting at 10 emails/day
- 20% daily increase provides steady reputation building
- Redis throttling provides precise per-minute/hour rate control
- Business day scheduling (Mon-Fri) improves B2B engagement

**Alternatives Considered:**
- Fixed incremental increases: Less adaptive
- Calendar day counting: Doesn't match provider limits (rolling 24-hour)
- Third-party warm-up services: Adds cost and complexity

**Implementation Notes:**

**Warm-up Schedule:**

```php
class Mailbox extends Model
{
    protected static $defaultWarmupSchedule = [
        // Week 1: 10 → 40 emails/day
        1 => 10, 2 => 12, 3 => 15, 4 => 18, 5 => 22, 6 => 27, 7 => 32,
        // Week 2: 40 → 100 emails/day
        8 => 38, 9 => 45, 10 => 54, 11 => 65, 12 => 78, 13 => 93, 14 => 100,
        // Week 3+: Hold at target
    ];

    public function getCurrentDailyLimit(): int
    {
        if (!$this->warmup_enabled) {
            return $this->daily_limit;
        }

        $day = $this->warmup_started_at->diffInDays(now()) + 1;
        return self::$defaultWarmupSchedule[$day] ?? $this->daily_limit;
    }
}
```

**Rate Limiting with Redis:**

```php
class SendEmailJob implements ShouldQueue
{
    public function handle()
    {
        $mailbox = Mailbox::find($this->mailboxId);
        $limit = $mailbox->getCurrentDailyLimit();

        // Check daily limit
        $dailyKey = "mailbox:{$mailbox->id}:daily:" . now()->format('Y-m-d');
        $sent = Redis::get($dailyKey) ?? 0;

        if ($sent >= $limit) {
            // Release to tomorrow
            $this->release($this->calculateDelayToTomorrow());
            return;
        }

        // Apply per-minute throttling
        Redis::throttle("mailbox:{$mailbox->id}:throttle")
            ->allow(2) // 2 per minute
            ->every(60)
            ->then(function() use ($mailbox, $dailyKey) {
                $this->sendEmail($mailbox);
                Redis::incr($dailyKey);
                Redis::expire($dailyKey, 86400);
            }, function() {
                $this->release(30);
            });
    }
}
```

**Send Distribution:**

```php
public function scheduleEmailsForDay(Campaign $campaign)
{
    $mailbox = $campaign->mailbox;
    $limit = $mailbox->getCurrentDailyLimit();

    $windowStart = Carbon::parse($mailbox->send_window_start, $mailbox->timezone);
    $windowEnd = Carbon::parse($mailbox->send_window_end, $mailbox->timezone);
    $windowMinutes = $windowStart->diffInMinutes($windowEnd);

    $pendingEmails = $campaign->pendingEmails()->limit($limit)->get();
    $intervalMinutes = $windowMinutes / max(count($pendingEmails), 1);

    foreach ($pendingEmails as $index => $email) {
        $delay = $windowStart->copy()->addMinutes($index * $intervalMinutes);

        // Add jitter (±15 minutes)
        $delay->addMinutes(rand(-15, 15));

        // Skip weekends
        while ($delay->isWeekend()) {
            $delay->addDay()->setTimeFromTimeString($mailbox->send_window_start);
        }

        SendEmailJob::dispatch($email)->delay($delay);
    }
}
```

---

## 6. Package Dependencies

Based on this research, the following packages should be added:

```bash
# Email Integration
composer require webklex/laravel-imap

# AI Integration
composer require claude-php/claude-php-sdk-laravel

# CSV Processing
composer require league/csv

# Email Parsing (for reply detection)
composer require zbateson/mail-mime-parser
```

---

## Summary of Key Decisions

| Area | Decision | Key Benefit |
|------|----------|-------------|
| SMTP | Symfony Mailer dynamic transport | Runtime credential configuration |
| IMAP | Webklex/laravel-imap | OAuth2 support, Laravel integration |
| AI | claude-php SDK + structured outputs | 100% reliable JSON, cost optimization |
| Threading | Message-ID + References + JWZ | Industry standard, reliable matching |
| CSV | League CSV + LazyCollections | Memory efficient, handles large files |
| Bulk Insert | upsert() with chunking | 95% faster than updateOrCreate |
| Rate Limiting | Redis throttling | Precise per-minute control |
| Warm-up | Progressive 20% daily increase | Optimal deliverability |
| Scheduling | Business days + send window | Better engagement rates |
