<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_games', function (Blueprint $table) {
            $table->string('skill_level')->nullable()->after('description');
            $table->json('position_slots')->nullable()->after('max_players'); 
            // Example JSON: {"goalkeeper": 2, "defender": 4, "midfielder": 3, "striker": 2}
        });
    }

    public function down(): void
    {
        Schema::table('group_games', function (Blueprint $table) {
            $table->dropColumn(['skill_level', 'position_slots']);
        });
    }
};