<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parents', function (Blueprint $table): void {
            $table->string('status')->default('active')->index();
            $table->timestamp('archived_at')->nullable();
        });
        Schema::table('teachers', function (Blueprint $table): void {
            $table->string('status')->default('active')->index();
            $table->timestamp('archived_at')->nullable();
        });
        Schema::table('class_sections', function (Blueprint $table): void {
            $table->string('status')->default('active')->index();
            $table->timestamp('archived_at')->nullable();
        });
        Schema::table('subjects', function (Blueprint $table): void {
            $table->string('status')->default('active')->index();
            $table->timestamp('archived_at')->nullable();
        });
        Schema::table('wallet_accounts', function (Blueprint $table): void {
            $table->string('status')->default('active')->index();
            $table->timestamp('archived_at')->nullable();
        });
    }

    public function down(): void
    {
        foreach (['parents', 'teachers', 'class_sections', 'subjects', 'wallet_accounts'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn(['status', 'archived_at']);
            });
        }
    }
};
