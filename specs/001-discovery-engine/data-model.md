# Data Model: Discovery Engine

**Branch**: `001-discovery-engine` | **Date**: 2025-12-08

## Entity Relationship Diagram

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│    User     │───────│   Mailbox   │───────│  Campaign   │
└─────────────┘  1:N  └─────────────┘  1:N  └─────────────┘
                            │                     │
                            │                     │ 1:N
                            │               ┌─────┴─────┐
                            │               │           │
                      ┌─────┴─────┐   ┌─────┴───┐ ┌─────┴─────────┐
                      │ SentEmail │   │  Lead   │ │ EmailTemplate │
                      └─────┬─────┘   └────┬────┘ └───────────────┘
                            │              │
                            │         ┌────┴────┐
                            │         │         │
                      ┌─────┴─────┐   │   ┌─────┴─────┐
                      │ Response  │───┘   │CallBooking│
                      └─────┬─────┘       └───────────┘
                            │
                      ┌─────┴─────┐
                      │  Insight  │
                      └───────────┘
```

---

## Entities

### User

Account owner who conducts discovery research.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| name | varchar(255) | required | User's full name |
| email | varchar(255) | required, unique | Login email |
| email_verified_at | timestamp | nullable | Email verification timestamp |
| password | varchar(255) | required | Hashed password |
| remember_token | varchar(100) | nullable | Session token |
| created_at | timestamp | required | Account creation |
| updated_at | timestamp | required | Last update |

**Relationships:**
- Has many `Mailbox`
- Has many `Campaign`

---

### Mailbox

Email account configuration for sending and receiving.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| user_id | bigint | FK, required | Owner reference |
| name | varchar(255) | required | Display name (e.g., "Work Gmail") |
| email_address | varchar(255) | required | From address |
| status | enum | required | 'active', 'paused', 'error', 'warmup' |
| smtp_host | varchar(255) | required | SMTP server host |
| smtp_port | int | required | SMTP port (587, 465) |
| smtp_encryption | enum | required | 'tls', 'ssl' |
| smtp_username | varchar(255) | required | SMTP username |
| smtp_password | text | required | **Encrypted** SMTP password |
| imap_host | varchar(255) | required | IMAP server host |
| imap_port | int | required | IMAP port (993) |
| imap_encryption | enum | required | 'ssl', 'tls' |
| imap_username | varchar(255) | required | IMAP username |
| imap_password | text | required | **Encrypted** IMAP password |
| uses_oauth | boolean | default false | OAuth2 authentication flag |
| oauth_provider | varchar(50) | nullable | 'google', 'microsoft' |
| oauth_access_token | text | nullable | **Encrypted** OAuth access token |
| oauth_refresh_token | text | nullable | **Encrypted** OAuth refresh token |
| oauth_expires_at | timestamp | nullable | Token expiration |
| daily_limit | int | default 50 | Max emails per day |
| warmup_enabled | boolean | default true | Warm-up mode active |
| warmup_started_at | date | nullable | Warm-up start date |
| warmup_day | int | default 0 | Days since warm-up started |
| send_window_start | time | default '09:00' | Sending window start |
| send_window_end | time | default '17:00' | Sending window end |
| skip_weekends | boolean | default true | Skip weekend sending |
| timezone | varchar(50) | default 'UTC' | Mailbox timezone |
| last_polled_at | timestamp | nullable | Last IMAP poll |
| error_message | text | nullable | Last error details |
| last_error_at | timestamp | nullable | Last error timestamp |
| created_at | timestamp | required | Creation timestamp |
| updated_at | timestamp | required | Last update |

**Relationships:**
- Belongs to `User`
- Has many `Campaign`
- Has many `SentEmail`

**Validation Rules:**
- smtp_port: in [25, 465, 587, 2525]
- imap_port: in [143, 993]
- daily_limit: min 1, max 500
- Credentials validated before save

---

### Campaign

Container for a discovery hypothesis test.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| user_id | bigint | FK, required | Owner reference |
| mailbox_id | bigint | FK, required | Sending mailbox |
| name | varchar(255) | required | Campaign name |
| status | enum | required | 'draft', 'active', 'paused', 'completed', 'archived' |
| industry | varchar(255) | nullable | Target industry |
| hypothesis | text | required | Problem hypothesis to validate |
| target_persona | text | nullable | Ideal customer profile |
| success_criteria | text | nullable | What constitutes validation |
| activated_at | timestamp | nullable | When campaign went active |
| completed_at | timestamp | nullable | When campaign completed |
| archived_at | timestamp | nullable | When campaign archived |
| created_at | timestamp | required | Creation timestamp |
| updated_at | timestamp | required | Last update |

**Relationships:**
- Belongs to `User`
- Belongs to `Mailbox`
- Has many `Lead`
- Has many `EmailTemplate`
- Has many `Insight`

**Computed Metrics (via queries):**
- response_rate: responses / sent_emails
- interest_breakdown: count by interest_level
- problem_validation_rate: confirmed / total_analyzed
- avg_pain_severity: average of pain_severity
- decision_score: calculated from multiple factors

---

### Lead

Individual contact for outreach.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| campaign_id | bigint | FK, required | Campaign reference |
| email | varchar(255) | required | Contact email |
| first_name | varchar(100) | nullable | First name |
| last_name | varchar(100) | nullable | Last name |
| company | varchar(255) | nullable | Company name |
| role | varchar(255) | nullable | Job title/role |
| linkedin_url | varchar(500) | nullable | LinkedIn profile |
| custom_field_1 | varchar(255) | nullable | Custom data field |
| custom_field_2 | varchar(255) | nullable | Custom data field |
| custom_field_3 | varchar(255) | nullable | Custom data field |
| custom_field_4 | varchar(255) | nullable | Custom data field |
| custom_field_5 | varchar(255) | nullable | Custom data field |
| status | enum | required | Lead status (see below) |
| current_sequence_step | int | default 0 | Current email in sequence |
| last_contacted_at | timestamp | nullable | Last email sent |
| replied_at | timestamp | nullable | When reply received |
| bounced_at | timestamp | nullable | When bounce detected |
| unsubscribed_at | timestamp | nullable | When unsubscribed |
| created_at | timestamp | required | Import/creation time |
| updated_at | timestamp | required | Last update |

**Status Values:**
- `pending` - Not yet contacted
- `queued` - In sending queue
- `contacted` - Email(s) sent, awaiting reply
- `replied` - Received response
- `call_booked` - Discovery call scheduled
- `converted` - Validated/converted
- `unsubscribed` - Opted out
- `bounced` - Email bounced

**Relationships:**
- Belongs to `Campaign`
- Has many `SentEmail`
- Has many `Response`
- Has one `CallBooking`

**Indexes:**
- Unique: `(email, campaign_id)` - Prevent duplicates per campaign
- Index: `email` - Fast lookup across campaigns
- Index: `status` - Filter queries

---

### EmailTemplate

Reusable email content with personalization variables.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| campaign_id | bigint | FK, nullable | Campaign reference (null = library) |
| user_id | bigint | FK, required | Owner reference |
| name | varchar(255) | required | Template name |
| subject | varchar(500) | required | Email subject line |
| body | text | required | Email body (HTML) |
| sequence_order | int | nullable | Position in sequence (1, 2, 3...) |
| delay_days | int | default 3 | Days after previous email |
| delay_type | enum | default 'business' | 'business', 'calendar' |
| is_library_template | boolean | default false | Saved to reusable library |
| created_at | timestamp | required | Creation timestamp |
| updated_at | timestamp | required | Last update |

**Relationships:**
- Belongs to `Campaign` (nullable)
- Belongs to `User`

**Supported Variables:**
- `{{first_name}}` - Lead first name
- `{{last_name}}` - Lead last name
- `{{company}}` - Lead company
- `{{role}}` - Lead role/title
- `{{custom_field_1}}` through `{{custom_field_5}}`

---

### SentEmail

Record of an email sent to a lead.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| mailbox_id | bigint | FK, required | Sending mailbox |
| campaign_id | bigint | FK, required | Campaign reference |
| lead_id | bigint | FK, required | Recipient lead |
| template_id | bigint | FK, nullable | Template used |
| message_id | varchar(255) | unique | RFC 5322 Message-ID header |
| subject | varchar(500) | required | Rendered subject |
| body | text | required | Rendered body |
| status | enum | required | 'pending', 'queued', 'sending', 'sent', 'failed', 'bounced' |
| sequence_step | int | required | Which email in sequence (1, 2, 3...) |
| scheduled_for | timestamp | nullable | When to send |
| sent_at | timestamp | nullable | Actual send time |
| opened_at | timestamp | nullable | First open (if tracked) |
| clicked_at | timestamp | nullable | First click (if tracked) |
| bounced_at | timestamp | nullable | Bounce detected |
| bounce_type | enum | nullable | 'hard', 'soft' |
| error_message | text | nullable | Send error details |
| created_at | timestamp | required | Creation timestamp |
| updated_at | timestamp | required | Last update |

**Relationships:**
- Belongs to `Mailbox`
- Belongs to `Campaign`
- Belongs to `Lead`
- Belongs to `EmailTemplate`
- Has one `Response`

**Indexes:**
- Unique: `message_id` - Reply matching
- Index: `(mailbox_id, sent_at)` - Daily limit tracking
- Index: `(lead_id, sequence_step)` - Sequence tracking
- Index: `status` - Queue processing

---

### Response

Reply received from a lead.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| sent_email_id | bigint | FK, required | Original email reference |
| lead_id | bigint | FK, required | Responding lead |
| campaign_id | bigint | FK, required | Campaign reference |
| message_id | varchar(255) | unique | Reply Message-ID |
| in_reply_to | varchar(255) | nullable | In-Reply-To header |
| subject | varchar(500) | required | Reply subject |
| body | text | required | Reply content |
| body_plain | text | nullable | Plain text version |
| is_auto_reply | boolean | default false | Out-of-office flag |
| received_at | timestamp | required | When received |
| analyzed_at | timestamp | nullable | AI analysis completion |
| analysis_status | enum | default 'pending' | 'pending', 'analyzing', 'completed', 'failed' |
| interest_level | enum | nullable | 'hot', 'warm', 'cold', 'negative' |
| problem_confirmation | enum | nullable | 'yes', 'no', 'different', 'unclear' |
| pain_severity | int | nullable | 1-5 scale |
| current_solution | text | nullable | How they solve it now |
| call_interest | boolean | nullable | Wants a call |
| key_quotes | json | nullable | Array of notable quotes |
| summary | text | nullable | AI-generated summary |
| analysis_confidence | decimal(3,2) | nullable | 0.00-1.00 confidence |
| review_status | enum | default 'unreviewed' | 'unreviewed', 'reviewed', 'actioned' |
| reviewed_at | timestamp | nullable | When marked reviewed |
| created_at | timestamp | required | Creation timestamp |
| updated_at | timestamp | required | Last update |

**Relationships:**
- Belongs to `SentEmail`
- Belongs to `Lead`
- Belongs to `Campaign`

**Indexes:**
- Index: `(campaign_id, interest_level)` - Filter by interest
- Index: `(campaign_id, review_status)` - Inbox filtering
- Index: `analysis_status` - Queue processing

---

### Insight

Aggregated learning detected across multiple responses.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| campaign_id | bigint | FK, required | Campaign reference |
| type | enum | required | 'pattern', 'theme', 'objection', 'quote' |
| title | varchar(255) | required | Insight title |
| description | text | nullable | Detailed description |
| frequency | int | default 1 | How often this appeared |
| response_ids | json | nullable | Array of response IDs |
| is_pinned | boolean | default false | Pinned to quote board |
| created_at | timestamp | required | Creation timestamp |
| updated_at | timestamp | required | Last update |

**Relationships:**
- Belongs to `Campaign`

**Insight Types:**
- `pattern` - Recurring behavior or situation
- `theme` - Common topic across responses
- `objection` - Frequently mentioned concern
- `quote` - Notable customer quote

---

### CallBooking

Scheduled or completed discovery call with a lead.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| lead_id | bigint | FK, required | Lead reference |
| campaign_id | bigint | FK, required | Campaign reference |
| response_id | bigint | FK, nullable | Triggering response |
| scheduled_at | timestamp | nullable | When call is scheduled |
| completed_at | timestamp | nullable | When call completed |
| duration_minutes | int | nullable | Call duration |
| outcome | enum | nullable | 'validated', 'invalidated', 'need_more_info', 'no_show', 'rescheduled' |
| notes | text | nullable | Call notes |
| scheduling_link | varchar(500) | nullable | Calendly/scheduling URL used |
| created_at | timestamp | required | Creation timestamp |
| updated_at | timestamp | required | Last update |

**Relationships:**
- Belongs to `Lead`
- Belongs to `Campaign`
- Belongs to `Response` (optional)

---

## Supporting Tables

### MessageReference

Stores email threading references for reply matching.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| sent_email_id | bigint | FK, required | Parent sent email |
| reference_message_id | varchar(255) | required | Referenced Message-ID |
| position | int | required | Order in References header |

**Indexes:**
- Index: `reference_message_id` - Fast reply matching

---

### MailboxSendingStat

Daily sending statistics per mailbox.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| mailbox_id | bigint | FK, required | Mailbox reference |
| date | date | required | Statistics date |
| emails_sent | int | default 0 | Emails sent |
| emails_delivered | int | default 0 | Confirmed delivered |
| emails_bounced | int | default 0 | Bounced count |
| emails_failed | int | default 0 | Failed to send |
| bounce_rate | decimal(5,2) | default 0 | Bounce percentage |

**Indexes:**
- Unique: `(mailbox_id, date)` - One row per day per mailbox

---

## State Transitions

### Lead Status Flow

```
pending → queued → contacted → replied → call_booked → converted
    │         │         │          │
    │         │         │          └──→ (stays replied if no call)
    │         │         │
    │         │         └──→ bounced (if email bounces)
    │         │
    │         └──→ bounced (if email bounces)
    │
    └──→ unsubscribed (at any point)
```

### Campaign Status Flow

```
draft → active → paused → active → completed → archived
          │                            │
          └────────────────────────────┘
```

### Response Analysis Flow

```
pending → analyzing → completed
              │
              └──→ failed (retry available)
```

---

## Validation Rules Summary

| Entity | Field | Rule |
|--------|-------|------|
| Mailbox | smtp_password | Encrypted via Laravel encrypt() |
| Mailbox | imap_password | Encrypted via Laravel encrypt() |
| Mailbox | oauth_* | Encrypted via Laravel encrypt() |
| Lead | email | Required, valid email format |
| Lead | (email, campaign_id) | Unique combination |
| EmailTemplate | body | Contains valid variable syntax |
| Response | pain_severity | Integer 1-5 |
| Response | analysis_confidence | Decimal 0.00-1.00 |

---

## Indexes Summary

| Table | Index | Columns | Purpose |
|-------|-------|---------|---------|
| leads | unique_email_per_campaign | (email, campaign_id) | Duplicate prevention |
| leads | idx_email | email | Cross-campaign lookup |
| sent_emails | uk_message_id | message_id | Reply matching |
| sent_emails | idx_mailbox_sent | (mailbox_id, sent_at) | Daily limit tracking |
| responses | idx_campaign_interest | (campaign_id, interest_level) | Filtering |
| message_references | idx_reference | reference_message_id | Reply matching |
