<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('short_form')->nullable()->unique();
            $table->string('registration_number');
            $table->string('license_number');
            $table->string('tin')->nullable();
            $table->string('code');
            $table->string('sale_point_code')->nullable();
            $table->string('category')->default('Others');
            $table->text('logo')->nullable();
            $table->string('email_address')->nullable();
            $table->string('postal_address')->nullable();
            $table->integer('phone_number')->nullable();
            $table->string('contact_person')->nullable();
            $table->integer('created_by')->index();
            $table->dateTime('create_at')->useCurrent();
            $table->integer('authorized_by')->nullable()->index();
            $table->dateTime('authorized_at')->nullable();
            $table->enum('status', ['Active', 'Inactive', 'Pending', 'Rejected'])->default('Active');
            $table->string('color', 200)->nullable();
            $table->longText('call_back_url')->nullable();
            $table->longText('call_back_url2')->nullable();
            $table->text('company_slogan')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('companies');
    }
};
