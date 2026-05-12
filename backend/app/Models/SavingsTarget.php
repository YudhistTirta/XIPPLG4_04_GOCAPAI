<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SavingsTarget extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'savings_goal_id',
        'frequency_type',
        'target_amount',
        'period_start_date',
        'period_end_date',
        'amount_collected',
        'status',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'amount_collected' => 'decimal:2',
        'period_start_date' => 'datetime',
        'period_end_date' => 'datetime',
    ];

    // Relationships

    /**
     * Get the savings goal this target belongs to
     */
    public function savingsGoal()
    {
        return $this->belongsTo(SavingsGoal::class);
    }

    // Scopes

    /**
     * Scope to get only pending targets
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get only on_track targets
     */
    public function scopeOnTrack($query)
    {
        return $query->where('status', 'on_track');
    }

    /**
     * Scope to get only behind targets
     */
    public function scopeBehind($query)
    {
        return $query->where('status', 'behind');
    }

    /**
     * Scope to get only completed targets
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get targets for a specific goal
     */
    public function scopeForGoal($query, $goalId)
    {
        return $query->where('savings_goal_id', $goalId);
    }
}
