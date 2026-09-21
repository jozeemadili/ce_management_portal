<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Adds "Foundation Classes" as a follow-up stage between "Follow-Up In
     * Progress" and "Connected to Cell" - the typical next step for a new
     * convert before they're plugged into a cell group.
     *
     * MySQL-only: `ALTER TABLE ... MODIFY ... ENUM(...)` has no Postgres
     * equivalent. This is safe to skip entirely on other drivers because
     * program_visitors is fully superseded two migrations later
     * (2026_09_24_090000_merge_new_souls_into_members.php), where
     * members.follow_up_status is defined with foundation_classes already
     * included in its value list from the start - so on a fresh non-MySQL
     * install there's nothing left for this migration to do.
     */
    public function up()
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE program_visitors MODIFY status ENUM('new','contacted','follow_up','foundation_classes','connected_to_cell','became_member','closed') NOT NULL DEFAULT 'new'");
    }

    public function down()
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("UPDATE program_visitors SET status = 'follow_up' WHERE status = 'foundation_classes'");
        DB::statement("ALTER TABLE program_visitors MODIFY status ENUM('new','contacted','follow_up','connected_to_cell','became_member','closed') NOT NULL DEFAULT 'new'");
    }
};
