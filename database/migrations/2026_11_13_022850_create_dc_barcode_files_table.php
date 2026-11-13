<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dc_barcode_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dc_code_id')->constrained('dc_codes')->cascadeOnDelete();
            $table->unique('dc_code_id');
            $table->string('filename');
            $table->string('mime', 100);
            $table->unsignedInteger('size');
            $table->longText('data'); // isi file dalam bentuk base64
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dc_barcode_files');
    }
};