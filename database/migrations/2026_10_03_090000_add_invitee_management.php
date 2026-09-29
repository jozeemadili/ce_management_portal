<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * New invitees (new souls) management:
 *  - members.location: where the person lives (area), used to choose the
 *    church that follows them up
 *  - new_soul_assignments: history of which church an invitee was assigned
 *    to, by whom and why
 *  - program_occurrences.report_*: a service's report is closed (final
 *    numbers frozen) once its invitees have been assigned
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('location', 255)->nullable()->after('email');
        });

        Schema::create('new_soul_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('from_church_id')->nullable();
            $table->unsignedBigInteger('to_church_id');
            $table->unsignedBigInteger('occurrence_id')->nullable();
            $table->integer('assigned_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('members')->cascadeOnDelete();
            $table->foreign('from_church_id')->references('id')->on('churches')->nullOnDelete();
            $table->foreign('to_church_id')->references('id')->on('churches')->cascadeOnDelete();
            $table->foreign('occurrence_id')->references('id')->on('program_occurrences')->nullOnDelete();
            $table->index('member_id');
        });

        Schema::table('program_occurrences', function (Blueprint $table) {
            $table->dateTime('report_closed_at')->nullable();
            $table->integer('report_closed_by')->nullable();
            $table->json('report_summary')->nullable();
        });
    }

    public function down()
    {
        Schema::table('program_occurrences', function (Blueprint $table) {
            $table->dropColumn(['report_closed_at', 'report_closed_by', 'report_summary']);
        });

        Schema::dropIfExists('new_soul_assignments');

        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
};
