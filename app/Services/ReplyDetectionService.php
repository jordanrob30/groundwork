<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\ResponseReceived;
use App\Exceptions\MailboxConnectionException;
use App\Models\Mailbox;
use App\Models\MessageReference;
use App\Models\Response;
use App\Models\SentEmail;
use Webklex\IMAP\Facades\Client;
use ZBateson\MailMimeParser\MailMimeParser;

class ReplyDetectionService
{
    protected array $autoReplySubjectPatterns = [
        '/^(out of office|automatic reply|auto:|autoreply:)/i',
        '/^(away|on vacation|on leave)/i',
        '/\[auto\-?reply\]/i',
    ];

    protected array $autoReplyHeaderPatterns = [
        'X-Auto-Response-Suppress',
        'X-Autoreply',
        'X-Autorespond',
        'Auto-Submitted',
    ];

    protected array $bounceSubjectPatterns = [
        '/^(undeliverable|delivery status|delivery failure|mail delivery failed)/i',
        '/^(returned mail|undelivered mail)/i',
        '/^(failure notice|delivery notification)/i',
    ];

    public function poll(Mailbox $mailbox): int
    {
        if ($mailbox->status === 'error' || $mailbox->status === 'paused') {
            return 0;
        }

        try {
            $client = $this->createImapClient($mailbox);
            $client->connect();

            $inbox = $client->getFolder('INBOX');
            $since = $mailbox->last_polled_at ?? now()->subDays(7);

            $messages = $inbox->messages()
                ->since($since)
                ->unseen()
                ->get();

            $processed = 0;

            foreach ($messages as $message) {
                $inReplyTo = (string) ($message->getInReplyTo() ?? '');
                $references = (string) ($message->getReferences() ?? '');
                $subject = (string) ($message->getSubject() ?? '');
                $fromAttribute = $message->getFrom();
                $fromAddress = $fromAttribute[0]->mail ?? null;

                if (! $fromAddress) {
                    continue;
                }

                if ($this->isBounce($subject, $fromAddress)) {
                    $this->handleBounceMessage($message, $mailbox);
                    $message->setFlag('Seen');
                    $processed++;

                    continue;
                }

                $sentEmail = $this->matchToSentEmail($inReplyTo, $references, $subject, $fromAddress, $mailbox);

                if ($sentEmail) {
                    $this->createResponse($sentEmail, $message);
                    $message->setFlag('Seen');
                    $processed++;
                }
            }

            $mailbox->update(['last_polled_at' => now()]);
            $client->disconnect();

            return $processed;
        } catch (\Exception $e) {
            $mailbox->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
                'last_error_at' => now(),
            ]);

            throw new MailboxConnectionException('Failed to poll mailbox: '.$e->getMessage(), 0, $e);
        }
    }

    protected function createImapClient(Mailbox $mailbox)
    {
        $encryption = $mailbox->imap_encryption;
        // 'none' means no encryption, use false for the IMAP client
        if ($encryption === 'none') {
            $encryption = false;
        }

        return Client::make([
            'host' => $mailbox->imap_host,
            'port' => $mailbox->imap_port,
            'encryption' => $encryption,
            'username' => $mailbox->imap_username,
            'password' => $mailbox->imap_password,
            'validate_cert' => $encryption !== false,
            'protocol' => 'imap',
        ]);
    }

    public function matchToSentEmail(
        ?string $inReplyTo,
        ?string $references,
        string $subject,
        string $fromEmail,
        Mailbox $mailbox
    ): ?SentEmail {
        if ($inReplyTo) {
            $sentEmail = SentEmail::where('message_id', $inReplyTo)
                ->where('mailbox_id', $mailbox->id)
                ->first();

            if ($sentEmail) {
                return $sentEmail;
            }
        }

        if ($references) {
            $refIds = preg_split('/\s+/', $references);
            foreach ($refIds as $refId) {
                $ref = MessageReference::where('reference_message_id', $refId)->first();
                if ($ref) {
                    return $ref->sentEmail;
                }

                $sentEmail = SentEmail::where('message_id', $refId)
                    ->where('mailbox_id', $mailbox->id)
                    ->first();

                if ($sentEmail) {
                    return $sentEmail;
                }
            }
        }

        $cleanSubject = preg_replace('/^(Re:|Fwd?:)\s*/i', '', $subject);
        $cleanSubject = trim($cleanSubject);

        return SentEmail::where('mailbox_id', $mailbox->id)
            ->whereRaw("REPLACE(REPLACE(subject, 'Re: ', ''), 'Fwd: ', '') = ?", [$cleanSubject])
            ->whereHas('lead', function ($q) use ($fromEmail) {
                $q->where('email', $fromEmail);
            })
            ->orderByDesc('sent_at')
            ->first();
    }

    public function isAutoReply($message): bool
    {
        $headers = $message->getHeader();

        foreach ($this->autoReplyHeaderPatterns as $headerName) {
            $headerValue = $headers->get($headerName);
            // The IMAP library returns objects - we need to cast to string and check if non-empty
            $stringValue = (string) $headerValue;
            if (! empty($stringValue)) {
                return true;
            }
        }

        $subject = (string) ($message->getSubject() ?? '');
        foreach ($this->autoReplySubjectPatterns as $pattern) {
            if (preg_match($pattern, $subject)) {
                return true;
            }
        }

        return false;
    }

    public function isBounce(string $subject, string $fromEmail): bool
    {
        foreach ($this->bounceSubjectPatterns as $pattern) {
            if (preg_match($pattern, $subject)) {
                return true;
            }
        }

        if (preg_match('/^(mailer-daemon|postmaster)@/i', $fromEmail)) {
            return true;
        }

        return false;
    }

    protected function handleBounceMessage($message, Mailbox $mailbox): void
    {
        $body = $message->getTextBody() ?? $message->getHTMLBody() ?? '';

        preg_match('/Final-Recipient:.*?([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i', $body, $matches);
        $bouncedEmail = $matches[1] ?? null;

        if ($bouncedEmail) {
            $sentEmail = SentEmail::where('mailbox_id', $mailbox->id)
                ->whereHas('lead', function ($q) use ($bouncedEmail) {
                    $q->where('email', $bouncedEmail);
                })
                ->where('status', SentEmail::STATUS_SENT)
                ->orderByDesc('sent_at')
                ->first();

            if ($sentEmail) {
                app(SendEngineService::class)->handleBounce($sentEmail, 'hard');
            }
        }
    }

    public function parseEmail(string $rawEmail): array
    {
        $parser = new MailMimeParser;
        $message = $parser->parse($rawEmail, true);

        return [
            'subject' => $message->getHeaderValue('Subject'),
            'from' => $message->getHeaderValue('From'),
            'to' => $message->getHeaderValue('To'),
            'date' => $message->getHeaderValue('Date'),
            'message_id' => $message->getHeaderValue('Message-ID'),
            'in_reply_to' => $message->getHeaderValue('In-Reply-To'),
            'references' => $message->getHeaderValue('References'),
            'body_html' => $message->getHtmlContent(),
            'body_plain' => $message->getTextContent(),
        ];
    }

    protected function createResponse(SentEmail $sentEmail, $message): Response
    {
        $isAutoReply = $this->isAutoReply($message);

        $messageId = (string) ($message->getMessageId() ?? '');
        if (empty($messageId)) {
            $messageId = '<'.uniqid().'@unknown>';
        }

        $htmlBody = $message->getHTMLBody() ?? '';
        $textBody = $message->getTextBody() ?? '';

        $response = Response::create([
            'sent_email_id' => $sentEmail->id,
            'lead_id' => $sentEmail->lead_id,
            'campaign_id' => $sentEmail->campaign_id,
            'message_id' => $messageId,
            'in_reply_to' => (string) ($message->getInReplyTo() ?? ''),
            'subject' => (string) ($message->getSubject() ?? '(No Subject)'),
            'body' => $htmlBody ?: $textBody,
            'body_plain' => $textBody ?: strip_tags($htmlBody),
            'is_auto_reply' => $isAutoReply,
            'received_at' => $message->getDate() ?? now(),
            'analysis_status' => $isAutoReply ? Response::ANALYSIS_STATUS_COMPLETED : Response::ANALYSIS_STATUS_PENDING,
        ]);

        if (! $isAutoReply) {
            $sentEmail->lead->markAsReplied();
        }

        event(new ResponseReceived($response));

        return $response;
    }

    public function getConversationThread(Response $response): array
    {
        $sentEmail = $response->sentEmail;
        $lead = $response->lead;
        $campaign = $response->campaign;

        $sentEmails = SentEmail::where('lead_id', $lead->id)
            ->where('campaign_id', $campaign->id)
            ->where('status', SentEmail::STATUS_SENT)
            ->orderBy('sent_at')
            ->get();

        $responses = Response::where('lead_id', $lead->id)
            ->where('campaign_id', $campaign->id)
            ->orderBy('received_at')
            ->get();

        $thread = [];

        foreach ($sentEmails as $email) {
            $thread[] = [
                'type' => 'sent',
                'id' => $email->id,
                'date' => $email->sent_at,
                'subject' => $email->subject,
                'body' => $email->body,
                'sequence_step' => $email->sequence_step,
            ];
        }

        foreach ($responses as $resp) {
            $thread[] = [
                'type' => 'received',
                'id' => $resp->id,
                'date' => $resp->received_at,
                'subject' => $resp->subject,
                'body' => $resp->body,
                'is_auto_reply' => $resp->is_auto_reply,
            ];
        }

        usort($thread, fn ($a, $b) => $a['date'] <=> $b['date']);

        return $thread;
    }
}
