<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Category extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
    ];

    // Relationships

    /**
     * Get all savings goals for this category
     */
    public function savingsGoals()
    {
        return $this->hasMany(SavingsGoal::class);
    }
}
