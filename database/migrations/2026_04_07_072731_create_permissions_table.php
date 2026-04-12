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
        // 1. Permissions — aksi yang bisa dilakukan
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();           // e.g. 'posts.create'
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->string('group')->nullable();        // e.g. 'posts', 'users'
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        // 2. Roles — kumpulan permission
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();           // e.g. 'admin', 'editor'
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->string('guard_name')->default('web');
            $table->unsignedTinyInteger('level')->default(0); // hierarki role (semakin tinggi = lebih powerful)
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // 3. Role ↔ Permission (RBAC pivot)
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        // 4. User ↔ Role (RBAC pivot)
        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamp('expires_at')->nullable(); // role sementara
            $table->primary(['user_id', 'role_id']);
        });

        // 5. User ↔ Permission langsung — untuk override per-user (RBAC direct)
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->boolean('granted')->default(true); // true = allow, false = explicit deny
            $table->timestamp('expires_at')->nullable();
            $table->primary(['user_id', 'permission_id']);
        });

        // 6. Permission Conditions — aturan ABAC (atribut-atribut yang harus terpenuhi)
        Schema::create('permission_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->string('attribute');  // e.g. 'user.department', 'resource.owner_id', 'env.ip'
            $table->string('operator');   // '=', '!=', '>', '>=', '<', '<=', 'in', 'not_in'
            $table->string('value');      // nilai yang dibandingkan
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_conditions');
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
