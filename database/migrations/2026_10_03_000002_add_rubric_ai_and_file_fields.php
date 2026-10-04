<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_questions', function (Blueprint $table) {
            // Essay rubric: [{criteria, description, weight}] — weights sum to 100.
            $table->json('rubric')->nullable()->after('model_answer');
            $table->string('explanation')->nullable()->after('rubric');
        });

        Schema::table('assessment_submissions', function (Blueprint $table) {
            $table->json('ai_details')->nullable()->after('feedback');
            $table->string('ai_model')->nullable()->after('ai_details');
            $table->text('ai_error')->nullable()->after('ai_model');
        });

        Schema::table('chapter_contents', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('url');
            $table->boolean('is_free_preview')->default(false)->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('chapter_contents', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'is_free_preview']);
        });

        Schema::table('assessment_submissions', function (Blueprint $table) {
            $table->dropColumn(['ai_details', 'ai_model', 'ai_error']);
        });

        Schema::table('assessment_questions', function (Blueprint $table) {
            $table->dropColumn(['rubric', 'explanation']);
        });
    }
};
