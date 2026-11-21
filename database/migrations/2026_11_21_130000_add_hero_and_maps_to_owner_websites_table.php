<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('owner_websites', function (Blueprint $table) {
            $table->string('hero_image_path')->nullable();
            $table->string('maps_url', 2048)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('owner_websites', function (Blueprint $table) {
            $table->dropColumn(['hero_image_path', 'maps_url']);
        });
    }
};
