# Livewire Component Contracts

**Branch**: `001-discovery-engine` | **Date**: 2025-12-08

This document defines the public interface contracts for all Livewire components in the Discovery Engine.

---

## Dashboard Components

### Dashboard\Dashboard

Main dashboard view with campaign summaries and activity feed.

**Public Properties:**
```php
public Collection $activeCampaigns;      // Active campaigns with metrics
public Collection $recentResponses;       // Last 10 responses requiring review
public array $weeklyMetrics;              // Aggregate metrics for past 7 days
public Collection $mailboxHealth;         // Mailbox status summary
```

**Actions:**
```php
public function mount(): void
public function refresh(): void           // Refresh all dashboard data
```

**Events Emitted:**
- None (read-only component)

**Events Listened:**
- `response-received` - Refresh recent responses
- `campaign-updated` - Refresh campaign list

---

## Mailbox Components

### Mailbox\MailboxList

Displays all user mailboxes with health status.

**Public Properties:**
```php
public Collection $mailboxes;
public ?string $filterStatus = null;      // 'active', 'paused', 'error', 'warmup'
```

**Actions:**
```php
public function mount(): void
public function pause(int $mailboxId): void
public function resume(int $mailboxId): void
public function delete(int $mailboxId): void
```

**Events Emitted:**
- `mailbox-updated` - After status change
- `mailbox-deleted` - After deletion

---

### Mailbox\MailboxForm

Create/edit mailbox with credential validation.

**Public Properties:**
```php
public ?Mailbox $mailbox = null;          // Null for create, populated for edit
public string $name = '';
public string $email_address = '';
public string $smtp_host = '';
public int $smtp_port = 587;
public string $smtp_encryption = 'tls';
public string $smtp_username = '';
public string $smtp_password = '';
public string $imap_host = '';
public int $imap_port = 993;
public string $imap_encryption = 'ssl';
public string $imap_username = '';
public string $imap_password = '';
public int $daily_limit = 50;
public string $send_window_start = '09:00';
public string $send_window_end = '17:00';
public bool $skip_weekends = true;
public string $timezone = 'UTC';
public bool $warmup_enabled = true;

// Validation state
public bool $isValidating = false;
public ?array $validationResult = null;
```

**Validation Rules:**
```php
protected $rules = [
    'name' => 'required|string|max:255',
    'email_address' => 'required|email|max:255',
    'smtp_host' => 'required|string|max:255',
    'smtp_port' => 'required|integer|in:25,465,587,2525',
    'smtp_encryption' => 'required|in:tls,ssl',
    'smtp_username' => 'required|string|max:255',
    'smtp_password' => 'required|string',
    'imap_host' => 'required|string|max:255',
    'imap_port' => 'required|integer|in:143,993',
    'imap_encryption' => 'required|in:tls,ssl',
    'imap_username' => 'required|string|max:255',
    'imap_password' => 'required|string',
    'daily_limit' => 'required|integer|min:1|max:500',
    'send_window_start' => 'required|date_format:H:i',
    'send_window_end' => 'required|date_format:H:i|after:send_window_start',
    'timezone' => 'required|timezone',
];
```

**Actions:**
```php
public function mount(?Mailbox $mailbox = null): void
public function validateCredentials(): void  // Test SMTP/IMAP connection
public function save(): void                  // Save mailbox (encrypts passwords)
```

**Events Emitted:**
- `mailbox-created` - After successful creation
- `mailbox-updated` - After successful update

---

### Mailbox\MailboxHealth

Displays detailed health status for a single mailbox.

**Public Properties:**
```php
public Mailbox $mailbox;
public array $recentStats;                // Last 7 days sending stats
public ?string $lastError;
public int $warmupProgress;               // Percentage complete (0-100)
```

**Actions:**
```php
public function mount(Mailbox $mailbox): void
public function testConnection(): void    // Re-test credentials
public function clearError(): void        // Acknowledge and clear error
```

---

## Campaign Components

### Campaign\CampaignList

Lists all campaigns with filtering and actions.

**Public Properties:**
```php
public Collection $campaigns;
public string $filterStatus = 'all';      // 'all', 'draft', 'active', 'paused', 'completed', 'archived'
public string $sortBy = 'updated_at';
public string $sortDirection = 'desc';
```

**Actions:**
```php
public function mount(): void
public function setFilter(string $status): void
public function sort(string $column): void
public function duplicate(int $campaignId): void
public function archive(int $campaignId): void
public function delete(int $campaignId): void
```

**Events Emitted:**
- `campaign-duplicated`
- `campaign-archived`
- `campaign-deleted`

---

### Campaign\CampaignForm

Create/edit campaign with hypothesis and settings.

**Public Properties:**
```php
public ?Campaign $campaign = null;
public string $name = '';
public int $mailbox_id;
public string $industry = '';
public string $hypothesis = '';
public string $target_persona = '';
public string $success_criteria = '';
```

**Validation Rules:**
```php
protected $rules = [
    'name' => 'required|string|max:255',
    'mailbox_id' => 'required|exists:mailboxes,id',
    'hypothesis' => 'required|string|min:20',
    'industry' => 'nullable|string|max:255',
    'target_persona' => 'nullable|string',
    'success_criteria' => 'nullable|string',
];
```

**Actions:**
```php
public function mount(?Campaign $campaign = null): void
public function save(): void
public function activate(): void          // Change status to 'active'
public function pause(): void             // Change status to 'paused'
```

**Events Emitted:**
- `campaign-created`
- `campaign-updated`
- `campaign-activated`
- `campaign-paused`

---

### Campaign\CampaignInsights

Displays aggregated insights and decision score for a campaign.

**Public Properties:**
```php
public Campaign $campaign;
public array $metrics;                    // response_rate, interest_breakdown, etc.
public array $decisionScore;              // score, factors, recommendation
public Collection $patterns;              // Detected patterns
public Collection $pinnedQuotes;          // Quote board
```

**Actions:**
```php
public function mount(Campaign $campaign): void
public function refresh(): void           // Regenerate insights
public function pinQuote(int $insightId): void
public function unpinQuote(int $insightId): void
public function triggerReanalysis(): void // Re-analyze all responses with AI
```

**Events Emitted:**
- `insights-regenerated`
- `quote-pinned`
- `quote-unpinned`

---

## Lead Components

### Lead\LeadList

Displays and manages leads within a campaign.

**Public Properties:**
```php
public Campaign $campaign;
public Collection $leads;
public string $search = '';
public string $filterStatus = 'all';
public array $selectedLeadIds = [];
public int $perPage = 25;
```

**Actions:**
```php
public function mount(Campaign $campaign): void
public function search(): void
public function setFilter(string $status): void
public function selectAll(): void
public function deselectAll(): void
public function bulkDelete(): void
public function bulkChangeStatus(string $status): void
public function export(): StreamedResponse
```

**Events Emitted:**
- `leads-deleted`
- `leads-status-changed`

---

### Lead\LeadImport

CSV import with column mapping interface.

**Public Properties:**
```php
public Campaign $campaign;
public $file;                             // TemporaryUploadedFile
public array $headers = [];               // Detected CSV headers
public array $columnMapping = [];         // CSV column → database field
public array $previewData = [];           // First 10 rows mapped
public int $step = 1;                     // 1=upload, 2=map, 3=preview, 4=importing
public ?string $batchId = null;           // Queue batch ID for progress
public int $importProgress = 0;           // 0-100 percentage
public array $importErrors = [];          // Validation failures
```

**Validation Rules:**
```php
protected $rules = [
    'file' => 'required|file|mimes:csv,txt|max:10240',
    'columnMapping.email' => 'required',  // Email mapping is required
];
```

**Actions:**
```php
public function mount(Campaign $campaign): void
public function updatedFile(): void       // Analyze uploaded file
public function mapColumns(): void        // Proceed to preview
public function generatePreview(): void   // Show mapped data preview
public function startImport(): void       // Queue import job
public function checkProgress(): void     // Poll batch status
```

**Events Emitted:**
- `import-started`
- `import-completed`
- `import-failed`

---

### Lead\LeadForm

Manual lead creation/edit form.

**Public Properties:**
```php
public Campaign $campaign;
public ?Lead $lead = null;
public string $email = '';
public string $first_name = '';
public string $last_name = '';
public string $company = '';
public string $role = '';
public string $linkedin_url = '';
public array $custom_fields = [];
```

**Validation Rules:**
```php
protected $rules = [
    'email' => 'required|email|max:255',
    'first_name' => 'nullable|string|max:100',
    'last_name' => 'nullable|string|max:100',
    'company' => 'nullable|string|max:255',
    'role' => 'nullable|string|max:255',
    'linkedin_url' => 'nullable|url|max:500',
];
```

**Actions:**
```php
public function mount(Campaign $campaign, ?Lead $lead = null): void
public function save(): void
```

**Events Emitted:**
- `lead-created`
- `lead-updated`

---

## Template Components

### Template\TemplateEditor

Email template creation with variable support and preview.

**Public Properties:**
```php
public Campaign $campaign;
public ?EmailTemplate $template = null;
public string $name = '';
public string $subject = '';
public string $body = '';
public int $sequence_order = 1;
public int $delay_days = 3;
public string $delay_type = 'business';
public bool $is_library_template = false;

// Preview
public ?Lead $previewLead = null;
public string $previewSubject = '';
public string $previewBody = '';
```

**Validation Rules:**
```php
protected $rules = [
    'name' => 'required|string|max:255',
    'subject' => 'required|string|max:500',
    'body' => 'required|string',
    'sequence_order' => 'required|integer|min:1',
    'delay_days' => 'required|integer|min:0|max:30',
    'delay_type' => 'required|in:business,calendar',
];
```

**Actions:**
```php
public function mount(Campaign $campaign, ?EmailTemplate $template = null): void
public function insertVariable(string $variable): void  // Insert at cursor
public function preview(?Lead $lead = null): void       // Render with sample data
public function save(): void
public function saveToLibrary(): void                   // Save as reusable template
```

**Events Emitted:**
- `template-created`
- `template-updated`
- `template-saved-to-library`

---

### Template\SequenceBuilder

Visual sequence builder for multi-email campaigns.

**Public Properties:**
```php
public Campaign $campaign;
public Collection $templates;             // Ordered sequence
public ?int $editingTemplateId = null;
```

**Actions:**
```php
public function mount(Campaign $campaign): void
public function reorder(array $order): void           // Drag-drop reordering
public function addFromLibrary(int $templateId): void // Add library template
public function remove(int $templateId): void
public function updateDelay(int $templateId, int $days): void
```

**Events Emitted:**
- `sequence-updated`
- `template-added`
- `template-removed`

---

## Response Components

### Response\ResponseInbox

Main inbox view for reviewing responses.

**Public Properties:**
```php
public ?Campaign $campaign = null;        // Filter by campaign (null = all)
public Collection $responses;
public string $filterInterest = 'all';    // 'all', 'hot', 'warm', 'cold', 'negative'
public string $filterStatus = 'unreviewed'; // 'all', 'unreviewed', 'reviewed', 'actioned'
public bool $showAutoReplies = false;
public int $perPage = 20;
```

**Actions:**
```php
public function mount(?Campaign $campaign = null): void
public function setInterestFilter(string $interest): void
public function setStatusFilter(string $status): void
public function toggleAutoReplies(): void
public function markReviewed(int $responseId): void
public function markActioned(int $responseId): void
public function bulkMarkReviewed(): void
```

**Events Emitted:**
- `response-reviewed`
- `response-actioned`

---

### Response\ResponseView

Detailed view of a single response with AI analysis.

**Public Properties:**
```php
public Response $response;
public Lead $lead;
public SentEmail $originalEmail;
public array $conversationThread;         // Full email thread

// AI Analysis (editable)
public ?string $interest_level;
public ?string $problem_confirmation;
public ?int $pain_severity;
public ?string $current_solution;
public ?bool $call_interest;
public array $key_quotes;
public ?string $summary;
```

**Actions:**
```php
public function mount(Response $response): void
public function saveAnalysisOverride(): void  // Manual correction of AI fields
public function reanalyze(): void             // Trigger new AI analysis
public function bookCall(): void              // Open call booking flow
public function reply(): void                 // Open reply composer
```

**Events Emitted:**
- `analysis-overridden`
- `analysis-requested`

---

### Response\QuoteBoard

Displays pinned quotes from campaign responses.

**Public Properties:**
```php
public Campaign $campaign;
public Collection $pinnedQuotes;
```

**Actions:**
```php
public function mount(Campaign $campaign): void
public function unpin(int $insightId): void
public function copyQuote(int $insightId): void
```

**Events Emitted:**
- `quote-unpinned`
- `quote-copied`

---

## Shared Event Bus

All components communicate through Laravel's event system. Key events:

| Event | Payload | Triggered By |
|-------|---------|--------------|
| `mailbox-updated` | `Mailbox $mailbox` | MailboxList, MailboxForm |
| `campaign-updated` | `Campaign $campaign` | CampaignForm |
| `campaign-activated` | `Campaign $campaign` | CampaignForm |
| `leads-imported` | `Campaign $campaign, int $count` | LeadImport |
| `response-received` | `Response $response` | ReplyDetectionService |
| `analysis-completed` | `Response $response` | AiAnalysisService |
| `insights-regenerated` | `Campaign $campaign` | CampaignInsights |
