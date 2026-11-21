<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_games', function (Blueprint $table) {

            // Remove the old booking relationship
            $table->dropConstrainedForeignId('booking_id');

            // Link the group game directly to a court
            $table->foreignId('court_id')
                ->after('admin_id')
                ->constrained('courts')
                ->restrictOnDelete();

            // Game schedule
            $table->date('game_date');
            $table->time('start_time');
            $table->time('end_time');

        });
    }

    public function down(): void
    {
        Schema::table('group_games', function (Blueprint $table) {

            // Remove the new court relationship and schedule
            $table->dropConstrainedForeignId('court_id');

            $table->dropColumn([
                'game_date',
                'start_time',
                'end_time',
            ]);

            // Restore the old booking relationship
            $table->foreignId('booking_id')
                ->nullable()
                ->constrained('bookings')
                ->nullOnDelete();

        });
    }
};