<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_email_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('message_id')->unique();
            $table->string('in_reply_to')->nullable();
            $table->string('subject', 500);
            $table->text('body');
            $table->text('body_plain')->nullable();
            $table->boolean('is_auto_reply')->default(false);
            $table->timestamp('received_at');
            $table->timestamp('analyzed_at')->nullable();
            $table->enum('analysis_status', ['pending', 'analyzing', 'completed', 'failed'])->default('pending');
            $table->enum('interest_level', ['hot', 'warm', 'cold', 'negative'])->nullable();
            $table->enum('problem_confirmation', ['yes', 'no', 'different', 'unclear'])->nullable();
            $table->unsignedTinyInteger('pain_severity')->nullable();
            $table->text('current_solution')->nullable();
            $table->boolean('call_interest')->nullable();
            $table->json('key_quotes')->nullable();
            $table->text('summary')->nullable();
            $table->decimal('analysis_confidence', 3, 2)->nullable();
            $table->enum('review_status', ['unreviewed', 'reviewed', 'actioned'])->default('unreviewed');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'interest_level'], 'idx_campaign_interest');
            $table->index(['campaign_id', 'review_status'], 'idx_campaign_review');
            $table->index('analysis_status', 'idx_analysis_status');
            $table->index('in_reply_to', 'idx_in_reply_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};
