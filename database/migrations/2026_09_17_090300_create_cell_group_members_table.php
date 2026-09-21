<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cell_group_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cell_group_id');
            $table->unsignedBigInteger('member_id');
            $table->string('role', 100)->default('member');
            $table->timestamps();

            $table->foreign('cell_group_id')->references('id')->on('cell_groups');
            $table->foreign('member_id')->references('id')->on('members');
            $table->unique(['cell_group_id', 'member_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cell_group_members');
    }
};
