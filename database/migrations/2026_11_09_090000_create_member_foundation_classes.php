<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Foundation School progress: one row per class (1-7) a person has completed. */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('member_foundation_classes')) {
            return;
        }

        Schema::create('member_foundation_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->unsignedTinyInteger('class_no');
            $table->date('completed_on')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();
            $table->unique(['member_id', 'class_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_foundation_classes');
    }
};
