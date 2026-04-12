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
        // 1. Grup menu — e.g. 'sidebar', 'header', 'footer'
        Schema::create('menu_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();           // e.g. 'sidebar', 'topbar'
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Item menu — mendukung hierarki (nested) dan dinamis
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('menu_groups')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menus')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->nullable();         // identifier unik opsional
            $table->string('url')->nullable();          // URL statis atau route name
            $table->boolean('is_route')->default(false); // jika true, 'url' adalah route name
            $table->string('icon')->nullable();         // nama ikon, e.g. 'LayoutDashboard'
            $table->string('badge')->nullable();        // teks badge, e.g. 'New', '5'
            $table->string('badge_variant')->nullable(); // e.g. 'default', 'destructive'
            $table->string('target')->default('_self'); // '_self' | '_blank'
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_divider')->default(false); // tampil sebagai separator
            $table->timestamps();
        });

        // 3. Akses menu berdasarkan role (RBAC)
        Schema::create('menu_roles', function (Blueprint $table) {
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['menu_id', 'role_id']);
        });

        // 4. Akses menu berdasarkan permission langsung
        Schema::create('menu_permissions', function (Blueprint $table) {
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['menu_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_permissions');
        Schema::dropIfExists('menu_roles');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('menu_groups');
    }
};
