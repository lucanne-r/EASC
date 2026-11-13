<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $t) {
            $t->id();
            $t->string('code');
            $t->string('project_slug')->index();
            $t->string('project')->default('');
            $t->string('machine_area')->default('');
            $t->string('priority')->default('');
            $t->string('category')->default('');
            $t->text('activity');
            $t->string('pic')->default('');
            $t->string('status')->default('Open');
            $t->string('due_date')->default('');
            $t->unsignedInteger('work_order')->default(0);
            $t->string('major_minor')->default('');
            $t->timestamps();
            $t->unique(['project_slug', 'code']);
        });

        Schema::create('activity_attachments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $t->string('original_name');
            $t->string('stored_name')->unique();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_attachments');
        Schema::dropIfExists('activities');
    }
};