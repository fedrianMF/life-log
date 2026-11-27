<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventRequest;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function __construct(private readonly EventService $eventService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Events/Index', [
            'events' => $this->eventService->paginateEvents($request->user()),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Events/Create', [
            'selectedTypeSlug' => $request->string('type')->toString(),
        ]);
    }

    public function store(EventRequest $request): RedirectResponse
    {
        $this->eventService->createEvent(
            $request->user(),
            $request->validatedEventAttributes(),
            $request->uploadedPhotos(),
        );

        return to_route('home');
    }

    public function edit(Event $event): Response
    {
        Gate::authorize('update', $event);

        return Inertia::render('Events/Edit', [
            'event' => $this->eventService->getEventForEditing($event),
        ]);
    }

    public function update(EventRequest $request, Event $event): RedirectResponse
    {
        $this->eventService->updateEvent(
            $event,
            $request->validatedEventAttributes(),
            $request->uploadedPhotos(),
            $request->removedAttachmentIds(),
        );

        return to_route('home');
    }

    public function destroy(Event $event): RedirectResponse
    {
        Gate::authorize('delete', $event);
        $this->eventService->deleteEvent($event);

        return to_route('home');
    }
}
