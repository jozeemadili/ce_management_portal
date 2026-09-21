<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Matches the live production `users` table (carried over from the
     * insurance-broker template this app was forked from) rather than
     * Laravel's stock default columns - the app reads role, company_id,
     * designation_id, etc. directly off Auth::user().
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('emp_id')->nullable();
            $table->string('branch_id', 11)->nullable();
            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('type', ['1', '2'])->default('2')->nullable();
            $table->integer('mobile')->unique();
            $table->enum('id_type', ['1', '2', '3', '4', '5', '6', '7'])
                ->default('1')
                ->comment('1. National Identification Number (NIN) 2. Voters registration number 3. Passport number 4. Driving License 5. Zanzibar Resident Id(ZANID) 6. Tax Identification Number (TIN) 7. Company Incorporation Certificate Number');
            $table->string('id_number')->nullable();
            $table->string('email')->nullable()->unique();
            $table->dateTime('email_verified_at')->nullable();
            $table->tinyInteger('remember_token')->nullable();
            $table->date('dob')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_first_time_pin')->default(true);
            $table->string('role', 200)->default('Customer Admin');
            $table->integer('company_id')->nullable()->index();
            $table->enum('status', ['Pending', 'Active', 'Inactive', 'Rejected'])->default('Active')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->integer('created_by')->nullable()->index();
            $table->integer('authorized_by')->nullable()->index();
            $table->dateTime('authorized_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('designation_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
