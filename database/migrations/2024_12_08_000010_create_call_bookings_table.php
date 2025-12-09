<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('response_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->enum('outcome', ['validated', 'invalidated', 'need_more_info', 'no_show', 'rescheduled'])->nullable();
            $table->text('notes')->nullable();
            $table->string('scheduling_link', 500)->nullable();
            $table->timestamps();

            $table->index('scheduled_at', 'idx_scheduled');
            $table->index(['campaign_id', 'outcome'], 'idx_campaign_outcome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_bookings');
    }
};
