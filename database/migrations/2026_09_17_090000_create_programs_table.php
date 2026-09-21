<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Split out of the programs-module migration so members can be created
     * after programs (members.first_visit_program_id references this table)
     * while the rest of the programs module - which references members -
     * still runs afterwards, unchanged.
     */
    public function up()
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('banner_path')->nullable();
            $table->enum('category', [
                'service', 'meeting', 'program', 'event', 'course',
                'crusade', 'conference', 'cell_meeting', 'other',
            ])->default('other');
            $table->enum('classification', ['recurring', 'special'])->default('special');
            $table->enum('scope', ['global', 'church', 'department', 'cell'])->default('church');
            $table->unsignedBigInteger('church_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('cell_group_id')->nullable();
            $table->string('organizer')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_frequency', ['daily', 'weekly', 'monthly', 'custom'])->nullable();
            $table->json('recurrence_days')->nullable();
            $table->enum('access_type', ['free', 'paid'])->default('free');
            $table->decimal('registration_fee', 15, 2)->default(0);
            $table->string('currency', 10)->default('TZS');
            $table->boolean('qr_enabled')->default(false);
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->foreign('church_id')->references('id')->on('churches')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('cell_group_id')->references('id')->on('cell_groups')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('programs');
    }
};
