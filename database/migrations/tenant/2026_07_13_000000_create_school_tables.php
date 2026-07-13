<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('academic_years', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestamps();
        });
        Schema::create('class_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('grade');
            $table->string('section');
            $table->unsignedBigInteger('homeroom_teacher_id')->nullable();
            $table->timestamps();
            $table->unique(['academic_year_id', 'grade', 'section']);
        });
        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('class_section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('dob')->nullable();
            $table->string('enrollment_status')->default('active');
            $table->timestamps();
        });
        Schema::create('parents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->timestamps();
        });
        Schema::create('parent_student', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('relationship');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['parent_id', 'student_id']);
        });
        Schema::create('attendance', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedSmallInteger('period')->default(0);
            $table->string('status');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('justification')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'date', 'period']);
            $table->index(['class_section_id', 'date']);
        });
        Schema::create('wallet_accounts', function (Blueprint $table): void {
            $table->id();
            $table->morphs('owner');
            $table->bigInteger('balance_cached')->default(0);
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
            $table->unique(['owner_type', 'owner_id']);
        });
        Schema::create('wallet_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->constrained('wallet_accounts')->cascadeOnDelete();
            $table->string('type');
            $table->bigInteger('amount');
            $table->bigInteger('balance_after');
            $table->nullableMorphs('reference');
            $table->string('idempotency_key')->unique();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['account_id', 'created_at']);
        });
        Schema::create('outbox_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('event_type');
            $table->json('payload');
            $table->timestamp('available_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamps();
            $table->index(['processed_at', 'available_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbox_messages');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallet_accounts');
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('parent_student');
        Schema::dropIfExists('parents');
        Schema::dropIfExists('students');
        Schema::dropIfExists('class_sections');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('users');
    }
};
