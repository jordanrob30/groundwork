<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('company')->nullable();
            $table->string('role')->nullable();
            $table->string('linkedin_url', 500)->nullable();
            $table->string('custom_field_1')->nullable();
            $table->string('custom_field_2')->nullable();
            $table->string('custom_field_3')->nullable();
            $table->string('custom_field_4')->nullable();
            $table->string('custom_field_5')->nullable();
            $table->enum('status', [
                'pending',
                'queued',
                'contacted',
                'replied',
                'call_booked',
                'converted',
                'unsubscribed',
                'bounced',
            ])->default('pending');
            $table->unsignedInteger('current_sequence_step')->default(0);
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->unique(['email', 'campaign_id'], 'unique_email_per_campaign');
            $table->index('email', 'idx_leads_email');
            $table->index('status', 'idx_leads_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
