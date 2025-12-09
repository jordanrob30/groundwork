<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailbox_sending_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mailbox_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('emails_sent')->default(0);
            $table->integer('emails_delivered')->default(0);
            $table->integer('emails_bounced')->default(0);
            $table->integer('emails_failed')->default(0);
            $table->decimal('bounce_rate', 5, 2)->default(0);
            $table->timestamps();

            // Unique constraint for one row per day per mailbox
            $table->unique(['mailbox_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailbox_sending_stats');
    }
};
