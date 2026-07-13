<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homework', function (Blueprint $table): void {
            $table->string('status')->default('assigned')->index();
            $table->timestamp('archived_at')->nullable();
        });
        Schema::table('submissions', function (Blueprint $table): void {
            $table->string('status')->default('submitted')->index();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table): void {
            $table->dropForeign(['graded_by']);
            $table->dropColumn(['status', 'graded_by', 'graded_at']);
        });
        Schema::table('homework', function (Blueprint $table): void {
            $table->dropColumn(['status', 'archived_at']);
        });
    }
};
