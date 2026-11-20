<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
    $table->decimal('reserved_carryover_hours', 8, 2)->default(0);
    $table->timestamp('carryover_finalized_at')->nullable();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('subscriptions', function (Blueprint $table) {
    $table->dropColumn([
        'reserved_carryover_hours',
        'carryover_finalized_at',
    ]);
});
    }
};
