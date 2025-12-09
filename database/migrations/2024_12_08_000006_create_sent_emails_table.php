<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sent_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mailbox_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->string('message_id')->unique();
            $table->string('subject', 500);
            $table->text('body');
            $table->enum('status', ['pending', 'queued', 'sending', 'sent', 'failed', 'bounced'])->default('pending');
            $table->unsignedInteger('sequence_step')->default(1);
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->enum('bounce_type', ['hard', 'soft'])->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['mailbox_id', 'sent_at'], 'idx_sent_emails_mailbox_sent');
            $table->index(['lead_id', 'sequence_step'], 'idx_sent_emails_lead_sequence');
            $table->index('status', 'idx_sent_emails_status');
            $table->index('scheduled_for', 'idx_sent_emails_scheduled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sent_emails');
    }
};
