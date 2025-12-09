<?php

declare(strict_types=1);

namespace App\Livewire\Mailbox;

use App\Models\Mailbox;
use App\Services\MailboxService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MailboxHealth extends Component
{
    public Mailbox $mailbox;

    public array $recentStats = [];

    public ?string $lastError = null;

    public int $warmupProgress = 0;

    public bool $isTesting = false;

    public ?array $testResult = null;

    public function mount(Mailbox $mailbox): void
    {
        $this->mailbox = $mailbox;
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $service = app(MailboxService::class);
        $this->recentStats = $service->getRecentStats($this->mailbox, 7);
        $this->lastError = $this->mailbox->error_message;
        $this->warmupProgress = $this->mailbox->getWarmupProgressPercentage();
    }

    public function testConnection(): void
    {
        $this->isTesting = true;
        $this->testResult = null;

        $service = app(MailboxService::class);

        // Test SMTP
        $smtpResult = $service->validateSmtpCredentials(
            $this->mailbox->smtp_host,
            $this->mailbox->smtp_port,
            $this->mailbox->smtp_encryption,
            $this->mailbox->smtp_username,
            $this->mailbox->smtp_password
        );

        // Test IMAP
        $imapResult = $service->validateImapCredentials(
            $this->mailbox->imap_host,
            $this->mailbox->imap_port,
            $this->mailbox->imap_encryption,
            $this->mailbox->imap_username,
            $this->mailbox->imap_password
        );

        $this->testResult = [
            'success' => $smtpResult['success'] && $imapResult['success'],
            'smtp' => $smtpResult,
            'imap' => $imapResult,
        ];

        $this->isTesting = false;
    }

    public function clearError(): void
    {
        app(MailboxService::class)->clearError($this->mailbox);
        $this->mailbox->refresh();
        $this->lastError = null;
    }

    public function render(): View
    {
        return view('livewire.mailbox.mailbox-health');
    }
}
