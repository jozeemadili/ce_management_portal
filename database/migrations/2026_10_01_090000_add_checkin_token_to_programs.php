<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * programs.checkin_token: code in the printable self check-in QR poster
 * (/checkin/{token}). Random, created the first time the poster is opened.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('checkin_token', 24)->nullable()->unique();
        });
    }

    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropUnique(['checkin_token']);
            $table->dropColumn('checkin_token');
        });
    }
};
