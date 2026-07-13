<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework_rubric_criteria', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('homework_id')->constrained('homework')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('max_score');
            $table->unsignedSmallInteger('position');
            $table->timestamps();
            $table->unique(['homework_id', 'position']);
        });

        Schema::create('submission_rubric_scores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->foreignId('criterion_id')->constrained('homework_rubric_criteria')->cascadeOnDelete();
            $table->unsignedSmallInteger('score');
            $table->timestamps();
            $table->unique(['submission_id', 'criterion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_rubric_scores');
        Schema::dropIfExists('homework_rubric_criteria');
    }
};
