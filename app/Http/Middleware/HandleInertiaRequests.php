<?php

namespace App\Http\Middleware;

use App\Services\EventTypeService;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Inertia\OnceProp;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function __construct(private readonly EventTypeService $eventTypeService) {}

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()?->only('name', 'email'),
            ],
            'app' => [
                'userTimezone' => config('app.user_timezone'),
            ],
        ];
    }

    /**
     * @return array<string, callable|OnceProp>
     */
    public function shareOnce(Request $request): array
    {
        return [
            ...parent::shareOnce($request),
            'catalogs' => fn (): array => [
                'eventTypes' => $this->eventTypeService->getAvailableEventTypes(),
            ],
        ];
    }
}
