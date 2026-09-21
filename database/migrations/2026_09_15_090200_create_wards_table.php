<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wards', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 66);
            $table->unsignedInteger('district_id');

            $table->index('district_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('wards');
    }
};
