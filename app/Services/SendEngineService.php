<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\EmailBounced;
use App\Events\EmailFailed;
use App\Events\EmailSent;
use App\Jobs\SendEmailJob;
use App\Metrics\Collectors\CampaignFlowCollector;
use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\Mailbox;
use App\Models\MailboxSendingStat;
use App\Models\MessageReference;
use App\Models\SentEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mime\Email;

class SendEngineService
{
    public function renderTemplate(EmailTemplate $template, Lead $lead): array
    {
        return [
            'subject' => $template->renderSubject($lead),
            'body' => $template->renderBody($lead),
        ];
    }

    public function send(SentEmail $sentEmail): bool
    {
        $mailbox = $sentEmail->mailbox;
        $lead = $sentEmail->lead;

        $sentEmail->markAsSending();

        try {
            $transport = $this->createTransport($mailbox);

            $email = (new Email)
                ->from($mailbox->email_address)
                ->to($lead->email)
                ->subject($sentEmail->subject)
                ->html($sentEmail->body)
                ->text(strip_tags($sentEmail->body));

            // Strip angle brackets from message_id for header
            $messageId = trim($sentEmail->message_id, '<>');
            $email->getHeaders()->addIdHeader('Message-ID', $messageId);

            $previousEmails = $lead->sentEmails()
                ->where('campaign_id', $sentEmail->campaign_id)
                ->where('status', SentEmail::STATUS_SENT)
                ->orderBy('sequence_step')
                ->get();

            if ($previousEmails->isNotEmpty()) {
                $lastEmail = $previousEmails->last();
                $lastMessageId = trim($lastEmail->message_id, '<>');
                $email->getHeaders()->addIdHeader('In-Reply-To', $lastMessageId);

                $references = $previousEmails->pluck('message_id')
                    ->map(fn ($id) => trim($id, '<>'))
                    ->toArray();
                $email->getHeaders()->addTextHeader('References', implode(' ', $references));

                foreach ($previousEmails as $index => $prevEmail) {
                    MessageReference::create([
                        'sent_email_id' => $sentEmail->id,
                        'reference_message_id' => $prevEmail->message_id,
                        'position' => $index + 1,
                    ]);
                }
            }

            $transport->send($email);
            $sentEmail->markAsSent();

            $this->recordSendingStat($mailbox);
            $lead->markAsContacted();

            event(new EmailSent($sentEmail));

            return true;
        } catch (\Exception $e) {
            $sentEmail->markAsFailed($e->getMessage());
            event(new EmailFailed($sentEmail, $e->getMessage()));

            return false;
        }
    }

    protected function createTransport(Mailbox $mailbox)
    {
        $scheme = match ($mailbox->smtp_encryption) {
            'ssl' => 'smtps',
            'tls' => 'smtp',
            default => 'smtp', // 'none' uses plain smtp
        };
        $port = $mailbox->smtp_port;

        // Build query options - disable TLS verification for 'none' encryption
        $options = [];
        if ($mailbox->smtp_encryption === 'none') {
            $options['verify_peer'] = '0';
        }

        $dsn = new Dsn(
            $scheme,
            $mailbox->smtp_host,
            $mailbox->smtp_username,
            $mailbox->smtp_password,
            $port,
            $options
        );

        $factory = new EsmtpTransportFactory;

        return $factory->create($dsn);
    }

    protected function recordSendingStat(Mailbox $mailbox): void
    {
        MailboxSendingStat::updateOrCreate(
            [
                'mailbox_id' => $mailbox->id,
                'date' => now()->toDateString(),
            ],
            []
        )->increment('emails_sent');
    }

    public function queueCampaignEmails(Campaign $campaign): int
    {
        if (! $campaign->isActive()) {
            return 0;
        }

        $mailbox = $campaign->mailbox;
        $dailyLimit = $mailbox->getCurrentDailyLimit();
        $sentToday = $this->getSentTodayCount($mailbox);
        $remaining = max(0, $dailyLimit - $sentToday);

        if ($remaining <= 0) {
            return 0;
        }

        $leads = $campaign->leads()
            ->pending()
            ->limit($remaining)
            ->get();

        $template = $campaign->templates()->where('sequence_order', 1)->first();

        if (! $template) {
            return 0;
        }

        $queued = 0;
        foreach ($leads as $lead) {
            $this->queueEmail($campaign, $lead, $template, 1);
            $queued++;
        }

        return $queued;
    }

    public function queueNextSequenceEmail(Lead $lead): ?SentEmail
    {
        $campaign = $lead->campaign;

        if (! $campaign->isActive()) {
            return null;
        }

        $nextStep = $lead->current_sequence_step + 1;
        $template = $campaign->templates()
            ->where('sequence_order', $nextStep)
            ->first();

        if (! $template) {
            return null;
        }

        $scheduledFor = $this->calculateScheduledTime($lead, $template);

        return $this->queueEmail($campaign, $lead, $template, $nextStep, $scheduledFor);
    }

    protected function queueEmail(
        Campaign $campaign,
        Lead $lead,
        EmailTemplate $template,
        int $sequenceStep,
        ?Carbon $scheduledFor = null
    ): SentEmail {
        $rendered = $this->renderTemplate($template, $lead);
        $messageId = $this->generateMessageId($campaign->mailbox);

        $sentEmail = SentEmail::create([
            'mailbox_id' => $campaign->mailbox_id,
            'campaign_id' => $campaign->id,
            'lead_id' => $lead->id,
            'template_id' => $template->id,
            'message_id' => $messageId,
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
            'status' => SentEmail::STATUS_QUEUED,
            'sequence_step' => $sequenceStep,
            'scheduled_for' => $scheduledFor,
        ]);

        $lead->update(['status' => Lead::STATUS_QUEUED]);

        // Record metric for email queued
        try {
            $collector = app(CampaignFlowCollector::class);
            $collector->incrementEmailQueued($campaign->id, $campaign->mailbox_id);
        } catch (\Throwable $e) {
            Log::debug('Failed to record email queued metric', ['error' => $e->getMessage()]);
        }

        return $sentEmail;
    }

    protected function calculateScheduledTime(Lead $lead, EmailTemplate $template): Carbon
    {
        $baseTime = $lead->last_contacted_at ?? now();
        $mailbox = $lead->campaign->mailbox;

        if ($template->delay_type === EmailTemplate::DELAY_TYPE_BUSINESS) {
            $scheduledTime = $this->addBusinessDays($baseTime, $template->delay_days, $mailbox);
        } else {
            $scheduledTime = $baseTime->copy()->addDays($template->delay_days);
        }

        return $this->adjustToSendWindow($scheduledTime, $mailbox);
    }

    protected function addBusinessDays(Carbon $date, int $days, Mailbox $mailbox): Carbon
    {
        $result = $date->copy();

        while ($days > 0) {
            $result->addDay();

            if ($mailbox->skip_weekends && $result->isWeekend()) {
                continue;
            }

            $days--;
        }

        return $result;
    }

    protected function adjustToSendWindow(Carbon $date, Mailbox $mailbox): Carbon
    {
        $timezone = $mailbox->timezone ?? 'UTC';
        $localDate = $date->copy()->setTimezone($timezone);

        [$startHour, $startMin] = explode(':', $mailbox->send_window_start);
        [$endHour, $endMin] = explode(':', $mailbox->send_window_end);

        $windowStart = $localDate->copy()->setTime((int) $startHour, (int) $startMin);
        $windowEnd = $localDate->copy()->setTime((int) $endHour, (int) $endMin);

        if ($localDate->lt($windowStart)) {
            $localDate = $windowStart;
        } elseif ($localDate->gt($windowEnd)) {
            $localDate = $windowStart->addDay();
        }

        if ($mailbox->skip_weekends && $localDate->isWeekend()) {
            $localDate = $localDate->next(Carbon::MONDAY)->setTime((int) $startHour, (int) $startMin);
        }

        return $localDate->setTimezone('UTC');
    }

    public function scheduleEmailsForDay(Campaign $campaign): int
    {
        $mailbox = $campaign->mailbox;
        $queuedEmails = SentEmail::where('campaign_id', $campaign->id)
            ->where('status', SentEmail::STATUS_QUEUED)
            ->where(function ($query) {
                // Include emails scheduled for today OR emails with no scheduled date (immediate send)
                $query->whereDate('scheduled_for', now()->toDateString())
                    ->orWhereNull('scheduled_for');
            })
            ->get();

        if ($queuedEmails->isEmpty()) {
            return 0;
        }

        $dailyLimit = $mailbox->getCurrentDailyLimit();
        $count = min($queuedEmails->count(), $dailyLimit);

        $timezone = $mailbox->timezone ?? 'UTC';
        [$startHour, $startMin] = explode(':', $mailbox->send_window_start);
        [$endHour, $endMin] = explode(':', $mailbox->send_window_end);

        $windowStart = now()->setTimezone($timezone)->setTime((int) $startHour, (int) $startMin);
        $windowEnd = now()->setTimezone($timezone)->setTime((int) $endHour, (int) $endMin);
        $windowMinutes = $windowStart->diffInMinutes($windowEnd);

        $interval = $count > 1 ? floor($windowMinutes / ($count - 1)) : 0;

        foreach ($queuedEmails->take($count) as $index => $email) {
            $sendTime = $windowStart->copy()->addMinutes($index * $interval);
            $email->update(['scheduled_for' => $sendTime->setTimezone('UTC')]);

            SendEmailJob::dispatch($email)->delay($sendTime);
        }

        return $count;
    }

    public function cancelPendingEmails(Lead $lead): int
    {
        return SentEmail::where('lead_id', $lead->id)
            ->whereIn('status', [SentEmail::STATUS_PENDING, SentEmail::STATUS_QUEUED])
            ->update(['status' => SentEmail::STATUS_FAILED, 'error_message' => 'Cancelled']);
    }

    public function handleBounce(SentEmail $sentEmail, string $type = 'hard'): void
    {
        $sentEmail->markAsBounced($type);

        $lead = $sentEmail->lead;
        $lead->markAsBounced();

        $this->cancelPendingEmails($lead);

        MailboxSendingStat::updateOrCreate(
            [
                'mailbox_id' => $sentEmail->mailbox_id,
                'date' => now()->toDateString(),
            ],
            []
        )->increment('emails_bounced');

        event(new EmailBounced($sentEmail));
    }

    public function sendReply(SentEmail $originalEmail, string $body): SentEmail
    {
        $mailbox = $originalEmail->mailbox;
        $lead = $originalEmail->lead;
        $campaign = $originalEmail->campaign;

        $messageId = $this->generateMessageId($mailbox);
        $subject = 'Re: '.preg_replace('/^Re:\s*/i', '', $originalEmail->subject);

        $sentEmail = SentEmail::create([
            'mailbox_id' => $mailbox->id,
            'campaign_id' => $campaign->id,
            'lead_id' => $lead->id,
            'message_id' => $messageId,
            'subject' => $subject,
            'body' => $body,
            'status' => SentEmail::STATUS_QUEUED,
            'sequence_step' => $originalEmail->sequence_step,
        ]);

        $this->send($sentEmail);

        return $sentEmail;
    }

    protected function generateMessageId(Mailbox $mailbox): string
    {
        $domain = explode('@', $mailbox->email_address)[1] ?? 'localhost';

        return '<'.Str::uuid().'@'.$domain.'>';
    }

    protected function getSentTodayCount(Mailbox $mailbox): int
    {
        $stat = MailboxSendingStat::where('mailbox_id', $mailbox->id)
            ->where('date', now()->toDateString())
            ->first();

        return $stat?->emails_sent ?? 0;
    }
}
