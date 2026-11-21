<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_games', function (Blueprint $table) {
            $table->decimal('player_price', 10, 2)->default(0)->after('max_players');
        });
    }

    public function down(): void
    {
        Schema::table('group_games', function (Blueprint $table) {
            $table->dropColumn('player_price');
        });
    }
};