<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Security & Settings > Access Control: the menus / sub-menus given to each
 * user. The menus themselves are listed in config/menu_access.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_menu_grants')) {
            return;
        }

        Schema::create('user_menu_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('menu_key', 100);
            $table->unsignedBigInteger('granted_by')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'menu_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_menu_grants');
    }
};
