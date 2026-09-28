<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Church services (recurring programs such as Sunday / Wednesday / Friday
 * service) held in every church at the church's own time:
 *  - service_church_times: a church's own start/end time for a service
 *    (no row = the service's default time on the program).
 *  - program_occurrences.church_id: one occurrence per service, church and
 *    date (NULL for programs that are not per-church, as before).
 *  - members.invited_by_member_id: the member who brought a new soul.
 *  - members.checkin_token: code in the member's personal check-in QR.
 *  - new_soul_followups: follow-up SMS link per new soul per service, and
 *    the testimony/feedback they send back through it.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('service_church_times', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('church_id');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->foreign('church_id')->references('id')->on('churches')->cascadeOnDelete();
            $table->unique(['program_id', 'church_id']);
        });

        Schema::table('program_occurrences', function (Blueprint $table) {
            $table->unsignedBigInteger('church_id')->nullable()->after('program_id');
            $table->dateTime('closed_at')->nullable();

            $table->foreign('church_id')->references('id')->on('churches')->cascadeOnDelete();
            // New unique index first: it also serves the program_id foreign key
            // once the old one is dropped (MySQL needs an index for it).
            $table->unique(['program_id', 'church_id', 'occurrence_date']);
        });

        Schema::table('program_occurrences', function (Blueprint $table) {
            $table->dropUnique(['program_id', 'occurrence_date']);
        });

        Schema::table('members', function (Blueprint $table) {
            $table->unsignedBigInteger('invited_by_member_id')->nullable()->after('invited_by');
            $table->string('checkin_token', 40)->nullable()->unique();

            $table->foreign('invited_by_member_id')->references('id')->on('members')->nullOnDelete();
        });

        Schema::create('new_soul_followups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('occurrence_id')->nullable();
            $table->string('token', 16)->unique();
            $table->dateTime('sms_sent_at')->nullable();
            $table->string('sms_status', 20)->nullable();
            $table->text('feedback')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('members')->cascadeOnDelete();
            $table->foreign('occurrence_id')->references('id')->on('program_occurrences')->nullOnDelete();
            $table->index(['occurrence_id', 'member_id']);
        });

        $now = now();
        DB::table('program_sms_templates')->insert([
            'program_id' => null,
            'type' => 'new_soul_followup',
            'body' => 'Dear {first_name}, thank you for worshipping with us at {church}. We would love to hear what blessed you today: {link}',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down()
    {
        DB::table('program_sms_templates')->where('type', 'new_soul_followup')->delete();

        Schema::dropIfExists('new_soul_followups');

        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['invited_by_member_id']);
            $table->dropUnique(['checkin_token']);
            $table->dropColumn(['invited_by_member_id', 'checkin_token']);
        });

        Schema::table('program_occurrences', function (Blueprint $table) {
            $table->unique(['program_id', 'occurrence_date']);
        });

        Schema::table('program_occurrences', function (Blueprint $table) {
            $table->dropForeign(['church_id']);
            $table->dropUnique(['program_id', 'church_id', 'occurrence_date']);
            $table->dropColumn(['church_id', 'closed_at']);
        });

        Schema::dropIfExists('service_church_times');
    }
};
