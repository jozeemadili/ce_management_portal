<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Training mode for church services: a training service is open all day,
 * never marks absentees, sends no follow-up SMS and is left out of the
 * dashboard and reports. New souls first recorded in one are flagged too
 * (hidden from the New Souls list) so "Reset" can remove them.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->boolean('is_training')->default(false);
        });

        Schema::table('members', function (Blueprint $table) {
            $table->boolean('is_training')->default(false);
        });
    }

    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('is_training');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('is_training');
        });
    }
};
