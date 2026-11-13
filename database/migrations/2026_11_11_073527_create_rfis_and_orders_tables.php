<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('rfi_no');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('budget')->default(0);
            $table->timestamps();
            $table->unique(['project_id', 'rfi_no']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rfi_id')->nullable()->constrained()->nullOnDelete();
            $table->string('period', 7);                       // contoh: 2026-03
            $table->unsignedInteger('item_no')->nullable();    // nomor urut dalam bulan
            $table->string('pr_no')->nullable();
            $table->string('description');
            $table->unsignedBigInteger('value')->nullable();
            $table->json('approvals')->nullable();
            $table->boolean('po_issued')->default(false);
            $table->string('po_no')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('rfis');
    }
};