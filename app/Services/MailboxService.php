<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\MailboxCreated;
use App\Events\MailboxErrorOccurred;
use App\Events\MailboxUpdated;
use App\Models\Mailbox;
use App\Models\MailboxSendingStat;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Webklex\PHPIMAP\Client;

class MailboxService
{
    /**
     * Validate SMTP credentials before saving.
     *
     * @return array{success: bool, message: string}
     */
    public function validateSmtpCredentials(
        string $host,
        int $port,
        string $encryption,
        string $username,
        string $password
    ): array {
        try {
            $transport = new EsmtpTransport(
                $host,
                $port,
                $encryption === 'ssl'
            );
            $transport->setUsername($username);
            $transport->setPassword($password);

            // Test connection
            $transport->start();
            $transport->stop();

            return ['success' => true, 'message' => 'SMTP connection successful'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Validate IMAP credentials before saving.
     *
     * @return array{success: bool, message: string}
     */
    public function validateImapCredentials(
        string $host,
        int $port,
        string $encryption,
        string $username,
        string $password
    ): array {
        try {
            $client = new Client([
                'host' => $host,
                'port' => $port,
                'encryption' => $encryption,
                'username' => $username,
                'password' => $password,
                'validate_cert' => true,
                'protocol' => 'imap',
            ]);

            $client->connect();
            $client->disconnect();

            return ['success' => true, 'message' => 'IMAP connection successful'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create a new mailbox with encrypted credentials.
     *
     * @throws \InvalidArgumentException If validation fails
     */
    public function create(int $userId, array $data): Mailbox
    {
        // Set warmup start date if warmup is enabled
        if ($data['warmup_enabled'] ?? true) {
            $data['warmup_started_at'] = now();
            $data['warmup_day'] = 1;
            $data['status'] = Mailbox::STATUS_WARMUP;
        } else {
            $data['status'] = Mailbox::STATUS_ACTIVE;
        }

        $data['user_id'] = $userId;

        $mailbox = Mailbox::create($data);

        event(new MailboxCreated($mailbox));

        return $mailbox;
    }

    /**
     * Update mailbox configuration.
     */
    public function update(Mailbox $mailbox, array $data): Mailbox
    {
        $mailbox->update($data);

        event(new MailboxUpdated($mailbox));

        return $mailbox->fresh();
    }

    /**
     * Check if mailbox has reached daily sending limit.
     */
    public function hasReachedDailyLimit(Mailbox $mailbox): bool
    {
        $sentCount = $this->getSentCountToday($mailbox);
        $limit = $this->getCurrentDailyLimit($mailbox);

        return $sentCount >= $limit;
    }

    /**
     * Get current daily limit considering warm-up schedule.
     */
    public function getCurrentDailyLimit(Mailbox $mailbox): int
    {
        return $mailbox->getCurrentDailyLimit();
    }

    /**
     * Get count of emails sent today.
     */
    public function getSentCountToday(Mailbox $mailbox): int
    {
        $stat = MailboxSendingStat::where('mailbox_id', $mailbox->id)
            ->where('date', now()->toDateString())
            ->first();

        return $stat?->emails_sent ?? 0;
    }

    /**
     * Get count of emails sent in last 24 hours.
     */
    public function getSentCountLast24Hours(Mailbox $mailbox): int
    {
        return $mailbox->sentEmails()
            ->where('sent_at', '>=', now()->subHours(24))
            ->count();
    }

    /**
     * Update warm-up progress daily.
     */
    public function incrementWarmupDay(Mailbox $mailbox): void
    {
        if (! $mailbox->warmup_enabled) {
            return;
        }

        $mailbox->warmup_day++;

        // After 14 days, warmup is complete
        if ($mailbox->warmup_day > 14) {
            $mailbox->warmup_enabled = false;
            $mailbox->status = Mailbox::STATUS_ACTIVE;
        }

        $mailbox->save();
    }

    /**
     * Pause a mailbox (stops sending but preserves config).
     */
    public function pause(Mailbox $mailbox): Mailbox
    {
        $mailbox->status = Mailbox::STATUS_PAUSED;
        $mailbox->save();

        event(new MailboxUpdated($mailbox));

        return $mailbox;
    }

    /**
     * Resume a paused mailbox.
     */
    public function resume(Mailbox $mailbox): Mailbox
    {
        $mailbox->status = $mailbox->warmup_enabled
            ? Mailbox::STATUS_WARMUP
            : Mailbox::STATUS_ACTIVE;

        $mailbox->error_message = null;
        $mailbox->last_error_at = null;
        $mailbox->save();

        event(new MailboxUpdated($mailbox));

        return $mailbox;
    }

    /**
     * Record mailbox error and update status.
     */
    public function recordError(Mailbox $mailbox, string $errorMessage): void
    {
        $mailbox->status = Mailbox::STATUS_ERROR;
        $mailbox->error_message = $errorMessage;
        $mailbox->last_error_at = now();
        $mailbox->save();

        event(new MailboxErrorOccurred($mailbox, $errorMessage));
    }

    /**
     * Clear error state from mailbox.
     */
    public function clearError(Mailbox $mailbox): void
    {
        $mailbox->error_message = null;
        $mailbox->last_error_at = null;

        if ($mailbox->status === Mailbox::STATUS_ERROR) {
            $mailbox->status = $mailbox->warmup_enabled
                ? Mailbox::STATUS_WARMUP
                : Mailbox::STATUS_ACTIVE;
        }

        $mailbox->save();

        event(new MailboxUpdated($mailbox));
    }

    /**
     * Increment the daily sent count for a mailbox.
     */
    public function incrementSentCount(Mailbox $mailbox): void
    {
        $today = now()->toDateString();

        MailboxSendingStat::updateOrCreate(
            ['mailbox_id' => $mailbox->id, 'date' => $today],
            ['emails_sent' => DB::raw('emails_sent + 1')]
        );
    }

    /**
     * Record a bounce for a mailbox.
     */
    public function recordBounce(Mailbox $mailbox): void
    {
        $today = now()->toDateString();

        $stat = MailboxSendingStat::updateOrCreate(
            ['mailbox_id' => $mailbox->id, 'date' => $today],
            ['emails_bounced' => DB::raw('emails_bounced + 1')]
        );

        $stat->updateBounceRate();
    }

    /**
     * Get recent sending stats for a mailbox.
     *
     * @return array<MailboxSendingStat>
     */
    public function getRecentStats(Mailbox $mailbox, int $days = 7): array
    {
        return $mailbox->sendingStats()
            ->where('date', '>=', now()->subDays($days))
            ->orderBy('date', 'desc')
            ->get()
            ->toArray();
    }
}
