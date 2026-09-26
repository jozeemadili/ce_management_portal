<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Program payments can now be submitted by the attendee (or whoever
 * registered them) with a proof of payment, from the portal or the app.
 * Those wait for a staff member to confirm them; payments recorded by staff
 * are confirmed straight away. Only confirmed payments count as paid.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('program_payments', function (Blueprint $table) {
            // pending = awaiting confirmation, confirmed = counts as paid, rejected
            $table->string('status', 20)->default('confirmed')->after('notes');
            $table->string('proof_path')->nullable()->after('status');
            // users.id is a plain int in this schema (see recorded_by).
            $table->integer('reviewed_by')->nullable()->after('proof_path');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->string('review_note')->nullable()->after('reviewed_at');

            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['registration_id', 'status']);
        });
    }

    public function down()
    {
        Schema::table('program_payments', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropIndex(['registration_id', 'status']);
            $table->dropColumn(['status', 'proof_path', 'reviewed_by', 'reviewed_at', 'review_note']);
        });
    }
};
