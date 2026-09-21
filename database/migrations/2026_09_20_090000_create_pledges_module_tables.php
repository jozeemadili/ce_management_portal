<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pledge_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('banner_path')->nullable();
            $table->decimal('target_amount', 15, 2)->default(0);
            $table->string('currency', 10)->default('TZS');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'closed'])->default('draft');
            $table->enum('scope', ['global', 'church'])->default('global');
            $table->unsignedBigInteger('church_id')->nullable();
            $table->boolean('allow_anonymous')->default(true);
            $table->boolean('live_enabled')->default(true);
            $table->boolean('live_show_amount')->default(true);
            $table->boolean('live_show_pledgers')->default(true);
            $table->boolean('live_show_latest')->default(true);
            $table->boolean('live_show_graph')->default(true);
            $table->boolean('live_show_target')->default(true);
            $table->boolean('live_mask_names')->default(true);
            $table->text('notes')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->foreign('church_id')->references('id')->on('churches')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('pledges', function (Blueprint $table) {
            $table->id();
            $table->string('pledge_reference')->unique()->nullable();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('member_id');
            $table->decimal('amount', 15, 2);
            $table->enum('frequency', ['one_time', 'weekly', 'monthly', 'custom'])->default('one_time');
            $table->enum('status', ['pledged', 'partially_fulfilled', 'fulfilled', 'cancelled'])->default('pledged');
            $table->boolean('anonymous_display')->default(false);
            $table->enum('source', ['member', 'staff'])->default('member');
            $table->integer('recorded_by')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('pledged_at')->nullable();
            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('pledge_campaigns')->cascadeOnDelete();
            $table->foreign('member_id')->references('id')->on('members')->cascadeOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['campaign_id', 'member_id']);
        });

        Schema::create('pledge_contributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pledge_id');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_reference')->nullable();
            $table->string('payment_method')->nullable();
            $table->integer('recorded_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('pledge_id')->references('id')->on('pledges')->cascadeOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('pledge_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('actor_id')->nullable();
            $table->string('action');
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['subject_type', 'subject_id']);
        });

        $now = now();
        $permissions = [
            ['code' => 'PLEDGES_VIEW', 'name' => 'View Pledges'],
            ['code' => 'PLEDGES_CREATE', 'name' => 'Create Pledges'],
            ['code' => 'PLEDGES_EDIT', 'name' => 'Edit Pledges'],
            ['code' => 'PLEDGES_DELETE', 'name' => 'Delete Pledges'],
            ['code' => 'PLEDGES_RECORD_ON_BEHALF', 'name' => 'Record Pledge on Behalf of Member'],
            ['code' => 'PLEDGES_MANAGE_CAMPAIGNS', 'name' => 'Manage Pledge Campaigns'],
            ['code' => 'PLEDGES_MANAGE_CONTRIBUTIONS', 'name' => 'Manage Pledge Contributions'],
            ['code' => 'PLEDGES_VIEW_REPORTS', 'name' => 'View Pledge Reports'],
            ['code' => 'PLEDGES_LIVE_PRESENTATION', 'name' => 'Use Pledges Live Presentation'],
            ['code' => 'PLEDGES_MANAGE_SETTINGS', 'name' => 'Manage Pledges Settings'],
        ];

        foreach ($permissions as $permission) {
            $exists = DB::table('permissions')->where('code', $permission['code'])->exists();
            if (!$exists) {
                DB::table('permissions')->insert([
                    'code' => $permission['code'],
                    'name' => $permission['name'],
                    'description' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('pledge_audit_logs');
        Schema::dropIfExists('pledge_contributions');
        Schema::dropIfExists('pledges');
        Schema::dropIfExists('pledge_campaigns');

        DB::table('permissions')->where('code', 'like', 'PLEDGES_%')->delete();
    }
};
