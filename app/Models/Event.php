<?php

namespace App\Models;

use App\Enums\EventSource;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'event_type_id', 'started_at', 'ended_at', 'notes', 'data', 'source', 'google_event_id'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Event $event): void {
            $event->attachments()->get()->each->delete();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, class-string|EventSource|string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'immutable_datetime',
            'ended_at' => 'immutable_datetime',
            'data' => 'array',
            'source' => EventSource::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
