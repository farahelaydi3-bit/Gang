<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql' && DB::table('information_schema.table_constraints')
            ->where('constraint_schema', DB::getDatabaseName())
            ->where('table_name', 'payments')
            ->where('constraint_name', 'payments_exactly_one_target_chk')
            ->exists()) {
            // The original check knew only about bookings and subscriptions, so it rejected game payments.
            DB::statement('ALTER TABLE payments DROP CHECK payments_exactly_one_target_chk');
        }

        if (Schema::hasColumn('group_games', 'position_slots')) {
            Schema::table('group_games', function (Blueprint $table) {
                $table->dropColumn('position_slots');
            });
        }

        if (Schema::hasColumn('game_players', 'position')) {
            Schema::table('game_players', function (Blueprint $table) {
                $table->dropColumn('position');
            });
        }

        if (! Schema::hasColumn('game_players', 'payment_id')) {
            Schema::table('game_players', function (Blueprint $table) {
                $table->foreignId('payment_id')
                    ->nullable()
                    ->after('status')
                    ->constrained('payments')
                    ->nullOnDelete();
            });
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE game_players MODIFY status ENUM('pending', 'joined', 'cancelled', 'attended') NOT NULL DEFAULT 'joined'");
        } else {
            Schema::table('game_players', function (Blueprint $table) {
                $table->string('status', 20)->default('joined')->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('game_players', function (Blueprint $table) {
            $table->string('position')->nullable()->after('user_id');
            $table->dropConstrainedForeignId('payment_id');
        });

        Schema::table('group_games', function (Blueprint $table) {
            $table->json('position_slots')->nullable()->after('max_players');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE game_players MODIFY status ENUM('joined', 'cancelled', 'attended') NOT NULL DEFAULT 'joined'");
        }
    }
};
