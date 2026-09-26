<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Programs get:
 *  - sessions (e.g. Morning 09:00-12:00, Noon 14:00-17:00) that repeat on
 *    every day of the program, with QR check-in per session;
 *  - a price per member designation for paid programs (the attendee's most
 *    senior designation decides the amount; no designation = free);
 *  - recorded payments against a registration (partial payments allowed).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('program_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->string('name', 100);
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
        });

        Schema::create('program_designation_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('designation_id');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->foreign('designation_id')->references('id')->on('member_designations')->cascadeOnDelete();
            $table->unique(['program_id', 'designation_id']);
        });

        Schema::create('program_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registration_id');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method', 100)->nullable();
            $table->string('payment_reference')->nullable();
            // users.id is a plain int in this schema (see registered_by).
            $table->integer('recorded_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('registration_id')->references('id')->on('program_registrations')->cascadeOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('program_registrations', function (Blueprint $table) {
            // Price fixed at registration time, from the attendee's most
            // senior designation (null on registrations made before pricing).
            $table->decimal('amount_due', 15, 2)->nullable()->after('payment_status');
            $table->unsignedBigInteger('priced_designation_id')->nullable()->after('amount_due');

            $table->foreign('priced_designation_id')->references('id')->on('member_designations')->nullOnDelete();
        });

        Schema::table('program_attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('session_id')->nullable()->after('occurrence_id');
            $table->foreign('session_id')->references('id')->on('program_sessions')->nullOnDelete();

            // One check-in per person per session per day (was: per day).
            // The new index is added first: it also starts with
            // occurrence_id, so MySQL can drop the old one even though the
            // occurrence_id foreign key relies on an index.
            $table->unique(['occurrence_id', 'member_id', 'session_id']);
            $table->dropUnique(['occurrence_id', 'member_id']);
        });

        // Existing programs: their single start/end time becomes one session.
        $now = now();
        DB::table('programs')->whereNotNull('start_time')->orderBy('id')->each(function ($program) use ($now) {
            DB::table('program_sessions')->insert([
                'program_id' => $program->id,
                'name' => 'Main Session',
                'start_time' => $program->start_time,
                'end_time' => $program->end_time ?? $program->start_time,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function down()
    {
        Schema::table('program_attendances', function (Blueprint $table) {
            $table->unique(['occurrence_id', 'member_id']);
            $table->dropUnique(['occurrence_id', 'member_id', 'session_id']);
            $table->dropForeign(['session_id']);
            $table->dropColumn('session_id');
        });

        Schema::table('program_registrations', function (Blueprint $table) {
            $table->dropForeign(['priced_designation_id']);
            $table->dropColumn(['amount_due', 'priced_designation_id']);
        });

        Schema::dropIfExists('program_payments');
        Schema::dropIfExists('program_designation_prices');
        Schema::dropIfExists('program_sessions');
    }
};
