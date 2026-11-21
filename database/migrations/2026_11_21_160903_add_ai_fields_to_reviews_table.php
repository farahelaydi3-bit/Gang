<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->enum('ai_decision', ['pending', 'safe', 'spam', 'abusive'])
                ->default('pending')
                ->after('comment');
            $table->text('ai_reason')->nullable()->after('ai_decision');
            $table->timestamp('ai_checked_at')->nullable()->after('ai_reason');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['ai_decision', 'ai_reason', 'ai_checked_at']);
        });
    }
};