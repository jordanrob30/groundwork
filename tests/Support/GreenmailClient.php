<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Support\Facades\Http;

/**
 * Client for interacting with Greenmail's REST API during integration tests.
 */
class GreenmailClient
{
    protected string $baseUrl;

    public function __construct(?string $baseUrl = null)
    {
        $this->baseUrl = $baseUrl ?? 'http://'.env('TEST_MAILBOX_IMAP_HOST', 'mailserver').':8080';
    }

    /**
     * Get all users configured in Greenmail.
     */
    public function getUsers(): array
    {
        $response = Http::get("{$this->baseUrl}/api/user");

        return $response->json() ?? [];
    }

    /**
     * Get all messages for a specific email address.
     */
    public function getMessages(string $email): array
    {
        $response = Http::get("{$this->baseUrl}/api/user/{$email}/messages");

        return $response->json() ?? [];
    }

    /**
     * Get a specific message by ID.
     */
    public function getMessage(string $email, int $messageId): ?array
    {
        $response = Http::get("{$this->baseUrl}/api/user/{$email}/messages/{$messageId}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Get the count of messages for a specific email address.
     */
    public function getMessageCount(string $email): int
    {
        return count($this->getMessages($email));
    }

    /**
     * Delete all messages for a specific email address.
     */
    public function deleteMessages(string $email): bool
    {
        $response = Http::delete("{$this->baseUrl}/api/user/{$email}/messages");

        return $response->successful();
    }

    /**
     * Delete all messages for all users (purge entire mailbox).
     */
    public function purgeAllMessages(): void
    {
        foreach ($this->getUsers() as $user) {
            $this->deleteMessages($user['email']);
        }
    }

    /**
     * Wait for a specific number of messages to arrive (with timeout).
     */
    public function waitForMessages(string $email, int $expectedCount, int $timeoutSeconds = 10): bool
    {
        $start = time();

        while (time() - $start < $timeoutSeconds) {
            if ($this->getMessageCount($email) >= $expectedCount) {
                return true;
            }
            usleep(100000); // 100ms
        }

        return false;
    }

    /**
     * Get the latest message for an email address.
     */
    public function getLatestMessage(string $email): ?array
    {
        $messages = $this->getMessages($email);

        if (empty($messages)) {
            return null;
        }

        return end($messages);
    }

    /**
     * Assert that a message with specific subject exists.
     */
    public function hasMessageWithSubject(string $email, string $subject): bool
    {
        $messages = $this->getMessages($email);

        foreach ($messages as $message) {
            if (str_contains($message['subject'] ?? '', $subject)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find messages matching a subject pattern.
     */
    public function findMessagesBySubject(string $email, string $subjectPattern): array
    {
        $messages = $this->getMessages($email);
        $matched = [];

        foreach ($messages as $message) {
            if (str_contains($message['subject'] ?? '', $subjectPattern)) {
                $matched[] = $message;
            }
        }

        return $matched;
    }
}
