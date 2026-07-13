<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('staff_no')->unique();
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('teacher_class_subject', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['teacher_id', 'class_section_id', 'subject_id'], 'tcs_teacher_class_subject_unique');
            $table->index(['class_section_id', 'subject_id']);
        });

        Schema::table('class_sections', function (Blueprint $table): void {
            $table->foreign('homeroom_teacher_id')->references('id')->on('teachers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('class_sections', function (Blueprint $table): void {
            $table->dropForeign(['homeroom_teacher_id']);
        });
        Schema::dropIfExists('teacher_class_subject');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('teachers');
    }
};
