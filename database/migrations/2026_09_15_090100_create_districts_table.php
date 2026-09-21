<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 76);
            $table->string('description', 199);
            $table->unsignedInteger('region_id');

            $table->index('region_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('districts');
    }
};
