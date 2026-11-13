<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weight_standards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('type_code');
            $table->unsignedInteger('nominal');
            $table->unsignedInteger('tolerance')->default(40);
            $table->timestamps();
            $table->unique(['project_id', 'type_code']);
        });

        Schema::create('dc_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('prog_no')->nullable();
            $table->string('dc_code');
            $table->string('part_number')->nullable();
            $table->string('model')->nullable();
            $table->string('type_code')->nullable();
            $table->unsignedInteger('scale_prog')->nullable();
            $table->string('status')->nullable();
            $table->string('remark')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'type_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dc_codes');
        Schema::dropIfExists('weight_standards');
    }
};