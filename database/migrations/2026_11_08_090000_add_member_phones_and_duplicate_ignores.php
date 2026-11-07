<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - member_phones: extra phone numbers of a person (members.phone stays the
 *   main one). Stored normalised as 0XXXXXXXXX; a number belongs to one
 *   person only. All duplicate checks look at main + extra numbers.
 * - member_duplicate_ignores: pairs marked "not the same person" on the
 *   Duplicate Members page, so they are not suggested again.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('member_phones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->string('phone', 20)->unique();
            $table->string('label', 50)->nullable();
            $table->integer('added_by')->nullable();
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('members')->cascadeOnDelete();
            $table->index('member_id');
        });

        Schema::create('member_duplicate_ignores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_a_id');
            $table->unsignedBigInteger('member_b_id');
            $table->integer('ignored_by')->nullable();
            $table->timestamps();

            $table->foreign('member_a_id')->references('id')->on('members')->cascadeOnDelete();
            $table->foreign('member_b_id')->references('id')->on('members')->cascadeOnDelete();
            $table->unique(['member_a_id', 'member_b_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('member_duplicate_ignores');
        Schema::dropIfExists('member_phones');
    }
};
