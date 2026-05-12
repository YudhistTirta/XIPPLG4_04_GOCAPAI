<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SavingsGoal extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'description',
        'target_amount',
        'current_amount',
        'status',
        'target_frequency',
        'target_amount_per_frequency',
        'started_at',
        'target_date',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'target_amount_per_frequency' => 'decimal:2',
        'started_at' => 'datetime',
        'target_date' => 'datetime',
    ];

    // Relationships

    /**
     * Get the user who owns this savings goal
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category for this savings goal
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all transactions for this savings goal
     */
    public function transactions()
    {
        return $this->hasMany(SavingsTransaction::class);
    }

    /**
     * Get all savings targets for this savings goal
     */
    public function savingsTargets()
    {
        return $this->hasMany(SavingsTarget::class);
    }

    // Accessors & Mutators

    /**
     * Get the percentage of goal completed
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->target_amount == 0) {
            return 0;
        }
        return round(($this->current_amount / $this->target_amount) * 100, 2);
    }

    /**
     * Get the remaining amount needed
     */
    public function getRemainingAmountAttribute()
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    /**
     * Check if goal is completed
     */
    public function getIsCompletedAttribute()
    {
        return $this->current_amount >= $this->target_amount;
    }

    // Scopes

    /**
     * Scope to get only active goals
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get only completed goals
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get goals for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
