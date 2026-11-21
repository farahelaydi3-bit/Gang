
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove the unique index before dropping booking_id
        Schema::table('group_games', function (Blueprint $table) {
            $table->dropUnique('group_games_booking_id_unique');
        });

        // Remove the old booking relationship
        Schema::table('group_games', function (Blueprint $table) {
            $table->dropConstrainedForeignId('booking_id');
        });

        // Add the new court relationship and schedule
        Schema::table('group_games', function (Blueprint $table) {
            $table->foreignId('court_id')
                ->after('admin_id')
                ->constrained('courts')
                ->restrictOnDelete();

            $table->date('game_date');
            $table->time('start_time');
            $table->time('end_time');
        });
    }

    public function down(): void
    {
        Schema::table('group_games', function (Blueprint $table) {
            $table->dropConstrainedForeignId('court_id');

            $table->dropColumn([
                'game_date',
                'start_time',
                'end_time',
            ]);
        });

        Schema::table('group_games', function (Blueprint $table) {
            $table->foreignId('booking_id')
                ->nullable()
                ->unique()
                ->constrained('bookings')
                ->nullOnDelete();
        });
    }
};
