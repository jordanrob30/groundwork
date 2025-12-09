<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email_address');
            $table->enum('status', ['active', 'paused', 'error', 'warmup'])->default('warmup');

            // SMTP Configuration
            $table->string('smtp_host');
            $table->integer('smtp_port')->default(587);
            $table->enum('smtp_encryption', ['none', 'tls', 'ssl'])->default('tls');
            $table->string('smtp_username');
            $table->text('smtp_password'); // Encrypted

            // IMAP Configuration
            $table->string('imap_host');
            $table->integer('imap_port')->default(993);
            $table->enum('imap_encryption', ['none', 'tls', 'ssl'])->default('ssl');
            $table->string('imap_username');
            $table->text('imap_password'); // Encrypted

            // OAuth Configuration (optional)
            $table->boolean('uses_oauth')->default(false);
            $table->string('oauth_provider', 50)->nullable();
            $table->text('oauth_access_token')->nullable(); // Encrypted
            $table->text('oauth_refresh_token')->nullable(); // Encrypted
            $table->timestamp('oauth_expires_at')->nullable();

            // Sending Limits & Warm-up
            $table->integer('daily_limit')->default(50);
            $table->boolean('warmup_enabled')->default(true);
            $table->date('warmup_started_at')->nullable();
            $table->integer('warmup_day')->default(0);

            // Sending Window
            $table->time('send_window_start')->default('09:00:00');
            $table->time('send_window_end')->default('17:00:00');
            $table->boolean('skip_weekends')->default(true);
            $table->string('timezone', 50)->default('UTC');

            // Status Tracking
            $table->timestamp('last_polled_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('last_error_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailboxes');
    }
};
