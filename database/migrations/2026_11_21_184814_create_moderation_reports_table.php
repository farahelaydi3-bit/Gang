<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moderation_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('review_id')
                ->constrained('reviews')
                ->cascadeOnDelete();

            $table->foreignId('court_id')
                ->nullable()
                ->constrained('courts')
                ->nullOnDelete();

            $table->foreignId('admin_id')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->enum('action', ['flagged', 'auto_banned', 'unbanned', 'dismissed']);
            $table->enum('ai_decision', ['spam', 'abusive', 'safe'])->nullable();
            $table->text('ai_reason')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('reviewed_by_super_admin_id')
                ->nullable()
                ->constrained('super_admins')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_reports');
    }
};