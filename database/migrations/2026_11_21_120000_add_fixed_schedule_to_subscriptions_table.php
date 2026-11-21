<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('is_fixed_schedule')->default(false);
            $table->json('schedule_weekdays')->nullable();
            $table->time('scheduled_start_time')->nullable();
            $table->time('scheduled_end_time')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'is_fixed_schedule', 'schedule_weekdays',
                'scheduled_start_time', 'scheduled_end_time',
            ]);
        });
    }
};
