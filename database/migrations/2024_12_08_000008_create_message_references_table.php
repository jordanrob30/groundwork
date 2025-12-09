<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_email_id')->constrained()->cascadeOnDelete();
            $table->string('reference_message_id');
            $table->unsignedInteger('position');
            $table->timestamps();

            $table->index('reference_message_id', 'idx_reference');
            $table->unique(['sent_email_id', 'reference_message_id'], 'unique_sent_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_references');
    }
};
