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
        Schema::create('savings_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('savings_goal_id')->constrained('savings_goals')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['deposit', 'withdrawal'])->default('deposit');
            $table->text('description')->nullable();
            $table->timestamp('transaction_date')->useCurrent();
            $table->softDeletes(); // For audit trail
            $table->timestamps();
            
            // Indexes untuk performa query
            $table->index('user_id');
            $table->index('savings_goal_id');
            $table->index('transaction_date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_transactions');
    }
};
