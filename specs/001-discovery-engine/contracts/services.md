# Service Contracts

**Branch**: `001-discovery-engine` | **Date**: 2025-12-08

This document defines the public interface contracts for all service classes in the Discovery Engine.

---

## MailboxService

Handles mailbox connection, validation, and credential management.

```php
namespace App\Services;

use App\Models\Mailbox;

interface MailboxServiceInterface
{
    /**
     * Validate SMTP credentials before saving.
     *
     * @param string $host SMTP server hostname
     * @param int $port SMTP port (25, 465, 587, 2525)
     * @param string $encryption 'tls' or 'ssl'
     * @param string $username SMTP username
     * @param string $password SMTP password (plain text, will be encrypted)
     * @return array{success: bool, message: string}
     */
    public function validateSmtpCredentials(
        string $host,
        int $port,
        string $encryption,
        string $username,
        string $password
    ): array;

    /**
     * Validate IMAP credentials before saving.
     *
     * @param string $host IMAP server hostname
     * @param int $port IMAP port (143, 993)
     * @param string $encryption 'tls' or 'ssl'
     * @param string $username IMAP username
     * @param string $password IMAP password (plain text, will be encrypted)
     * @return array{success: bool, message: string}
     */
    public function validateImapCredentials(
        string $host,
        int $port,
        string $encryption,
        string $username,
        string $password
    ): array;

    /**
     * Create a new mailbox with encrypted credentials.
     *
     * @param int $userId Owner user ID
     * @param array $data Mailbox configuration
     * @return Mailbox
     * @throws \InvalidArgumentException If validation fails
     */
    public function create(int $userId, array $data): Mailbox;

    /**
     * Update mailbox configuration.
     *
     * @param Mailbox $mailbox
     * @param array $data Updated configuration
     * @return Mailbox
     */
    public function update(Mailbox $mailbox, array $data): Mailbox;

    /**
     * Check if mailbox has reached daily sending limit.
     *
     * @param Mailbox $mailbox
     * @return bool
     */
    public function hasReachedDailyLimit(Mailbox $mailbox): bool;

    /**
     * Get current daily limit considering warm-up schedule.
     *
     * @param Mailbox $mailbox
     * @return int
     */
    public function getCurrentDailyLimit(Mailbox $mailbox): int;

    /**
     * Get count of emails sent in last 24 hours.
     *
     * @param Mailbox $mailbox
     * @return int
     */
    public function getSentCountLast24Hours(Mailbox $mailbox): int;

    /**
     * Update warm-up progress daily.
     *
     * @param Mailbox $mailbox
     * @return void
     */
    public function incrementWarmupDay(Mailbox $mailbox): void;

    /**
     * Pause a mailbox (stops sending but preserves config).
     *
     * @param Mailbox $mailbox
     * @return Mailbox
     */
    public function pause(Mailbox $mailbox): Mailbox;

    /**
     * Resume a paused mailbox.
     *
     * @param Mailbox $mailbox
     * @return Mailbox
     */
    public function resume(Mailbox $mailbox): Mailbox;

    /**
     * Record mailbox error and update status.
     *
     * @param Mailbox $mailbox
     * @param string $errorMessage
     * @return void
     */
    public function recordError(Mailbox $mailbox, string $errorMessage): void;

    /**
     * Clear error state from mailbox.
     *
     * @param Mailbox $mailbox
     * @return void
     */
    public function clearError(Mailbox $mailbox): void;
}
```

---

## CampaignService

Manages campaign lifecycle and status transitions.

```php
namespace App\Services;

use App\Models\Campaign;
use App\Models\User;

interface CampaignServiceInterface
{
    /**
     * Create a new campaign.
     *
     * @param User $user Owner
     * @param array $data Campaign data
     * @return Campaign
     */
    public function create(User $user, array $data): Campaign;

    /**
     * Update campaign configuration.
     *
     * @param Campaign $campaign
     * @param array $data
     * @return Campaign
     */
    public function update(Campaign $campaign, array $data): Campaign;

    /**
     * Activate a draft or paused campaign.
     * Queues emails for leads that haven't been contacted.
     *
     * @param Campaign $campaign
     * @return Campaign
     * @throws \DomainException If campaign cannot be activated
     */
    public function activate(Campaign $campaign): Campaign;

    /**
     * Pause an active campaign.
     * Stops new emails but allows existing sequences to complete tracking.
     *
     * @param Campaign $campaign
     * @return Campaign
     */
    public function pause(Campaign $campaign): Campaign;

    /**
     * Mark campaign as completed.
     *
     * @param Campaign $campaign
     * @return Campaign
     */
    public function complete(Campaign $campaign): Campaign;

    /**
     * Archive a campaign (hides from active view).
     *
     * @param Campaign $campaign
     * @return Campaign
     */
    public function archive(Campaign $campaign): Campaign;

    /**
     * Duplicate a campaign with all settings.
     *
     * @param Campaign $campaign
     * @param string $newName
     * @return Campaign New campaign in draft status
     */
    public function duplicate(Campaign $campaign, string $newName): Campaign;

    /**
     * Calculate campaign metrics.
     *
     * @param Campaign $campaign
     * @return array{
     *   total_leads: int,
     *   emails_sent: int,
     *   responses: int,
     *   response_rate: float,
     *   interest_breakdown: array,
     *   problem_validation_rate: float,
     *   avg_pain_severity: float,
     *   calls_booked: int
     * }
     */
    public function calculateMetrics(Campaign $campaign): array;

    /**
     * Calculate decision score based on campaign data.
     *
     * @param Campaign $campaign
     * @return array{
     *   score: int,
     *   recommendation: string,
     *   factors: array
     * }
     */
    public function calculateDecisionScore(Campaign $campaign): array;
}
```

---

## LeadImportService

Handles CSV parsing, column mapping, and bulk lead import.

```php
namespace App\Services;

use App\Models\Campaign;
use Illuminate\Support\Collection;

interface LeadImportServiceInterface
{
    /**
     * Analyze CSV file and extract headers.
     *
     * @param string $filePath Path to uploaded CSV
     * @return array{headers: array, row_count: int, sample_rows: array}
     */
    public function analyzeFile(string $filePath): array;

    /**
     * Generate preview of mapped data.
     *
     * @param string $filePath Path to CSV
     * @param array $columnMapping CSV column => database field mapping
     * @param int $limit Number of rows to preview
     * @return Collection Preview data
     */
    public function generatePreview(
        string $filePath,
        array $columnMapping,
        int $limit = 10
    ): Collection;

    /**
     * Import leads from CSV with column mapping.
     *
     * @param Campaign $campaign Target campaign
     * @param string $filePath Path to CSV
     * @param array $columnMapping CSV column => database field mapping
     * @return array{
     *   imported: int,
     *   updated: int,
     *   skipped: int,
     *   errors: array
     * }
     */
    public function import(
        Campaign $campaign,
        string $filePath,
        array $columnMapping
    ): array;

    /**
     * Queue import job for large files.
     *
     * @param Campaign $campaign
     * @param string $filePath
     * @param array $columnMapping
     * @return string Batch ID for progress tracking
     */
    public function queueImport(
        Campaign $campaign,
        string $filePath,
        array $columnMapping
    ): string;

    /**
     * Check import progress by batch ID.
     *
     * @param string $batchId
     * @return array{
     *   status: string,
     *   progress: int,
     *   processed: int,
     *   failed: int,
     *   errors: array
     * }
     */
    public function checkProgress(string $batchId): array;

    /**
     * Validate a single lead record.
     *
     * @param array $data Lead data
     * @return array{valid: bool, errors: array}
     */
    public function validateLead(array $data): array;
}
```

---

## SendEngineService

Manages email queue, scheduling, and sending.

```php
namespace App\Services;

use App\Models\Campaign;
use App\Models\Lead;
use App\Models\Mailbox;
use App\Models\SentEmail;
use Carbon\Carbon;

interface SendEngineServiceInterface
{
    /**
     * Queue emails for a campaign's pending leads.
     *
     * @param Campaign $campaign
     * @return int Number of emails queued
     */
    public function queueCampaignEmails(Campaign $campaign): int;

    /**
     * Queue the next sequence email for a lead.
     *
     * @param Lead $lead
     * @return ?SentEmail Null if no more sequence emails
     */
    public function queueNextSequenceEmail(Lead $lead): ?SentEmail;

    /**
     * Schedule emails throughout the sending window.
     *
     * @param Mailbox $mailbox
     * @param Carbon $date Date to schedule for
     * @return int Number of emails scheduled
     */
    public function scheduleEmailsForDay(Mailbox $mailbox, Carbon $date): int;

    /**
     * Send a single email via the mailbox's SMTP.
     *
     * @param SentEmail $sentEmail
     * @return array{success: bool, message_id: ?string, error: ?string}
     */
    public function send(SentEmail $sentEmail): array;

    /**
     * Render email template with lead data.
     *
     * @param string $template Template with {{variables}}
     * @param Lead $lead Lead data for interpolation
     * @return string Rendered content
     */
    public function renderTemplate(string $template, Lead $lead): string;

    /**
     * Cancel pending emails for a lead (e.g., after reply).
     *
     * @param Lead $lead
     * @return int Number of emails cancelled
     */
    public function cancelPendingEmails(Lead $lead): int;

    /**
     * Handle bounced email.
     *
     * @param SentEmail $sentEmail
     * @param string $bounceType 'hard' or 'soft'
     * @param string $bounceMessage
     * @return void
     */
    public function handleBounce(
        SentEmail $sentEmail,
        string $bounceType,
        string $bounceMessage
    ): void;

    /**
     * Check if sending is allowed (within window, not weekend).
     *
     * @param Mailbox $mailbox
     * @param Carbon $dateTime
     * @return bool
     */
    public function isSendingAllowed(Mailbox $mailbox, Carbon $dateTime): bool;

    /**
     * Calculate next available send time.
     *
     * @param Mailbox $mailbox
     * @return Carbon
     */
    public function getNextSendTime(Mailbox $mailbox): Carbon;
}
```

---

## ReplyDetectionService

Handles IMAP polling and reply matching.

```php
namespace App\Services;

use App\Models\Mailbox;
use App\Models\Response;
use Illuminate\Support\Collection;

interface ReplyDetectionServiceInterface
{
    /**
     * Poll mailbox for new emails.
     *
     * @param Mailbox $mailbox
     * @return Collection<Response> New responses detected
     */
    public function poll(Mailbox $mailbox): Collection;

    /**
     * Match an incoming email to a sent email.
     *
     * @param array $headers Email headers (Message-ID, In-Reply-To, References, Subject)
     * @param string $fromEmail Sender email address
     * @return ?int SentEmail ID if matched, null otherwise
     */
    public function matchToSentEmail(array $headers, string $fromEmail): ?int;

    /**
     * Check if email is an auto-reply (out-of-office).
     *
     * @param array $headers Email headers
     * @param string $subject Email subject
     * @return bool
     */
    public function isAutoReply(array $headers, string $subject): bool;

    /**
     * Check if email is a bounce notification.
     *
     * @param array $headers Email headers
     * @param string $body Email body
     * @return array{is_bounce: bool, type: ?string, original_recipient: ?string}
     */
    public function isBounce(array $headers, string $body): array;

    /**
     * Parse email content from raw message.
     *
     * @param string $rawEmail Raw email content
     * @return array{
     *   message_id: string,
     *   in_reply_to: ?string,
     *   references: array,
     *   from: string,
     *   subject: string,
     *   body_html: ?string,
     *   body_plain: ?string,
     *   received_at: Carbon
     * }
     */
    public function parseEmail(string $rawEmail): array;

    /**
     * Store a response and update lead status.
     *
     * @param int $sentEmailId Matched sent email
     * @param array $emailData Parsed email data
     * @return Response
     */
    public function storeResponse(int $sentEmailId, array $emailData): Response;

    /**
     * Get full conversation thread for a lead.
     *
     * @param int $leadId
     * @return Collection Ordered sent emails and responses
     */
    public function getConversationThread(int $leadId): Collection;
}
```

---

## AiAnalysisService

Handles Claude API integration for response analysis.

```php
namespace App\Services;

use App\Models\Response;
use App\Models\Campaign;

interface AiAnalysisServiceInterface
{
    /**
     * Analyze a single response with AI.
     *
     * @param Response $response
     * @return array{
     *   interest_level: string,
     *   problem_confirmation: string,
     *   pain_severity: int,
     *   current_solution: ?string,
     *   call_interest: bool,
     *   key_quotes: array,
     *   summary: string,
     *   confidence: float
     * }
     * @throws \App\Exceptions\AiAnalysisException On API failure
     */
    public function analyze(Response $response): array;

    /**
     * Queue response for background analysis.
     *
     * @param Response $response
     * @return void
     */
    public function queueAnalysis(Response $response): void;

    /**
     * Batch re-analyze all responses in a campaign.
     *
     * @param Campaign $campaign
     * @return string Batch ID for progress tracking
     */
    public function batchReanalyze(Campaign $campaign): string;

    /**
     * Build the analysis prompt for a response.
     *
     * @param Response $response
     * @return string
     */
    public function buildPrompt(Response $response): string;

    /**
     * Get the JSON schema for structured output.
     *
     * @return array
     */
    public function getAnalysisSchema(): array;

    /**
     * Check if analysis is rate limited.
     *
     * @return bool
     */
    public function isRateLimited(): bool;

    /**
     * Get estimated cost for analyzing a campaign.
     *
     * @param Campaign $campaign
     * @return array{input_tokens: int, output_tokens: int, estimated_cost: float}
     */
    public function estimateCost(Campaign $campaign): array;
}
```

---

## InsightService

Detects patterns and generates campaign insights.

```php
namespace App\Services;

use App\Models\Campaign;
use App\Models\Insight;
use Illuminate\Support\Collection;

interface InsightServiceInterface
{
    /**
     * Generate insights for a campaign.
     * Requires minimum 10 analyzed responses.
     *
     * @param Campaign $campaign
     * @return Collection<Insight>
     */
    public function generateInsights(Campaign $campaign): Collection;

    /**
     * Detect recurring patterns across responses.
     *
     * @param Campaign $campaign
     * @return Collection<Insight> Pattern insights
     */
    public function detectPatterns(Campaign $campaign): Collection;

    /**
     * Extract common themes from responses.
     *
     * @param Campaign $campaign
     * @return Collection<Insight> Theme insights
     */
    public function detectThemes(Campaign $campaign): Collection;

    /**
     * Identify common objections.
     *
     * @param Campaign $campaign
     * @return Collection<Insight> Objection insights
     */
    public function detectObjections(Campaign $campaign): Collection;

    /**
     * Extract notable quotes from responses.
     *
     * @param Campaign $campaign
     * @return Collection<Insight> Quote insights
     */
    public function extractQuotes(Campaign $campaign): Collection;

    /**
     * Pin a quote to the quote board.
     *
     * @param Insight $insight
     * @return Insight
     */
    public function pinQuote(Insight $insight): Insight;

    /**
     * Unpin a quote from the quote board.
     *
     * @param Insight $insight
     * @return Insight
     */
    public function unpinQuote(Insight $insight): Insight;

    /**
     * Get pinned quotes for a campaign.
     *
     * @param Campaign $campaign
     * @return Collection<Insight>
     */
    public function getPinnedQuotes(Campaign $campaign): Collection;
}
```

---

## Job Contracts

### SendEmailJob

```php
namespace App\Jobs;

use App\Models\SentEmail;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailJob implements ShouldQueue
{
    public int $tries = 3;
    public array $backoff = [60, 300, 900];
    public int $timeout = 60;

    public function __construct(
        public int $sentEmailId
    ) {}

    public function handle(SendEngineServiceInterface $sendEngine): void;
    public function failed(\Throwable $exception): void;
    public function middleware(): array; // Rate limiting middleware
}
```

### PollMailboxJob

```php
namespace App\Jobs;

use App\Models\Mailbox;
use Illuminate\Contracts\Queue\ShouldQueue;

class PollMailboxJob implements ShouldQueue
{
    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public int $mailboxId
    ) {}

    public function handle(ReplyDetectionServiceInterface $replyDetection): void;
    public function failed(\Throwable $exception): void;
}
```

### AnalyzeResponseJob

```php
namespace App\Jobs;

use App\Models\Response;
use Illuminate\Contracts\Queue\ShouldQueue;

class AnalyzeResponseJob implements ShouldQueue
{
    public int $tries = 3;
    public array $backoff = [30, 120, 480];
    public int $timeout = 120;

    public function __construct(
        public int $responseId
    ) {}

    public function handle(AiAnalysisServiceInterface $aiAnalysis): void;
    public function failed(\Throwable $exception): void;
}
```

### GenerateInsightsJob

```php
namespace App\Jobs;

use App\Models\Campaign;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateInsightsJob implements ShouldQueue
{
    public int $timeout = 300;

    public function __construct(
        public int $campaignId
    ) {}

    public function handle(InsightServiceInterface $insightService): void;
}
```

### ImportLeadsJob

```php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Batchable;

class ImportLeadsJob implements ShouldQueue
{
    use Batchable;

    public int $timeout = 120;

    public function __construct(
        public int $campaignId,
        public array $rows,
        public array $columnMapping
    ) {}

    public function handle(LeadImportServiceInterface $importService): void;
}
```

---

## Event Contracts

```php
// Mailbox Events
class MailboxCreated { public function __construct(public Mailbox $mailbox) {} }
class MailboxUpdated { public function __construct(public Mailbox $mailbox) {} }
class MailboxErrorOccurred { public function __construct(public Mailbox $mailbox, public string $error) {} }

// Campaign Events
class CampaignActivated { public function __construct(public Campaign $campaign) {} }
class CampaignPaused { public function __construct(public Campaign $campaign) {} }
class CampaignCompleted { public function __construct(public Campaign $campaign) {} }

// Lead Events
class LeadsImported { public function __construct(public Campaign $campaign, public int $count) {} }
class LeadStatusChanged { public function __construct(public Lead $lead, public string $oldStatus, public string $newStatus) {} }

// Email Events
class EmailSent { public function __construct(public SentEmail $sentEmail) {} }
class EmailBounced { public function __construct(public SentEmail $sentEmail, public string $bounceType) {} }
class EmailFailed { public function __construct(public SentEmail $sentEmail, public string $error) {} }

// Response Events
class ResponseReceived { public function __construct(public Response $response) {} }
class ResponseAnalyzed { public function __construct(public Response $response) {} }
class AnalysisFailed { public function __construct(public Response $response, public string $error) {} }

// Insight Events
class InsightsGenerated { public function __construct(public Campaign $campaign, public int $count) {} }
```
