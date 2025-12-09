<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('response_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('insight_type', ['pattern', 'theme', 'objection', 'quote']);
            $table->string('title')->nullable();
            $table->text('content');
            $table->unsignedInteger('frequency')->default(1);
            $table->json('response_ids')->nullable();
            $table->json('metadata')->nullable();
            $table->decimal('confidence_score', 3, 2)->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->index(['campaign_id', 'insight_type'], 'idx_campaign_type');
            $table->index(['campaign_id', 'is_pinned'], 'idx_campaign_pinned');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insights');
    }
};
