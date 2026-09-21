<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Now that every attendee (real member or new soul) is just a
     * members.id, program_attendances.visitor_id is redundant - the
     * existing unique(occurrence_id, member_id) already covers dedup for
     * both. program_visitors itself is fully superseded by members.
     */
    public function up()
    {
        Schema::table('program_attendances', function (Blueprint $table) {
            $table->dropForeign(['visitor_id']);
            $table->dropUnique(['occurrence_id', 'visitor_id']);
            $table->dropColumn('visitor_id');
        });

        Schema::dropIfExists('program_visitors');
    }

    public function down()
    {
        Schema::create('program_visitors', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->unsignedBigInteger('church_id')->nullable();
            $table->string('invited_by')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', [
                'new', 'contacted', 'follow_up', 'foundation_classes', 'connected_to_cell', 'became_member', 'closed',
            ])->default('new');
            $table->unsignedBigInteger('member_id')->nullable();
            $table->unsignedBigInteger('first_visit_program_id')->nullable();
            $table->date('first_visit_date')->nullable();
            $table->integer('recorded_by')->nullable();
            $table->timestamps();

            $table->foreign('church_id')->references('id')->on('churches')->nullOnDelete();
            $table->foreign('member_id')->references('id')->on('members')->nullOnDelete();
            $table->foreign('first_visit_program_id')->references('id')->on('programs')->nullOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();
            $table->index('phone');
        });

        Schema::table('program_attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('visitor_id')->nullable()->after('member_id');
            $table->foreign('visitor_id')->references('id')->on('program_visitors')->cascadeOnDelete();
            $table->unique(['occurrence_id', 'visitor_id']);
        });
    }
};
