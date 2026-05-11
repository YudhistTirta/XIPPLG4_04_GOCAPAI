<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('savings_targets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('savings_goal_id')->constrained('savings_goals')->onDelete('cascade');
            $table->enum('frequency_type', ['daily', 'weekly', 'monthly'])->default('weekly');
            $table->decimal('target_amount', 15, 2);
            $table->timestamp('period_start_date');
            $table->timestamp('period_end_date');
            $table->decimal('amount_collected', 15, 2)->default(0);
            $table->enum('status', ['pending', 'on_track', 'behind', 'completed'])->default('pending');
            $table->timestamps();

            // Indexes untuk performa query
            $table->index('savings_goal_id');
            $table->index('status');
            $table->index('period_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_targets');
    }
};
