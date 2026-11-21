<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('owner_websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->unique()->constrained('admins')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 160)->unique();
            $table->string('tagline', 180)->nullable();
            $table->text('intro')->nullable();
            $table->string('theme', 30)->default('midnight');
            $table->string('logo_path')->nullable();
            $table->string('html_path')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_active')->default(true); // Super Admin controls this field only.
            $table->timestamp('trial_started_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('paid_until')->nullable();
            $table->timestamp('ai_generated_at')->nullable();
            $table->timestamps();
        });
        Schema::create('owner_website_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_website_id')->constrained('owner_websites')->cascadeOnDelete();
            $table->foreignId('sport_id')->constrained('sports')->restrictOnDelete();
            $table->string('title', 120);
            $table->text('description')->nullable();
            $table->json('images')->nullable(); // Up to FIVE images PER sport, never per court.
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['owner_website_id', 'sport_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_website_sections');
        Schema::dropIfExists('owner_websites');
    }
};
