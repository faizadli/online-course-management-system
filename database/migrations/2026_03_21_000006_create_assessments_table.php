<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('type'); // mcq, essay
            $table->text('description')->nullable();
            $table->unsignedInteger('pass_score')->default(70);
            $table->unsignedInteger('time_limit_minutes')->nullable();
            $table->string('status')->default('draft'); // draft, published
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
