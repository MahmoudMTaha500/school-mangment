<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outbox_messages', function (Blueprint $table): void {
            $table->timestamp('failed_at')->nullable()->after('processed_at');
            $table->text('last_error')->nullable()->after('failed_at');
            $table->index(['processed_at', 'failed_at', 'available_at']);
        });
    }

    public function down(): void
    {
        Schema::table('outbox_messages', function (Blueprint $table): void {
            $table->dropIndex(['processed_at', 'failed_at', 'available_at']);
            $table->dropColumn(['failed_at', 'last_error']);
        });
    }
};
