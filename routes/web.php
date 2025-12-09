<?php

use App\Livewire\Admin\AuditLog;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\UserList;
use App\Livewire\Campaign\CampaignForm;
use App\Livewire\Campaign\CampaignInsights;
use App\Livewire\Campaign\CampaignList;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\DevMailTool;
use App\Livewire\Lead\LeadForm;
use App\Livewire\Lead\LeadImport;
use App\Livewire\Lead\LeadList;
use App\Livewire\Mailbox\MailboxForm;
use App\Livewire\Mailbox\MailboxHealth;
use App\Livewire\Mailbox\MailboxList;
use App\Livewire\Response\ResponseInbox;
use App\Livewire\Response\ResponseView;
use App\Livewire\Template\SequenceBuilder;
use App\Livewire\Template\TemplateEditor;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

// Protected routes requiring authentication
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Profile (from Breeze) - blocked during impersonation for security
    Route::view('profile', 'profile')->name('profile')->middleware('not-impersonating');

    // Mailboxes
    Route::prefix('mailboxes')->name('mailboxes.')->group(function () {
        Route::get('/', MailboxList::class)->name('index');
        Route::get('/create', MailboxForm::class)->name('create');
        Route::get('/{mailbox}/edit', MailboxForm::class)->name('edit');
        Route::get('/{mailbox}/health', MailboxHealth::class)->name('health');
    });

    // Campaigns
    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        Route::get('/', CampaignList::class)->name('index');
        Route::get('/create', CampaignForm::class)->name('create');
        Route::get('/{campaign}/edit', CampaignForm::class)->name('edit');
        Route::get('/{campaign}/insights', CampaignInsights::class)->name('insights');

        // Leads within campaign
        Route::get('/{campaign}/leads', LeadList::class)->name('leads.index');
        Route::get('/{campaign}/leads/import', LeadImport::class)->name('leads.import');
        Route::get('/{campaign}/leads/create', LeadForm::class)->name('leads.create');
        Route::get('/{campaign}/leads/{lead}/edit', LeadForm::class)->name('leads.edit');

        // Templates within campaign
        Route::get('/{campaign}/templates', SequenceBuilder::class)->name('templates.index');
        Route::get('/{campaign}/templates/create', TemplateEditor::class)->name('templates.create');
        Route::get('/{campaign}/templates/{template}/edit', TemplateEditor::class)->name('templates.edit');
    });

    // Response Inbox
    Route::prefix('responses')->name('responses.')->group(function () {
        Route::get('/', ResponseInbox::class)->name('index');
        Route::get('/{response}', ResponseView::class)->name('show');
    });
});

// Admin routes (requires admin role)
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', AdminDashboard::class)->name('dashboard');
        Route::get('/users', UserList::class)->name('users.index');
        Route::get('/audit-log', AuditLog::class)->name('audit-log');

        // Stop impersonation (accessible while impersonating)
        Route::post('/stop-impersonate', function () {
            $targetUserId = session('impersonating');
            $startedAt = session('impersonation_started_at');

            if ($targetUserId) {
                $targetUser = \App\Models\User::find($targetUserId);

                if ($targetUser && $startedAt) {
                    $durationSeconds = (int) \Carbon\Carbon::parse($startedAt)->diffInSeconds(now());
                    \App\Services\AuditLogService::logImpersonationStop($targetUser, $durationSeconds);
                }
            }

            session()->forget(['impersonating', 'impersonated_by', 'impersonation_started_at']);

            return redirect()->route('admin.users.index')->with('message', 'Stopped emulating user.');
        })->name('stop-impersonate')->withoutMiddleware('admin');
    });

// Dev Tools (local environment only)
if (app()->environment('local', 'testing')) {
    Route::middleware(['auth'])->prefix('dev')->name('dev.')->group(function () {
        Route::get('/mail', DevMailTool::class)->name('mail');
    });
}

require __DIR__.'/auth.php';
