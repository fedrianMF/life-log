<?php

namespace App\Models;

use Database\Factories\EventTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'name', 'has_duration'])]
class EventType extends Model
{
    /** @use HasFactory<EventTypeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'has_duration' => 'boolean',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
