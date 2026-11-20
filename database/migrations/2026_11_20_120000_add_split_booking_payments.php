<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('bookings', 'confirmed_at')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dateTime('confirmed_at')->nullable()->after('booking_status');
            });
        }

        if (! Schema::hasColumn('payments', 'payment_stage')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->enum('payment_stage', ['deposit', 'balance'])->nullable()->after('payment_type');
            });
        }

        // Add a replacement foreign-key index before removing the old unique index.
        if (! Schema::hasIndex('payments', 'payments_booking_stage_unique')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unique(['booking_id', 'payment_stage'], 'payments_booking_stage_unique');
            });
        }

        if (Schema::hasIndex('payments', 'payments_booking_id_unique')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropUnique('payments_booking_id_unique');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasIndex('payments', 'payments_booking_id_unique')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unique('booking_id', 'payments_booking_id_unique');
            });
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasIndex('payments', 'payments_booking_stage_unique')) {
                $table->dropUnique('payments_booking_stage_unique');
            }
            if (Schema::hasColumn('payments', 'payment_stage')) {
                $table->dropColumn('payment_stage');
            }
        });

        if (Schema::hasColumn('bookings', 'confirmed_at')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('confirmed_at');
            });
        }
    }
};
