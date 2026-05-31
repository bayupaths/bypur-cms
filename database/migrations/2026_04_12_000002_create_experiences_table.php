<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('company');
            $table->string('position');
            $table->string('location')->nullable();
            $table->string('type')->nullable();
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->json('tech_stack')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
