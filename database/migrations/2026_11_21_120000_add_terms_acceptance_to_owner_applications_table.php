<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owner_applications', function (Blueprint $table) {
            $table->timestamp('terms_accepted_at')->nullable()->after('application_message');
            $table->string('terms_version', 40)->nullable()->after('terms_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('owner_applications', function (Blueprint $table) {
            $table->dropColumn(['terms_accepted_at', 'terms_version']);
        });
    }
};
