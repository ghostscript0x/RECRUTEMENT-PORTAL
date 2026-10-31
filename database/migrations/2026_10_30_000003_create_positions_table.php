<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('department')->nullable();
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->date('opens_at')->nullable();
            $table->date('closes_at')->nullable();
            $table->string('status', 20)->default('draft')->index();
            // Document requirement config: [["key"=>"cv","label"=>"Curriculum Vitae","required"=>true], ...]
            $table->json('required_documents')->nullable();
            $table->json('optional_documents')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
