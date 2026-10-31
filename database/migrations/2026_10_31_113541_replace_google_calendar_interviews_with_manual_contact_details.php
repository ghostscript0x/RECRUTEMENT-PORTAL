<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->string('email_subject', 200)->nullable();
            $table->text('email_message')->nullable();
        });

        DB::table('interviews')
            ->where('mode', 'online')
            ->whereNotNull('meeting_url')
            ->update([
                'location' => DB::raw('meeting_url'),
                'mode' => 'google_meet',
            ]);

        DB::table('interviews')
            ->where('mode', 'online')
            ->update(['mode' => 'google_meet']);

        Schema::table('interviews', function (Blueprint $table) {
            $table->dropUnique(['calendar_event_id']);
            $table->dropColumn(['meeting_url', 'calendar_event_id', 'calendar_sync_status']);
        });

        Schema::dropIfExists('google_calendar_connections');
    }

    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->text('meeting_url')->nullable();
            $table->string('calendar_event_id', 128)->nullable()->unique();
            $table->string('calendar_sync_status', 16)->default('not_required');
            $table->dropColumn(['email_subject', 'email_message']);
        });

        DB::table('interviews')
            ->where('mode', 'google_meet')
            ->update([
                'meeting_url' => DB::raw('location'),
                'mode' => 'online',
            ]);

        Schema::create('google_calendar_connections', function (Blueprint $table) {
            $table->id();
            $table->string('google_email');
            $table->text('refresh_token');
            $table->foreignId('connected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
};
