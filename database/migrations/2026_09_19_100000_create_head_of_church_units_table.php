<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('head_of_church_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('head_of_unit');
            $table->dateTime('reg_date');
            $table->integer('reg_by')->nullable();

            $table->foreign('unit_id')->references('id')->on('churches')->cascadeOnDelete();
            $table->foreign('head_of_unit')->references('id')->on('members')->cascadeOnDelete();
            $table->foreign('reg_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['unit_id', 'reg_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('head_of_church_units');
    }
};
