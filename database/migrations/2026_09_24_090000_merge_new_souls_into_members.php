<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Merges the "new soul" concept into the members table (tagged via
     * member_type) instead of a separate program_visitors table - a new
     * soul is simply a Member row that hasn't been promoted yet. Also
     * backfills program_attendances.member_id from any existing visitor_id
     * rows, ahead of that column being dropped in the next migration.
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->enum('member_type', ['member', 'new_soul'])->default('member')->after('church_id');
            $table->enum('follow_up_status', [
                'new', 'contacted', 'follow_up', 'foundation_classes', 'connected_to_cell', 'became_member', 'closed',
            ])->nullable()->after('member_type');
            $table->string('gender', 20)->nullable()->after('phone');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->text('notes')->nullable()->after('date_of_birth');
            $table->string('invited_by')->nullable()->after('notes');
            $table->unsignedBigInteger('first_visit_program_id')->nullable()->after('invited_by');
            $table->date('first_visit_date')->nullable()->after('first_visit_program_id');
            $table->integer('recorded_by')->nullable()->after('first_visit_date');

            $table->index('member_type');
            $table->index('phone');

            $table->foreign('first_visit_program_id')->references('id')->on('programs')->nullOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();
        });

        if (Schema::hasTable('program_visitors')) {
            $visitors = DB::table('program_visitors')->get();
            $mapping = [];

            foreach ($visitors as $visitor) {
                if (!is_null($visitor->member_id) && DB::table('members')->where('id', $visitor->member_id)->exists()) {
                    // Already linked to a real member - enrich that row, don't duplicate it.
                    DB::table('members')->where('id', $visitor->member_id)->update([
                        'follow_up_status' => $visitor->status,
                        'gender' => $visitor->gender,
                        'date_of_birth' => $visitor->date_of_birth,
                        'notes' => $visitor->notes,
                        'invited_by' => $visitor->invited_by,
                        'first_visit_program_id' => $visitor->first_visit_program_id,
                        'first_visit_date' => $visitor->first_visit_date,
                        'recorded_by' => $visitor->recorded_by,
                    ]);
                    $mapping[$visitor->id] = $visitor->member_id;
                    continue;
                }

                $churchId = $visitor->church_id ?? DB::table('churches')->whereNull('parent_church_id')->value('id');

                $newMemberId = DB::table('members')->insertGetId([
                    'user_id' => null,
                    'church_id' => $churchId,
                    'member_type' => 'new_soul',
                    'follow_up_status' => $visitor->status,
                    'first_name' => $visitor->first_name,
                    'last_name' => $visitor->last_name,
                    'phone' => $visitor->phone,
                    'gender' => $visitor->gender,
                    'date_of_birth' => $visitor->date_of_birth,
                    'notes' => $visitor->notes,
                    'invited_by' => $visitor->invited_by,
                    'first_visit_program_id' => $visitor->first_visit_program_id,
                    'first_visit_date' => $visitor->first_visit_date,
                    'recorded_by' => $visitor->recorded_by,
                    'created_at' => $visitor->created_at,
                    'updated_at' => $visitor->updated_at,
                ]);

                $mapping[$visitor->id] = $newMemberId;
            }

            foreach ($mapping as $oldVisitorId => $newMemberId) {
                DB::table('program_attendances')
                    ->where('visitor_id', $oldVisitorId)
                    ->update(['member_id' => $newMemberId]);
            }
        }
    }

    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['first_visit_program_id']);
            $table->dropForeign(['recorded_by']);
            $table->dropIndex(['member_type']);
            $table->dropIndex(['phone']);
            $table->dropColumn([
                'member_type', 'follow_up_status', 'gender', 'date_of_birth', 'notes',
                'invited_by', 'first_visit_program_id', 'first_visit_date', 'recorded_by',
            ]);
        });
    }
};
