<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('booking_id')->nullable()->unique()->constrained('bookings')->restrictOnDelete();
    $table->foreignId('subscription_id')->nullable()->unique()->constrained('subscriptions')->restrictOnDelete();
    $table->enum('payment_type', ['deposit', 'full', 'subscription']);
    $table->enum('payment_method', ['cash', 'card', 'wallet', 'other']);
    $table->decimal('amount', 10, 2);
    $table->enum('status', ['pending', 'successful', 'failed', 'refunded'])->default('pending');
    $table->string('transaction_reference')->nullable()->unique();
    $table->dateTime('payment_date')->nullable();
    $table->timestamps();
});
        // Payment target constraints are added after all supported target columns exist.
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
