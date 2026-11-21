<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_games', function (Blueprint $table) {
            $table->foreignId('booking_id')
                ->nullable()
                ->unique()
                ->after('admin_id')
                ->constrained('bookings')
                ->nullOnDelete();
        });

        Schema::table('waiting_list', function (Blueprint $table) {
            $table->foreignId('group_game_id')
                ->nullable()
                ->after('court_id')
                ->constrained('group_games')
                ->cascadeOnDelete();
            $table->index(['group_game_id', 'status', 'created_at'], 'waitlist_game_status_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unique(['game_player_id', 'payment_stage'], 'payments_game_player_stage_unique');
        });

        Schema::table('game_players', function (Blueprint $table) {
            $table->dateTime('reservation_expires_at')->nullable()->after('joined_at');
        });

        // Admin-created group bookings have a creator admin rather than a player owner.
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_game_player_stage_unique');
        });

        Schema::table('game_players', function (Blueprint $table) {
            $table->dropColumn('reservation_expires_at');
        });

        Schema::table('waiting_list', function (Blueprint $table) {
            $table->dropIndex('waitlist_game_status_idx');
            $table->dropConstrainedForeignId('group_game_id');
        });

        Schema::table('group_games', function (Blueprint $table) {
            $table->dropUnique('group_games_booking_id_unique');
            $table->dropConstrainedForeignId('booking_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
