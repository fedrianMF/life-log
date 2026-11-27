<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class EventService
{
    public function __construct(private readonly AttachmentService $attachmentService) {}

    /** @return LengthAwarePaginator<int, Event> */
    public function paginateEvents(?User $user = null): LengthAwarePaginator
    {
        return Event::query()
            ->select(['id', 'event_type_id', 'started_at', 'ended_at', 'notes', 'data'])
            ->with([
                'eventType:id,slug,name,has_duration',
                'attachments:id,event_id',
            ])
            ->when(
                $user,
                fn (Builder $eventsQuery, User $timelineUser): Builder => $eventsQuery
                    ->whereBelongsTo($timelineUser),
            )
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();
    }

    public function getEventForEditing(Event $event): Event
    {
        return Event::query()
            ->select(['id', 'event_type_id', 'started_at', 'ended_at', 'notes', 'data'])
            ->with('attachments:id,event_id')
            ->findOrFail($event->getKey());
    }

    /**
     * @param  array<string, mixed>  $eventAttributes
     * @param  array<int, UploadedFile>  $photos
     */
    public function createEvent(User $user, array $eventAttributes, array $photos): Event
    {
        $storedPhotoPaths = [];

        try {
            return DB::transaction(function () use ($user, $eventAttributes, $photos, &$storedPhotoPaths): Event {
                $event = $user->events()->create($eventAttributes);
                $this->attachmentService->storeEventAttachments(
                    $event,
                    $photos,
                    $storedPhotoPaths,
                );

                return $event;
            });
        } catch (Throwable $exception) {
            $this->attachmentService->deleteStoredFiles($storedPhotoPaths);

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $eventAttributes
     * @param  array<int, UploadedFile>  $photos
     * @param  array<int, int>  $removedAttachmentIds
     */
    public function updateEvent(
        Event $event,
        array $eventAttributes,
        array $photos,
        array $removedAttachmentIds,
    ): Event {
        $storedPhotoPaths = [];

        try {
            DB::transaction(function () use ($event, $eventAttributes, $photos, &$storedPhotoPaths): void {
                $event->update($eventAttributes);
                $this->attachmentService->storeEventAttachments(
                    $event,
                    $photos,
                    $storedPhotoPaths,
                );
            });
        } catch (Throwable $exception) {
            $this->attachmentService->deleteStoredFiles($storedPhotoPaths);

            throw $exception;
        }

        $this->attachmentService->deleteEventAttachments($event, $removedAttachmentIds);

        return $event->refresh();
    }

    public function deleteEvent(Event $event): void
    {
        $event->delete();
    }
}
