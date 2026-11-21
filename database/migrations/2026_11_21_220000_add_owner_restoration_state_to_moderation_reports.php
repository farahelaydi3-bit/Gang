<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moderation_reports', function (Blueprint $table) {
            $table->json('pre_ban_state')->nullable();
            $table->foreignId('related_report_id')
                ->nullable()
                ->constrained('moderation_reports')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('moderation_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('related_report_id');
            $table->dropColumn('pre_ban_state');
        });
    }
};
