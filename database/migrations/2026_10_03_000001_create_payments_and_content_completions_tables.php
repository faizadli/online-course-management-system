<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One payment per enrolment (1:1) — see ERD in the project proposal.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrolment_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount', 8, 2)->default(0);
            $table->string('method')->default('card'); // card, paypal, bank
            $table->string('status')->default('paid'); // pending, paid, failed, refunded
            $table->string('card_brand')->nullable();
            $table->string('card_last4', 4)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // Tracks which lessons a student has completed (drives the progress bar).
        Schema::create('content_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrolment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_content_id')->constrained('chapter_contents')->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['enrolment_id', 'chapter_content_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_completions');
        Schema::dropIfExists('payments');
    }
};
