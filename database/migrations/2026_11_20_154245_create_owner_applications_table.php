
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('admin_id')
                ->constrained('admins')
                ->cascadeOnDelete();

            // Facility Information
            $table->string('facility_name');
            $table->string('address');
            $table->unsignedInteger('courts_count');
            $table->text('facility_description');

            // Personal Application Letter
            $table->text('application_message');

            // Application Review
            $table->enum('status', [
                'submitted',
                'approved',
                'rejected'
            ])->default('submitted');

            $table->text('review_notes')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('super_admins')
                ->nullOnDelete();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_applications');
    }
};
