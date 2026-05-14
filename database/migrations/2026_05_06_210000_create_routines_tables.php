<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('description')->nullable();
            $table->enum('period', ['morning', 'afternoon', 'evening']);
            $table->unsignedInteger('base_xp')->default(5);
            $table->unsignedInteger('bonus_xp')->default(50);
            $table->enum('goal_type', ['streak', 'volume']);
            $table->unsignedInteger('goal_target');
            $table->unsignedInteger('window_days')->nullable();
            $table->date('active_from');
            $table->date('active_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('child_routine_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('routine_template_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('approved_count')->default(0);
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('best_streak')->default(0);
            $table->unsignedInteger('completed_cycles')->default(0);
            $table->date('window_start')->nullable();
            $table->date('last_approved_date')->nullable();
            $table->timestamp('last_completed_at')->nullable();
            $table->timestamps();

            $table->unique(['child_id', 'routine_template_id']);
        });

        Schema::table('missions', function (Blueprint $table) {
            $table->foreignId('routine_template_id')->nullable()->after('skill_domain_id')->constrained()->nullOnDelete();
            $table->index(['routine_template_id', 'mission_date']);
        });
    }

    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropIndex(['routine_template_id', 'mission_date']);
            $table->dropConstrainedForeignId('routine_template_id');
        });

        Schema::dropIfExists('child_routine_progress');
        Schema::dropIfExists('routine_templates');
    }
};
