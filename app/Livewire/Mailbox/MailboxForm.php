<?php

declare(strict_types=1);

namespace App\Livewire\Mailbox;

use App\Models\Mailbox;
use App\Services\MailboxService;
use App\Traits\HandlesImpersonation;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class MailboxForm extends Component
{
    use HandlesImpersonation;

    public ?Mailbox $mailbox = null;

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

    public bool $isValidating = false;

    public ?array $validationResult = null;

    public function mount(?Mailbox $mailbox = null): void
    {
        if ($mailbox && $mailbox->exists) {
            $this->mailbox = $mailbox;
            $this->name = $mailbox->name;
            $this->email_address = $mailbox->email_address;
            $this->smtp_host = $mailbox->smtp_host;
            $this->smtp_port = $mailbox->smtp_port;
            $this->smtp_encryption = $mailbox->smtp_encryption;
            $this->smtp_username = $mailbox->smtp_username;
            $this->smtp_password = ''; // Don't expose password
            $this->imap_host = $mailbox->imap_host;
            $this->imap_port = $mailbox->imap_port;
            $this->imap_encryption = $mailbox->imap_encryption;
            $this->imap_username = $mailbox->imap_username;
            $this->imap_password = ''; // Don't expose password
            $this->daily_limit = $mailbox->daily_limit;
            $this->send_window_start = substr($mailbox->send_window_start, 0, 5);
            $this->send_window_end = substr($mailbox->send_window_end, 0, 5);
            $this->skip_weekends = $mailbox->skip_weekends;
            $this->timezone = $mailbox->timezone;
            $this->warmup_enabled = $mailbox->warmup_enabled;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email_address' => ['required', 'email', 'max:255'],
            'smtp_host' => ['required', 'string', 'max:255'],
            'smtp_port' => ['required', 'integer', Rule::in([25, 465, 587, 2525])],
            'smtp_encryption' => ['required', Rule::in(['tls', 'ssl'])],
            'smtp_username' => ['required', 'string', 'max:255'],
            'smtp_password' => [$this->mailbox ? 'nullable' : 'required', 'string'],
            'imap_host' => ['required', 'string', 'max:255'],
            'imap_port' => ['required', 'integer', Rule::in([143, 993])],
            'imap_encryption' => ['required', Rule::in(['tls', 'ssl'])],
            'imap_username' => ['required', 'string', 'max:255'],
            'imap_password' => [$this->mailbox ? 'nullable' : 'required', 'string'],
            'daily_limit' => ['required', 'integer', 'min:1', 'max:500'],
            'send_window_start' => ['required', 'date_format:H:i'],
            'send_window_end' => ['required', 'date_format:H:i', 'after:send_window_start'],
            'skip_weekends' => ['boolean'],
            'timezone' => ['required', 'timezone'],
            'warmup_enabled' => ['boolean'],
        ];
    }

    public function validateCredentials(): void
    {
        $this->validate([
            'smtp_host' => ['required', 'string'],
            'smtp_port' => ['required', 'integer'],
            'smtp_encryption' => ['required'],
            'smtp_username' => ['required', 'string'],
            'smtp_password' => ['required', 'string'],
            'imap_host' => ['required', 'string'],
            'imap_port' => ['required', 'integer'],
            'imap_encryption' => ['required'],
            'imap_username' => ['required', 'string'],
            'imap_password' => ['required', 'string'],
        ]);

        $this->isValidating = true;
        $this->validationResult = null;

        $service = app(MailboxService::class);

        // Validate SMTP
        $smtpResult = $service->validateSmtpCredentials(
            $this->smtp_host,
            $this->smtp_port,
            $this->smtp_encryption,
            $this->smtp_username,
            $this->smtp_password
        );

        if (! $smtpResult['success']) {
            $this->validationResult = [
                'success' => false,
                'smtp' => $smtpResult,
                'imap' => null,
            ];
            $this->isValidating = false;

            return;
        }

        // Validate IMAP
        $imapResult = $service->validateImapCredentials(
            $this->imap_host,
            $this->imap_port,
            $this->imap_encryption,
            $this->imap_username,
            $this->imap_password
        );

        $this->validationResult = [
            'success' => $smtpResult['success'] && $imapResult['success'],
            'smtp' => $smtpResult,
            'imap' => $imapResult,
        ];

        $this->isValidating = false;
    }

    public function save(): void
    {
        $this->validate();

        $service = app(MailboxService::class);

        $data = [
            'name' => $this->name,
            'email_address' => $this->email_address,
            'smtp_host' => $this->smtp_host,
            'smtp_port' => $this->smtp_port,
            'smtp_encryption' => $this->smtp_encryption,
            'smtp_username' => $this->smtp_username,
            'imap_host' => $this->imap_host,
            'imap_port' => $this->imap_port,
            'imap_encryption' => $this->imap_encryption,
            'imap_username' => $this->imap_username,
            'daily_limit' => $this->daily_limit,
            'send_window_start' => $this->send_window_start.':00',
            'send_window_end' => $this->send_window_end.':00',
            'skip_weekends' => $this->skip_weekends,
            'timezone' => $this->timezone,
            'warmup_enabled' => $this->warmup_enabled,
        ];

        // Only include passwords if they're provided
        if ($this->smtp_password) {
            $data['smtp_password'] = $this->smtp_password;
        }
        if ($this->imap_password) {
            $data['imap_password'] = $this->imap_password;
        }

        if ($this->mailbox) {
            $service->update($this->mailbox, $data);
            $this->dispatch('mailbox-updated', mailbox: $this->mailbox);
            session()->flash('message', 'Mailbox updated successfully.');
        } else {
            $mailbox = $service->create($this->getEffectiveUserId(), $data);
            $this->dispatch('mailbox-created', mailbox: $mailbox);
            session()->flash('message', 'Mailbox created successfully.');
        }

        $this->redirect(route('mailboxes.index'));
    }

    public function render(): View
    {
        return view('livewire.mailbox.mailbox-form');
    }
}
