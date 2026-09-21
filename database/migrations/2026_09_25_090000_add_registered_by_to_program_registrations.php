<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks who performed the registration (front desk / a member
     * registering others), separate from member_id (the attendee). Powers
     * the "people you've invited" list on My Registrations.
     */
    public function up()
    {
        Schema::table('program_registrations', function (Blueprint $table) {
            $table->integer('registered_by')->nullable()->after('member_id');
            $table->foreign('registered_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('program_registrations', function (Blueprint $table) {
            $table->dropForeign(['registered_by']);
            $table->dropColumn('registered_by');
        });
    }
};
