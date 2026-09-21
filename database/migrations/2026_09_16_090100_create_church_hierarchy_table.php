<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('church_hierarchy', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('church_id');
            $table->unsignedBigInteger('parent_church_id');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamps();

            $table->foreign('church_id')->references('id')->on('churches');
            $table->foreign('parent_church_id')->references('id')->on('churches');
        });
    }

    public function down()
    {
        Schema::dropIfExists('church_hierarchy');
    }
};
