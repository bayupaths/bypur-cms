<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_skill', function (Blueprint $table) {
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('level')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->primary(['profile_id', 'skill_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_skill');
    }
};
