<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('churches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('physical_location');
            $table->unsignedBigInteger('designation_id');
            $table->unsignedBigInteger('parent_church_id')->nullable();
            $table->timestamps();
            $table->string('status', 200)->default('ACTIVE');

            $table->foreign('designation_id')->references('id')->on('church_designations');
            $table->foreign('parent_church_id')->references('id')->on('churches');
        });
    }

    public function down()
    {
        Schema::dropIfExists('churches');
    }
};
