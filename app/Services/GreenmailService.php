<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mime\Email;

/**
 * Service for interacting with Greenmail test mail server.
 * Only available in local/testing environments.
 */
class GreenmailService
{
    protected string $apiUrl;

    protected string $smtpHost;

    protected int $smtpPort;

    protected string $imapHost;

    protected int $imapPort;

    public function __construct()
    {
        $this->apiUrl = 'http://'.env('MAIL_HOST', 'mailserver').':8080';
        $this->smtpHost = env('MAIL_HOST', 'mailserver');
        $this->smtpPort = (int) env('MAIL_PORT', 3025);
        $this->imapHost = env('TEST_MAILBOX_IMAP_HOST', 'mailserver');
        $this->imapPort = (int) env('TEST_MAILBOX_IMAP_PORT', 3143);
    }

    /**
     * Get all users/mailboxes in Greenmail.
     */
    public function getUsers(): array
    {
        $response = Http::get("{$this->apiUrl}/api/user");

        if ($response->failed()) {
            return [];
        }

        return $response->json() ?? [];
    }

    /**
     * Get all messages for a specific email address.
     */
    public function getMessages(string $email): array
    {
        $response = Http::get("{$this->apiUrl}/api/user/{$email}/messages");

        if ($response->failed()) {
            return [];
        }

        $messages = $response->json() ?? [];

        // Normalize and parse each message
        return array_map(fn ($msg) => $this->parseMessage($msg), $messages);
    }

    /**
     * Get a specific message by UID.
     */
    public function getMessage(string $email, string $uid): ?array
    {
        $messages = $this->getMessages($email);

        foreach ($messages as $message) {
            if ($message['id'] === $uid) {
                return $message;
            }
        }

        return null;
    }

    /**
     * Parse a Greenmail message into a normalized structure.
     */
    protected function parseMessage(array $raw): array
    {
        $mime = $raw['mimeMessage'] ?? '';

        // Parse headers from MIME
        $from = $this->extractHeader($mime, 'From');
        $to = $this->extractHeader($mime, 'To');
        $date = $this->extractHeader($mime, 'Date');

        // Extract body content
        $htmlContent = $this->extractMimePart($mime, 'text/html');
        $textContent = $this->extractMimePart($mime, 'text/plain');

        return [
            'id' => $raw['uid'] ?? '',
            'messageId' => $raw['Message-ID'] ?? '',
            'subject' => $raw['subject'] ?? '(No Subject)',
            'from' => $from,
            'to' => $to,
            'receivedDate' => $date,
            'contentType' => $raw['contentType'] ?? '',
            'htmlContent' => $htmlContent,
            'textContent' => $textContent,
        ];
    }

    /**
     * Extract a header value from MIME content.
     */
    protected function extractHeader(string $mime, string $header): string
    {
        if (preg_match('/^'.preg_quote($header, '/').': (.+?)(?:\r\n(?![ \t])|\r\n\r\n)/ms', $mime, $matches)) {
            return trim(preg_replace('/\r\n[ \t]+/', ' ', $matches[1]));
        }

        return '';
    }

    /**
     * Extract a MIME part by content type.
     */
    protected function extractMimePart(string $mime, string $contentType): string
    {
        // Find the boundary
        if (! preg_match('/boundary=([^\s;]+)/i', $mime, $boundaryMatch)) {
            // No boundary, check if it's a simple message
            if (str_contains($mime, $contentType)) {
                $parts = preg_split('/\r\n\r\n/', $mime, 2);

                return $parts[1] ?? '';
            }

            return '';
        }

        $boundary = trim($boundaryMatch[1], '"');

        // Split by boundary
        $parts = explode('--'.$boundary, $mime);

        foreach ($parts as $part) {
            if (str_contains($part, $contentType)) {
                // Extract content after headers
                $sections = preg_split('/\r\n\r\n/', $part, 2);
                if (isset($sections[1])) {
                    $content = trim($sections[1]);
                    // Remove trailing boundary markers
                    $content = preg_replace('/--$/', '', $content);

                    return trim($content);
                }
            }
        }

        return '';
    }

    /**
     * Get message count for a user.
     */
    public function getMessageCount(string $email): int
    {
        $messages = $this->getMessages($email);

        return count($messages);
    }

    /**
     * Delete all messages for a user.
     */
    public function deleteMessages(string $email): bool
    {
        $response = Http::delete("{$this->apiUrl}/api/user/{$email}/messages");

        return $response->successful();
    }

    /**
     * Purge all messages from all mailboxes.
     */
    public function purgeAll(): bool
    {
        $response = Http::delete("{$this->apiUrl}/api/mail/purge");

        return $response->successful();
    }

    /**
     * Send an email through Greenmail SMTP.
     */
    public function sendEmail(string $from, string $to, string $subject, string $body, ?string $inReplyTo = null): bool
    {
        try {
            $dsn = new Dsn(
                'smtp',
                $this->smtpHost,
                null,
                null,
                $this->smtpPort,
                ['verify_peer' => '0']
            );

            $factory = new EsmtpTransportFactory;
            $transport = $factory->create($dsn);

            $email = (new Email)
                ->from($from)
                ->to($to)
                ->subject($subject)
                ->html($body)
                ->text(strip_tags($body));

            if ($inReplyTo) {
                $email->getHeaders()->addIdHeader('In-Reply-To', trim($inReplyTo, '<>'));
                $email->getHeaders()->addTextHeader('References', trim($inReplyTo, '<>'));
            }

            $transport->send($email);

            return true;
        } catch (\Exception $e) {
            report($e);

            return false;
        }
    }

    /**
     * Check if Greenmail is available.
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(2)->get("{$this->apiUrl}/api/user");

            return $response->successful();
        } catch (\Exception) {
            return false;
        }
    }
}
