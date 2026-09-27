<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * members.kingschat_username - optional KingsChat handle (stored without "@").
 * users.must_change_password - set for accounts created with the shared
 * initial password; the user must pick their own password on first login.
 * Existing accounts default to false, so nobody already using the portal is
 * forced through the new screen.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('kingschat_username', 100)->nullable()->after('email');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false);
        });
    }

    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('kingschat_username');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });
    }
};
