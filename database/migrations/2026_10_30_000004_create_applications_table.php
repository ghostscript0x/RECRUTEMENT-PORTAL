<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->foreignId('position_id')->constrained()->restrictOnDelete();

            // Personal information
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->index();
            $table->string('phone', 32);
            $table->date('date_of_birth');
            $table->string('gender', 16);
            $table->foreignId('state_of_origin_id')->nullable()->constrained('states')->nullOnDelete();
            $table->foreignId('origin_lga_id')->nullable()->constrained('local_governments')->nullOnDelete();

            // Location
            $table->foreignId('residence_state_id')->constrained('states')->restrictOnDelete();
            $table->foreignId('residence_lga_id')->nullable()->constrained('local_governments')->nullOnDelete();
            $table->text('residential_address');

            // Education
            $table->string('highest_qualification', 16);
            $table->string('institution')->nullable();
            $table->string('course_of_study')->nullable();
            $table->smallInteger('graduation_year')->nullable();
            $table->text('additional_qualifications')->nullable();

            // Leadership experience
            $table->boolean('has_leadership')->default(false);
            $table->string('leadership_organization')->nullable();
            $table->string('leadership_position')->nullable();
            $table->string('leadership_duration')->nullable();
            $table->text('leadership_description')->nullable();

            // About yourself
            $table->text('about')->nullable();

            // Workflow
            $table->string('status', 32)->default('submitted')->index();
            $table->timestamp('submitted_at')->index();
            $table->timestamps();

            $table->index(['position_id', 'status']);
        });

        Schema::create('application_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['application_id', 'name']);
            $table->index('name');
        });

        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedInteger('size');
            $table->timestamps();
            $table->index(['application_id', 'type']);
        });

        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['application_id', 'created_at']);
        });

        Schema::create('application_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_notes');
        Schema::dropIfExists('application_status_histories');
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('application_skills');
        Schema::dropIfExists('applications');
    }
};
