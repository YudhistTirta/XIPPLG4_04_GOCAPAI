<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavingsTransaction extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'savings_goal_id',
        'amount',
        'type',
        'description',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    // Relationships

    /**
     * Get the user who made this transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the savings goal this transaction belongs to
     */
    public function savingsGoal()
    {
        return $this->belongsTo(SavingsGoal::class);
    }

    // Scopes

    /**
     * Scope to get only deposit transactions
     */
    public function scopeDeposits($query)
    {
        return $query->where('type', 'deposit');
    }

    /**
     * Scope to get only withdrawal transactions
     */
    public function scopeWithdrawals($query)
    {
        return $query->where('type', 'withdrawal');
    }

    /**
     * Scope to get transactions for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get transactions for a specific goal
     */
    public function scopeForGoal($query, $goalId)
    {
        return $query->where('savings_goal_id', $goalId);
    }

    /**
     * Scope to get recent transactions
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('transaction_date', '>=', now()->subDays($days));
    }
}
