<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasDuplicateApplications = DB::table('applications')
            ->select('position_id')
            ->groupBy('position_id')
            ->groupByRaw('LOWER(email)')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateApplications) {
            throw new \RuntimeException(
                'Cannot enforce one application per email and position until existing duplicates are reviewed.',
            );
        }

        DB::table('applications')->update(['email' => DB::raw('LOWER(email)')]);

        Schema::table('applications', function (Blueprint $table) {
            $table->unique(['position_id', 'email'], 'applications_position_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropUnique('applications_position_email_unique');
        });
    }
};
