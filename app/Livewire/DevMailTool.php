<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\GreenmailService;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Developer mail tool for testing with Greenmail.
 * Only available in local environment.
 */
class DevMailTool extends Component
{
    public ?string $selectedUser = null;

    public ?string $selectedMessageId = null;

    public bool $showCompose = false;

    public bool $showReply = false;

    // Compose form
    public string $composeFrom = '';

    public string $composeTo = '';

    public string $composeSubject = '';

    public string $composeBody = '';

    // Reply form
    public string $replyBody = '';

    protected GreenmailService $greenmail;

    public function boot(GreenmailService $greenmail): void
    {
        $this->greenmail = $greenmail;
    }

    public function mount(): void
    {
        $this->composeFrom = 'test@localhost';
    }

    #[Computed]
    public function users(): array
    {
        return $this->greenmail->getUsers();
    }

    #[Computed]
    public function messages(): array
    {
        if (! $this->selectedUser) {
            return [];
        }

        return $this->greenmail->getMessages($this->selectedUser);
    }

    #[Computed]
    public function currentMessage(): ?array
    {
        if (! $this->selectedUser || $this->selectedMessageId === null) {
            return null;
        }

        return $this->greenmail->getMessage($this->selectedUser, $this->selectedMessageId);
    }

    #[Computed]
    public function isAvailable(): bool
    {
        return $this->greenmail->isAvailable();
    }

    public function selectUser(string $email): void
    {
        $this->selectedUser = $email;
        $this->selectedMessageId = null;
        $this->showCompose = false;
        $this->showReply = false;
    }

    public function selectMessage(string $id): void
    {
        $this->selectedMessageId = $id;
        $this->showCompose = false;
        $this->showReply = false;
    }

    public function openCompose(): void
    {
        $this->showCompose = true;
        $this->showReply = false;
        $this->selectedMessageId = null;
        $this->resetCompose();
    }

    public function openReply(): void
    {
        $this->showReply = true;
        $this->replyBody = '';
    }

    public function closeCompose(): void
    {
        $this->showCompose = false;
        $this->resetCompose();
    }

    public function closeReply(): void
    {
        $this->showReply = false;
        $this->replyBody = '';
    }

    public function sendEmail(): void
    {
        $this->validate([
            'composeFrom' => 'required|email',
            'composeTo' => 'required|email',
            'composeSubject' => 'required|string|max:255',
            'composeBody' => 'required|string',
        ]);

        $success = $this->greenmail->sendEmail(
            $this->composeFrom,
            $this->composeTo,
            $this->composeSubject,
            nl2br($this->composeBody)
        );

        if ($success) {
            session()->flash('success', 'Email sent successfully!');
            $this->closeCompose();
            // Refresh the user list to show new mailbox if created
            unset($this->users);
        } else {
            session()->flash('error', 'Failed to send email.');
        }
    }

    public function sendReply(): void
    {
        $this->validate([
            'replyBody' => 'required|string',
        ]);

        $message = $this->currentMessage;
        if (! $message) {
            session()->flash('error', 'No message selected.');

            return;
        }

        // Extract reply details from original message
        $from = $this->selectedUser;
        $to = $this->extractEmail($message['from'] ?: '');
        $subject = 'Re: '.preg_replace('/^Re:\s*/i', '', $message['subject'] ?: 'No Subject');
        $messageId = $message['messageId'] ?: null;

        // Build reply body with quoted original
        $originalBody = $message['textContent'] ?: strip_tags($message['htmlContent'] ?: '');
        $quotedOriginal = "\n\n--- Original Message ---\n".
            "From: {$message['from']}\n".
            "Date: {$message['receivedDate']}\n".
            "Subject: {$message['subject']}\n\n".
            $originalBody;

        $fullBody = nl2br($this->replyBody).'<br><br><blockquote style="border-left: 2px solid #ccc; padding-left: 10px; color: #666;">'.
            nl2br(htmlspecialchars($quotedOriginal)).
            '</blockquote>';

        $success = $this->greenmail->sendEmail($from, $to, $subject, $fullBody, $messageId);

        if ($success) {
            session()->flash('success', 'Reply sent successfully!');
            $this->closeReply();
            unset($this->messages);
        } else {
            session()->flash('error', 'Failed to send reply.');
        }
    }

    public function deleteUserMessages(): void
    {
        if (! $this->selectedUser) {
            return;
        }

        $this->greenmail->deleteMessages($this->selectedUser);
        $this->selectedMessageId = null;
        unset($this->messages);
        session()->flash('success', 'All messages deleted.');
    }

    public function purgeAll(): void
    {
        $this->greenmail->purgeAll();
        $this->selectedUser = null;
        $this->selectedMessageId = null;
        unset($this->users, $this->messages);
        session()->flash('success', 'All mailboxes purged.');
    }

    public function refresh(): void
    {
        unset($this->users, $this->messages, $this->currentMessage);
    }

    protected function resetCompose(): void
    {
        $this->composeFrom = 'test@localhost';
        $this->composeTo = '';
        $this->composeSubject = '';
        $this->composeBody = '';
    }

    protected function extractEmail(string $fromHeader): string
    {
        // Extract email from "Name <email@domain.com>" format
        if (preg_match('/<([^>]+)>/', $fromHeader, $matches)) {
            return $matches[1];
        }

        return $fromHeader;
    }

    public function render()
    {
        return view('livewire.dev-mail-tool')
            ->layout('components.layouts.app', ['title' => 'Dev Mail Tool']);
    }
}
