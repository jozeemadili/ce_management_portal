<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('department_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('member_id');
            $table->string('role', 100)->default('member');
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('member_id')->references('id')->on('members');
            $table->unique(['department_id', 'member_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('department_members');
    }
};
