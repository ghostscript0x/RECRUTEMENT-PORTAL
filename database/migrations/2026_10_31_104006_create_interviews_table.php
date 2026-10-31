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
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('scheduled_at')->nullable()->index();
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->string('mode', 16)->default('online');
            $table->string('location')->nullable();
            $table->text('meeting_url')->nullable();
            $table->text('instructions')->nullable();
            $table->string('calendar_event_id', 128)->nullable()->unique();
            $table->string('calendar_sync_status', 16)->default('not_required');
            $table->string('status', 24)->default('scheduled')->index();
            $table->char('reschedule_token_hash', 64)->unique();
            $table->text('reschedule_token');
            $table->text('reschedule_reason')->nullable();
            $table->timestamp('reschedule_requested_at')->nullable();
            $table->timestamp('invitation_sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
