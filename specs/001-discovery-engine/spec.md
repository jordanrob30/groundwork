# Feature Specification: Discovery Engine

**Feature Branch**: `001-discovery-engine`
**Created**: 2024-12-08
**Status**: Draft
**Input**: User description: "Discovery Engine - Customer discovery automation platform for founders to validate business ideas through systematic cold email outreach with AI-powered response analysis"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Connect Email Account (Priority: P1)

As a founder, I need to connect my email account so that I can send research outreach emails and receive replies through the platform.

**Why this priority**: This is the foundational capability - without email connectivity, no outreach or response collection is possible. Everything else depends on this working.

**Independent Test**: Can be fully tested by connecting an email account, verifying credentials, and confirming the system can send/receive emails. Delivers value by enabling the core communication channel.

**Acceptance Scenarios**:

1. **Given** I am a logged-in user, **When** I enter valid SMTP/IMAP credentials, **Then** the system validates the connection and saves my mailbox configuration
2. **Given** I have entered invalid credentials, **When** I attempt to connect, **Then** the system displays a specific error message explaining what failed
3. **Given** I have a connected mailbox, **When** I pause the mailbox, **Then** no emails are sent but my settings are preserved
4. **Given** I have a new mailbox, **When** I enable warm-up mode, **Then** daily sending limits start low and gradually increase over time

---

### User Story 2 - Create and Launch Discovery Campaign (Priority: P1)

As a founder, I need to create a hypothesis-driven outreach campaign so that I can systematically test my business assumptions with real potential customers.

**Why this priority**: Campaigns are the organizing unit for all discovery work. Users cannot conduct research without this capability.

**Independent Test**: Can be fully tested by creating a campaign with a hypothesis, adding leads, creating templates, and launching. Delivers value by enabling structured outreach.

**Acceptance Scenarios**:

1. **Given** I am a logged-in user with a connected mailbox, **When** I create a campaign with name, industry, hypothesis, and target persona, **Then** the campaign is saved in draft status
2. **Given** I have a draft campaign with leads and templates, **When** I activate the campaign, **Then** emails begin sending according to my schedule and limits
3. **Given** I have an active campaign, **When** I pause it, **Then** no new emails are sent but existing sequences continue tracking
4. **Given** I have a completed campaign, **When** I archive it, **Then** it is hidden from my active view but data is preserved

---

### User Story 3 - Import Leads for Outreach (Priority: P1)

As a founder, I need to import my target contacts so that I can conduct outreach at scale without manual data entry.

**Why this priority**: Lead import enables scale. Without bulk import, users would need to enter contacts one by one, making the platform impractical.

**Independent Test**: Can be fully tested by importing a CSV file, mapping columns, and verifying leads appear in the campaign. Delivers value by enabling efficient contact management.

**Acceptance Scenarios**:

1. **Given** I have a campaign, **When** I upload a CSV file, **Then** I see a column mapping interface showing detected fields
2. **Given** I am mapping CSV columns, **When** I assign columns to lead fields, **Then** the system shows a preview of how data will be imported
3. **Given** my CSV contains duplicate emails, **When** I confirm the import, **Then** the system flags duplicates and lets me choose to skip or update them
4. **Given** I have imported leads, **When** I view the leads list, **Then** I can search, filter, and perform bulk actions

---

### User Story 4 - Create Email Sequence Templates (Priority: P1)

As a founder, I need to create a sequence of research-framed emails so that I can follow up automatically with non-responders.

**Why this priority**: Email templates with sequences are essential for effective outreach. Multi-touch sequences significantly improve response rates.

**Independent Test**: Can be fully tested by creating a 3-email sequence with variables, previewing with sample data, and verifying template saves. Delivers value by enabling personalized, automated follow-ups.

**Acceptance Scenarios**:

1. **Given** I have a campaign, **When** I create a template with subject and body, **Then** I can insert personalization variables like {{first_name}} and {{company}}
2. **Given** I have a template, **When** I add it to a sequence with a delay, **Then** it becomes part of the automated follow-up chain
3. **Given** I have a multi-email sequence, **When** I preview with a sample lead, **Then** I see all emails with variables rendered
4. **Given** I have created templates, **When** I save them to my library, **Then** I can reuse them across different campaigns

---

### User Story 5 - Track and Match Reply Detection (Priority: P1)

As a founder, I need the system to automatically detect and match replies to my outreach so that I can track who responded without manual monitoring.

**Why this priority**: Reply detection is critical for measuring success and triggering the response analysis workflow. Without it, users cannot track engagement.

**Independent Test**: Can be fully tested by sending an email, receiving a reply, and verifying it appears matched to the correct lead and campaign. Delivers value by automating response tracking.

**Acceptance Scenarios**:

1. **Given** I have sent outreach emails, **When** a recipient replies, **Then** the system matches the reply to the original lead within 10 minutes
2. **Given** a reply is detected, **When** the lead had pending follow-up emails, **Then** those follow-ups are automatically cancelled
3. **Given** an auto-reply (out-of-office) is detected, **When** processing the response, **Then** it is flagged as auto-reply and not counted in response metrics
4. **Given** a reply cannot be matched automatically, **When** viewing unmatched emails, **Then** I can manually assign them to leads

---

### User Story 6 - Review AI-Analyzed Responses (Priority: P2)

As a founder, I need AI to analyze responses and extract key insights so that I can quickly understand sentiment and patterns without reading every email in detail.

**Why this priority**: AI analysis transforms raw emails into structured data. While users could manually analyze, this automation dramatically reduces time and improves consistency.

**Independent Test**: Can be fully tested by receiving a response and viewing the AI-extracted fields (interest level, problem confirmation, key quotes). Delivers value by automating insight extraction.

**Acceptance Scenarios**:

1. **Given** a new response is received, **When** AI analysis completes, **Then** I see interest level, problem confirmation status, pain severity, and key quotes extracted
2. **Given** AI has analyzed a response, **When** I disagree with the analysis, **Then** I can manually override any extracted field
3. **Given** responses are analyzed, **When** I filter the response inbox, **Then** I can filter by interest level, problem confirmation, and other AI-extracted fields
4. **Given** I want to improve analysis quality, **When** I trigger batch re-analysis, **Then** all responses are re-processed with the updated analysis approach

---

### User Story 7 - View Campaign Insights and Decision Score (Priority: P2)

As a founder, I need aggregated insights and a decision score so that I can determine whether my hypothesis is validated or if I should pivot.

**Why this priority**: Insights transform individual responses into actionable intelligence. The decision score provides clear guidance for go/no-go decisions.

**Independent Test**: Can be fully tested by viewing a campaign with 10+ responses and seeing aggregated metrics, patterns, and decision score. Delivers value by providing decision-making framework.

**Acceptance Scenarios**:

1. **Given** I have a campaign with responses, **When** I view the insights dashboard, **Then** I see response rate, interest breakdown, problem validation rate, and pain severity average
2. **Given** I have 10+ analyzed responses, **When** patterns are detected, **Then** I see recurring themes, common objections, and unexpected problems surfaced
3. **Given** I have campaign data, **When** viewing the decision score, **Then** I see a score with clear breakdown explaining each factor contributing to the recommendation
4. **Given** responses contain notable quotes, **When** viewing the quote board, **Then** I can browse and pin the most impactful customer quotes

---

### User Story 8 - Book Discovery Calls (Priority: P3)

As a founder, I need to convert interested respondents into scheduled calls so that I can have deeper conversations for validation.

**Why this priority**: Call booking is the bridge between email discovery and deeper customer interviews. Important but not core - users can book calls manually outside the platform.

**Independent Test**: Can be fully tested by clicking "Book Call" from a response, inserting a scheduling link, and tracking the call outcome. Delivers value by streamlining the interview scheduling process.

**Acceptance Scenarios**:

1. **Given** I have a response from an interested lead, **When** I click "Book Call," **Then** my scheduling link is inserted into a reply template
2. **Given** a lead books a call, **When** the event is created, **Then** the lead status updates to "Call Booked"
3. **Given** I completed a call, **When** I record the outcome, **Then** I can mark it as validated, invalidated, or needs more info with notes

---

### User Story 9 - Reply to Responses from Platform (Priority: P3)

As a founder, I need to reply directly from the platform so that I can maintain conversation context and continue discovery conversations.

**Why this priority**: Reply functionality keeps conversations centralized. However, users can reply from their email client, so this is convenient but not critical.

**Independent Test**: Can be fully tested by composing and sending a reply from the response view and verifying it sends through the connected mailbox. Delivers value by keeping conversations in one place.

**Acceptance Scenarios**:

1. **Given** I am viewing a response, **When** I compose a reply, **Then** I can use reply templates and see the full conversation thread
2. **Given** I send a reply, **When** it is delivered, **Then** the conversation thread updates with my sent message
3. **Given** the recipient replies again, **When** the reply is detected, **Then** it is threaded with the existing conversation

---

### User Story 10 - Monitor Dashboard and Mailbox Health (Priority: P3)

As a founder, I need a central dashboard showing activity and mailbox health so that I can quickly assess the state of my discovery efforts.

**Why this priority**: The dashboard provides operational awareness. Users can get this information elsewhere in the system, but a centralized view improves efficiency.

**Independent Test**: Can be fully tested by logging in and viewing active campaigns, recent responses, key metrics, and mailbox status. Delivers value by providing at-a-glance operational awareness.

**Acceptance Scenarios**:

1. **Given** I am logged in, **When** I view the dashboard, **Then** I see active campaigns summary, recent responses requiring review, and key metrics
2. **Given** I have connected mailboxes, **When** a mailbox has connection issues, **Then** I see a health warning with actionable error details
3. **Given** I want to take action, **When** I use quick action buttons, **Then** I can navigate to create campaign, import leads, or view responses

---

### Edge Cases

- What happens when a mailbox exceeds its daily sending limit? The system queues remaining emails for the next day.
- How does the system handle bounced emails? The lead is marked as bounced, the sequence stops, and the bounce is logged.
- What happens if the AI analysis service is unavailable? Responses are stored and queued for analysis when service resumes.
- How does the system handle replies to very old campaigns? Replies are still matched and recorded, even if the campaign is completed/archived.
- What happens when a user imports leads that already exist in another campaign? The system allows the same lead across multiple campaigns (different contexts).
- How does the system handle email threading across multiple replies? All replies in a thread are grouped and displayed together.

## Requirements *(mandatory)*

### Functional Requirements

**Mailbox Management**
- **FR-001**: System MUST allow users to connect email accounts via SMTP/IMAP credentials
- **FR-002**: System MUST validate email credentials before saving a mailbox configuration
- **FR-003**: System MUST enforce configurable daily sending limits per mailbox (default: 50 emails/day)
- **FR-004**: System MUST support a warm-up mode that gradually increases sending limits for new mailboxes
- **FR-005**: System MUST monitor mailbox connection health and display actionable errors when issues occur
- **FR-006**: Users MUST be able to pause and resume mailboxes without losing configuration

**Campaign Management**
- **FR-007**: System MUST allow users to create campaigns with name, industry, hypothesis, target persona, and success criteria
- **FR-008**: System MUST support campaign statuses: Draft, Active, Paused, Completed
- **FR-009**: Users MUST be able to duplicate existing campaigns
- **FR-010**: Users MUST be able to archive completed campaigns

**Lead Management**
- **FR-011**: System MUST support CSV import with a column mapping interface
- **FR-012**: System MUST require email address for each lead (other fields optional)
- **FR-013**: System MUST detect duplicate leads by email within a campaign
- **FR-014**: System MUST support lead statuses: Pending, Queued, Contacted, Replied, Call Booked, Converted, Unsubscribed, Bounced
- **FR-015**: Users MUST be able to manually add leads via a form
- **FR-016**: Users MUST be able to perform bulk actions: delete, move to campaign, change status
- **FR-017**: Users MUST be able to search, filter, and export leads

**Email Templates**
- **FR-018**: System MUST provide a text editor for email templates with basic formatting
- **FR-019**: System MUST support personalization variables: {{first_name}}, {{last_name}}, {{company}}, {{role}}, and up to 5 custom fields
- **FR-020**: System MUST support multi-email sequences with configurable delays between emails
- **FR-021**: System MUST provide a preview mode showing templates rendered with sample lead data
- **FR-022**: Users MUST be able to save templates to a library for reuse across campaigns

**Send Engine**
- **FR-023**: System MUST send emails via a queue, respecting daily limits per mailbox
- **FR-024**: System MUST spread email sending throughout the configured sending window (not all at once)
- **FR-025**: System MUST support skipping weekends for email delivery
- **FR-026**: System MUST automatically progress through email sequences based on configured delays
- **FR-027**: System MUST stop the sequence for a lead when a reply is received
- **FR-028**: System MUST handle bounced emails by marking leads as bounced and stopping their sequence
- **FR-029**: System MUST track Message-ID headers for reply matching

**Reply Detection**
- **FR-030**: System MUST poll connected mailboxes for new emails at regular intervals
- **FR-031**: System MUST match replies using In-Reply-To headers, subject line matching, and sender email matching as fallbacks
- **FR-032**: System MUST detect and flag auto-reply messages (out-of-office) separately from genuine responses
- **FR-033**: System MUST support reply threading for ongoing conversations

**AI Response Analysis**
- **FR-034**: System MUST analyze new responses using AI to extract structured insights
- **FR-035**: System MUST extract: interest level (hot/warm/cold/negative), problem confirmation (yes/no/different/unclear), current solution, pain severity (1-5), call interest, key quotes, and summary
- **FR-036**: Users MUST be able to manually override any AI-extracted field
- **FR-037**: System MUST display AI confidence level for each analysis
- **FR-038**: Users MUST be able to trigger batch re-analysis of all responses in a campaign

**Response Management**
- **FR-039**: System MUST display responses in an inbox view sorted by recency
- **FR-040**: Users MUST be able to filter responses by campaign, interest level, problem confirmation, call interest, and review status
- **FR-041**: System MUST display the original sent email alongside each response for context
- **FR-042**: Users MUST be able to mark responses as reviewed or actioned
- **FR-043**: Users MUST be able to compose and send replies directly from the response view

**Insights & Patterns**
- **FR-044**: System MUST calculate and display campaign metrics: response rate, interest breakdown, problem validation rate, average pain severity, call conversion rate
- **FR-045**: System MUST detect recurring patterns across responses (minimum 10 responses required)
- **FR-046**: System MUST display a quote board with key quotes from responses
- **FR-047**: System MUST calculate a decision score based on: response rate, interest distribution, problem confirmation rate, pain severity, and call requests

**Call Booking**
- **FR-048**: Users MUST be able to insert a scheduling link into reply templates
- **FR-049**: System MUST track call booking status on leads
- **FR-050**: Users MUST be able to record call outcomes: Validated, Invalidated, Need More Info, No Show
- **FR-051**: Users MUST be able to add notes to call records

**User Dashboard**
- **FR-052**: System MUST display active campaigns summary on the dashboard
- **FR-053**: System MUST display recent responses requiring review
- **FR-054**: System MUST display aggregate metrics: contacts this week, replies this week, response rate trend, calls booked
- **FR-055**: System MUST display mailbox health status with warnings for issues

### Key Entities

- **User**: Account owner who conducts discovery research. Has authentication credentials, settings, and owns all other entities.
- **Mailbox**: Email account configuration for sending and receiving. Linked to a user, has credentials, limits, and health status.
- **Campaign**: Container for a discovery hypothesis test. Has a problem hypothesis, target persona, success criteria, and aggregates all outreach for that test.
- **Lead**: Individual contact for outreach. Has email, name, company, role, custom fields, and status within a campaign.
- **EmailTemplate**: Reusable email content with personalization variables. Belongs to a campaign sequence with a defined order and delay.
- **SentEmail**: Record of an email sent to a lead. Tracks delivery status, timing, and enables reply matching.
- **Response**: Reply received from a lead. Contains raw content, AI-extracted insights, and review status.
- **Insight**: Aggregated learning detected across multiple responses. Includes patterns, themes, quotes, and objections.
- **CallBooking**: Scheduled or completed discovery call with a lead. Tracks outcome and notes.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can connect a mailbox and validate credentials in under 2 minutes
- **SC-002**: Users can create a campaign with hypothesis, leads, and templates in under 10 minutes
- **SC-003**: Users can import 1,000 leads in under 30 seconds
- **SC-004**: System matches 95%+ of replies to the correct lead and campaign within 10 minutes of receipt
- **SC-005**: AI analysis completes within 30 seconds of reply detection
- **SC-006**: Users can review and process 20 responses in under 10 minutes
- **SC-007**: Dashboard loads in under 2 seconds
- **SC-008**: Campaign insights and decision score update within 5 minutes of new response data
- **SC-009**: System respects daily sending limits with 100% accuracy (no over-sending)
- **SC-010**: Users rate AI analysis accuracy at 80%+ (measured by manual correction rate)
- **SC-011**: 40%+ of users who sign up successfully launch their first campaign (activation rate)
- **SC-012**: 60%+ of users with an account show activity each week (engagement rate)

## Assumptions

- Users have access to SMTP/IMAP credentials for their email accounts (standard business email setup)
- Users understand the concept of customer discovery and hypothesis testing (target market is familiar with lean methodology)
- Average campaign size is 100-500 leads based on typical founder outreach patterns
- Response rates for research-framed cold email typically range from 5-20%
- AI analysis uses standard natural language processing capabilities available through commercial APIs
- Users accept that email deliverability depends on their email provider reputation and content quality
- Users are responsible for compliance with email regulations (CAN-SPAM, GDPR) in their jurisdiction
- Warm-up mode follows industry-standard gradual increase patterns (starting at ~10/day, increasing over 2-4 weeks)
