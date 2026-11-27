<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, Event $event): bool
    {
        return $user->is($event->user);
    }

    public function update(User $user, Event $event): bool
    {
        return $user->is($event->user);
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->is($event->user);
    }
}
