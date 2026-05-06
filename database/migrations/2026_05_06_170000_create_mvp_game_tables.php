<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('children', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('total_xp')->default(0);
            $table->timestamps();
        });

        Schema::create('skill_domains', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_domain_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('xp_reward')->default(10);
            $table->date('mission_date');
            $table->timestamps();
        });

        Schema::create('mission_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending_parent');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['mission_id', 'child_id']);
        });

        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('description');
            $table->timestamps();
        });

        Schema::create('child_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->timestamp('unlocked_at');
            $table->timestamps();

            $table->unique(['child_id', 'achievement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_achievements');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('mission_completions');
        Schema::dropIfExists('missions');
        Schema::dropIfExists('skill_domains');
        Schema::dropIfExists('children');
    }
};
