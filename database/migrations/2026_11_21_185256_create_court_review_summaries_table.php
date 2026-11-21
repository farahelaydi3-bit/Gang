<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('court_review_summaries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('court_id')
                ->unique()
                ->constrained('courts')
                ->cascadeOnDelete();

            $table->unsignedInteger('total_reviews')->default(0);
            $table->unsignedInteger('safe_reviews')->default(0);
            $table->unsignedInteger('spam_reviews')->default(0);
            $table->unsignedInteger('abusive_reviews')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);

            $table->text('summary')->nullable();         // AI text summary
            $table->text('top_complaints')->nullable();  // AI summarized complaints
            $table->text('top_praises')->nullable();     // AI summarized praises

            $table->timestamp('generated_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('court_review_summaries');
    }
};