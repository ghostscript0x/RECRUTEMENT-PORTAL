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
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('assigned_reviewer_id')->nullable()->after('position_id')->constrained('users')->nullOnDelete();
            $table->index(['assigned_reviewer_id', 'submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex(['assigned_reviewer_id', 'submitted_at']);
            $table->dropConstrainedForeignId('assigned_reviewer_id');
        });
    }
};
