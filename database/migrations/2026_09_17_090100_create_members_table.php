<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('church_id');
            $table->enum('member_type', ['member', 'new_soul'])->default('member');
            $table->enum('follow_up_status', [
                'new', 'contacted', 'follow_up', 'foundation_classes',
                'connected_to_cell', 'became_member', 'closed',
            ])->nullable();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('gender', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('notes')->nullable();
            $table->string('invited_by')->nullable();
            $table->unsignedBigInteger('first_visit_program_id')->nullable();
            $table->date('first_visit_date')->nullable();
            $table->integer('recorded_by')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->string('foundation_clases', 200)->nullable();
            $table->date('foundation_clases_date')->nullable();
            $table->string('baptism_status', 200)->nullable();
            $table->date('baptism_date')->nullable();
            $table->string('marriage_status', 200)->nullable();
            $table->date('marriage_dates')->nullable();

            $table->foreign('church_id')->references('id')->on('churches');
            $table->foreign('first_visit_program_id')->references('id')->on('programs')->nullOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();
            $table->index('member_type');
            $table->index('phone');
        });
    }

    public function down()
    {
        Schema::dropIfExists('members');
    }
};
