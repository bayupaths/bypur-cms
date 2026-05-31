<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('nickname')->nullable();
            $table->string('tagline')->nullable();
            $table->json('taglines')->nullable();
            $table->string('avatar')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('location')->nullable();
            $table->text('bio')->nullable();
            $table->string('resume_url')->nullable();
            $table->string('website_url')->nullable(); // URL portfolio publik milik pemilik
            $table->boolean('is_available')->default(false);

            // Personal
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('birth_date')->nullable();

            // Address
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable()->default('Indonesia');
            $table->string('postal_code', 10)->nullable();

            // Social links (platform lainnya disimpan JSON)
            $table->json('socials')->nullable(); // e.g. {"github":"...","linkedin":"...","twitter":"..."}

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
