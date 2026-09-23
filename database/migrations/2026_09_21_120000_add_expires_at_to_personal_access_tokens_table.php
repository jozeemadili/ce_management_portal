<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The local/live personal_access_tokens table was created from an older
     * Sanctum schema that predates the expires_at column
     * (2019_12_14_000001_create_personal_access_tokens_table.php has been
     * corrected for fresh installs, but this table already exists here) -
     * Sanctum's HasApiTokens::createToken() writes to this column
     * unconditionally, so token issuance fails without it.
     */
    public function up()
    {
        if (Schema::hasColumn('personal_access_tokens', 'expires_at')) {
            return;
        }

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('last_used_at');
        });
    }

    public function down()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
