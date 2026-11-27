<?php

namespace App\Services;

use App\Models\EventType;
use Illuminate\Database\Eloquent\Collection;

class EventTypeService
{
    /** @return Collection<int, EventType> */
    public function getAvailableEventTypes(): Collection
    {
        return EventType::query()
            ->select(['id', 'slug', 'name', 'has_duration'])
            ->whereIn('slug', ['walk', 'meal', 'exercise', 'expense'])
            ->get();
    }
}
