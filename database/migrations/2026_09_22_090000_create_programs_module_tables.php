<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // `programs` is created by 2026_09_17_090000_create_programs_table.php,
        // ahead of `members` (which references it) and this file (whose
        // program_registrations/program_visitors/program_attendances
        // reference `members`) - see that migration for why.

        Schema::create('program_occurrences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->date('occurrence_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();

            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->unique(['program_id', 'occurrence_date']);
        });

        Schema::create('program_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('member_id');
            $table->string('registration_reference')->unique()->nullable();
            $table->enum('registration_status', ['registered', 'cancelled'])->default('registered');
            $table->enum('payment_status', ['free', 'paid', 'pending', 'failed', 'refunded'])->default('free');
            $table->decimal('amount_paid', 15, 2)->nullable();
            $table->dateTime('registered_at')->nullable();
            $table->timestamps();

            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->foreign('member_id')->references('id')->on('members')->cascadeOnDelete();
            $table->index(['program_id', 'member_id']);
        });

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
                'new', 'contacted', 'follow_up', 'connected_to_cell', 'became_member', 'closed',
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

        Schema::create('program_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('occurrence_id');
            $table->unsignedBigInteger('member_id')->nullable();
            $table->unsignedBigInteger('visitor_id')->nullable();
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->enum('attendance_status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->enum('check_in_method', ['manual', 'qr'])->default('manual');
            $table->dateTime('checked_in_at')->nullable();
            $table->integer('recorded_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->foreign('occurrence_id')->references('id')->on('program_occurrences')->cascadeOnDelete();
            $table->foreign('member_id')->references('id')->on('members')->cascadeOnDelete();
            $table->foreign('visitor_id')->references('id')->on('program_visitors')->cascadeOnDelete();
            $table->foreign('registration_id')->references('id')->on('program_registrations')->nullOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['occurrence_id', 'member_id']);
            $table->unique(['occurrence_id', 'visitor_id']);
        });

        Schema::create('program_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('actor_id')->nullable();
            $table->string('action');
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['subject_type', 'subject_id']);
        });

        $now = now();
        $permissions = [
            ['code' => 'PROGRAMS_VIEW', 'name' => 'View Programs'],
            ['code' => 'PROGRAMS_CREATE', 'name' => 'Create Programs'],
            ['code' => 'PROGRAMS_EDIT', 'name' => 'Edit Programs'],
            ['code' => 'PROGRAMS_DELETE', 'name' => 'Delete Programs'],
            ['code' => 'PROGRAMS_MANAGE_RECURRING', 'name' => 'Manage Recurring Programs'],
            ['code' => 'PROGRAMS_MANAGE_REGISTRATIONS', 'name' => 'Manage Program Registrations'],
            ['code' => 'ATTENDANCE_VIEW', 'name' => 'View Attendance'],
            ['code' => 'ATTENDANCE_RECORD', 'name' => 'Record Attendance'],
            ['code' => 'ATTENDANCE_EDIT', 'name' => 'Edit Attendance'],
            ['code' => 'ATTENDANCE_DELETE', 'name' => 'Delete Attendance'],
            ['code' => 'ATTENDANCE_SCAN_QR', 'name' => 'Scan QR Attendance'],
            ['code' => 'NEW_SOULS_VIEW', 'name' => 'View New Souls'],
            ['code' => 'NEW_SOULS_CREATE', 'name' => 'Create New Souls'],
            ['code' => 'NEW_SOULS_EDIT', 'name' => 'Edit New Souls'],
            ['code' => 'NEW_SOULS_REPORTS', 'name' => 'View New Souls Reports'],
            ['code' => 'PROGRAM_REPORTS_VIEW', 'name' => 'View Program Reports'],
            ['code' => 'PROGRAM_REPORTS_EXPORT', 'name' => 'Export Program Reports'],
        ];

        foreach ($permissions as $permission) {
            $exists = DB::table('permissions')->where('code', $permission['code'])->exists();
            if (!$exists) {
                DB::table('permissions')->insert([
                    'code' => $permission['code'],
                    'name' => $permission['name'],
                    'description' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('program_audit_logs');
        Schema::dropIfExists('program_attendances');
        Schema::dropIfExists('program_visitors');
        Schema::dropIfExists('program_registrations');
        Schema::dropIfExists('program_occurrences');

        DB::table('permissions')->whereIn('code', [
            'PROGRAMS_VIEW', 'PROGRAMS_CREATE', 'PROGRAMS_EDIT', 'PROGRAMS_DELETE',
            'PROGRAMS_MANAGE_RECURRING', 'PROGRAMS_MANAGE_REGISTRATIONS',
            'ATTENDANCE_VIEW', 'ATTENDANCE_RECORD', 'ATTENDANCE_EDIT', 'ATTENDANCE_DELETE',
            'ATTENDANCE_SCAN_QR', 'NEW_SOULS_VIEW', 'NEW_SOULS_CREATE', 'NEW_SOULS_EDIT',
            'NEW_SOULS_REPORTS', 'PROGRAM_REPORTS_VIEW', 'PROGRAM_REPORTS_EXPORT',
        ])->delete();
    }
};
