<?php

namespace App\Models;

use Database\Factories\DailySummaryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'date', 'summary', 'calories_estimate', 'recommendations', 'metrics', 'is_stale', 'generated_at', 'notified_at'])]
class DailySummary extends Model
{
    /** @use HasFactory<DailySummaryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'metrics' => 'array',
            'is_stale' => 'boolean',
            'generated_at' => 'immutable_datetime',
            'notified_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
