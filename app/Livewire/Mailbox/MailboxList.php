<?php

declare(strict_types=1);

namespace App\Livewire\Mailbox;

use App\Models\Mailbox;
use App\Services\MailboxService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class MailboxList extends Component
{
    public Collection $mailboxes;

    public ?string $filterStatus = null;

    public function mount(): void
    {
        $this->loadMailboxes();
    }

    public function loadMailboxes(): void
    {
        $query = Mailbox::forUser(auth()->id())
            ->orderBy('created_at', 'desc');

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        $this->mailboxes = $query->get();
    }

    public function setFilter(?string $status): void
    {
        $this->filterStatus = $status;
        $this->loadMailboxes();
    }

    public function pause(int $mailboxId): void
    {
        $mailbox = Mailbox::findOrFail($mailboxId);

        if ($mailbox->user_id !== auth()->id()) {
            return;
        }

        app(MailboxService::class)->pause($mailbox);
        $this->dispatch('mailbox-updated', mailbox: $mailbox);
        $this->loadMailboxes();
    }

    public function resume(int $mailboxId): void
    {
        $mailbox = Mailbox::findOrFail($mailboxId);

        if ($mailbox->user_id !== auth()->id()) {
            return;
        }

        app(MailboxService::class)->resume($mailbox);
        $this->dispatch('mailbox-updated', mailbox: $mailbox);
        $this->loadMailboxes();
    }

    public function delete(int $mailboxId): void
    {
        $mailbox = Mailbox::findOrFail($mailboxId);

        if ($mailbox->user_id !== auth()->id()) {
            return;
        }

        $mailbox->delete();
        $this->dispatch('mailbox-deleted', mailboxId: $mailboxId);
        $this->loadMailboxes();
    }

    public function render(): View
    {
        return view('livewire.mailbox.mailbox-list');
    }
}
