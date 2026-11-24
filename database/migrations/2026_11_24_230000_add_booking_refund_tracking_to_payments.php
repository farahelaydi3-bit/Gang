<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('refund_status', 24)->nullable()->index();
            $table->string('stripe_refund_id')->nullable()->unique();
            $table->unsignedBigInteger('refund_amount_minor')->nullable();
            $table->string('refund_currency', 3)->nullable();
            $table->timestamp('refund_requested_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->string('refund_error')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['refund_status']);
            $table->dropUnique(['stripe_refund_id']);
            $table->dropColumn(['refund_status', 'stripe_refund_id', 'refund_amount_minor',
                'refund_currency', 'refund_requested_at', 'refunded_at', 'refund_error']);
        });
    }
};
