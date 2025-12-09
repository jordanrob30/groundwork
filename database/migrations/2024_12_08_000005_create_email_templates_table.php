<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('subject', 500);
            $table->text('body');
            $table->unsignedInteger('sequence_order')->nullable();
            $table->unsignedInteger('delay_days')->default(3);
            $table->enum('delay_type', ['business', 'calendar'])->default('business');
            $table->boolean('is_library_template')->default(false);
            $table->timestamps();

            $table->index(['campaign_id', 'sequence_order'], 'idx_campaign_sequence');
            $table->index(['user_id', 'is_library_template'], 'idx_user_library');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
