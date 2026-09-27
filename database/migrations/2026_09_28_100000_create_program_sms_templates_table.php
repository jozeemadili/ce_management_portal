<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SMS templates for Programs: registration (sent automatically on
 * registration), payment (sent when a payment is confirmed) and reminder
 * (sent from the program page). program_id NULL = default for all programs;
 * a program-specific template overrides the default of the same type.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('program_sms_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id')->nullable();
            $table->string('type', 30);
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->index(['type', 'program_id']);
        });

        $now = now();
        DB::table('program_sms_templates')->insert([
            [
                'program_id' => null,
                'type' => 'registration',
                'body' => 'Dear {first_name}, you are registered for {program} on {date}. Your registration code is {code}. Christ Embassy',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'program_id' => null,
                'type' => 'payment',
                'body' => 'Dear {first_name}, we have received {payment_amount} for {program} ({code}). Total paid {paid}, balance {balance}. Thank you.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'program_id' => null,
                'type' => 'reminder',
                'body' => 'Reminder: {program} is on {date} at {time}, {venue}. Your registration code is {code}. See you there!',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('program_sms_templates');
    }
};
