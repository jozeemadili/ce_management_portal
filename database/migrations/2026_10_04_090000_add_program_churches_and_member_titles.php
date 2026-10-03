<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - program_churches: a church-scoped program can be held by several
 *   churches together. programs.church_id stays as the first/main church.
 *   Existing church-scoped programs are copied in.
 * - member_titles + members.title_id: Brother, Sister, Deacon... chosen from
 *   a managed list (Church Setup > Member Titles).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('program_churches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('church_id');
            $table->timestamps();

            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->foreign('church_id')->references('id')->on('churches')->cascadeOnDelete();
            $table->unique(['program_id', 'church_id']);
        });

        $now = now();
        foreach (DB::table('programs')->where('scope', 'church')->whereNotNull('church_id')->get(['id', 'church_id']) as $p) {
            DB::table('program_churches')->insert(['program_id' => $p->id, 'church_id' => $p->church_id, 'created_at' => $now, 'updated_at' => $now]);
        }

        Schema::create('member_titles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        foreach (['Brother', 'Sister', 'Deacon', 'Deaconess', 'Elder', 'Evangelist', 'Pastor'] as $i => $name) {
            DB::table('member_titles')->insert(['name' => $name, 'sort_order' => $i + 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        Schema::table('members', function (Blueprint $table) {
            $table->unsignedBigInteger('title_id')->nullable()->after('church_id');
            $table->foreign('title_id')->references('id')->on('member_titles')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['title_id']);
            $table->dropColumn('title_id');
        });
        Schema::dropIfExists('member_titles');
        Schema::dropIfExists('program_churches');
    }
};
