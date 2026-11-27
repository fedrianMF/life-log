<?php

namespace App\Http\Requests;

use App\Enums\EventSource;
use App\Models\Event;
use App\Models\EventType;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class EventRequest extends FormRequest
{
    private const array EVENT_DATA_RULES = [
        'walk' => [
            'data.distance_km' => ['nullable', 'numeric', 'between:0,100'],
            'data.steps' => ['nullable', 'integer', 'between:0,200000'],
        ],
        'meal' => [
            'data.meal' => ['required', 'in:breakfast,lunch,dinner,snack'],
            'data.description' => ['nullable', 'string', 'max:500'],
        ],
        'exercise' => [
            'data.kind' => ['required', 'string', 'max:100'],
            'data.intensity' => ['nullable', 'in:low,medium,high'],
        ],
        'expense' => [
            'data.amount_cents' => ['required', 'integer', 'min:1'],
            'data.currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'data.category' => ['nullable', 'string', 'max:100'],
            'data.description' => ['nullable', 'string', 'max:255'],
        ],
    ];

    private bool $hasResolvedEventType = false;

    private ?EventType $resolvedEventType = null;

    public function authorize(): bool
    {
        $event = $this->routeEvent();

        return $event
            ? ($this->user()?->can('update', $event) ?? false)
            : ($this->user()?->can('create', Event::class) ?? false);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $eventType = $this->resolveSelectedEventType();

        $rules = [
            'event_type_id' => ['required', 'integer', Rule::exists('event_types', 'id')],
            'started_at' => ['required', 'date'],
            'ended_at' => [
                'nullable',
                'date',
                'after_or_equal:started_at',
                Rule::prohibitedIf(! $eventType?->has_duration),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
            'data' => ['nullable', 'array'],
            'photos' => ['nullable', 'array', 'max:4'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp,heic', 'max:10240'],
            ...$this->eventDataRules($eventType?->slug ?? ''),
        ];

        $event = $this->routeEvent();

        if (! $event) {
            return $rules;
        }

        return [
            ...$rules,
            'removed_attachment_ids' => ['nullable', 'array'],
            'removed_attachment_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('attachments', 'id')->where('event_id', $event->id),
            ],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        $event = $this->routeEvent();

        if (! $event) {
            return [];
        }

        return [
            function (Validator $validator) use ($event): void {
                if ($validator->errors()->hasAny(['photos', 'removed_attachment_ids'])) {
                    return;
                }

                $removedAttachmentIds = $this->input('removed_attachment_ids', []);
                $remainingPhotoCount = $event->attachments()->count() - count($removedAttachmentIds);
                $newPhotoCount = count($this->file('photos', []));

                if ($remainingPhotoCount + $newPhotoCount > 4) {
                    $validator->errors()->add('photos', 'An event may have at most 4 photos.');
                }
            },
        ];
    }

    /** @return array<string, mixed> */
    public function validatedEventAttributes(): array
    {
        $eventType = $this->resolveSelectedEventType() ?? EventType::findOrFail($this->integer('event_type_id'));
        $allowedDataKeys = collect(array_keys($this->eventDataRules($eventType->slug)))
            ->map(fn (string $ruleKey): string => str($ruleKey)->after('data.')->toString())
            ->all();

        return [
            'event_type_id' => $eventType->id,
            'started_at' => $this->convertToUtc($this->validated('started_at')),
            'ended_at' => $this->validated('ended_at')
                ? $this->convertToUtc($this->validated('ended_at'))
                : null,
            'notes' => $this->validated('notes'),
            'data' => Arr::only($this->validated('data', []), $allowedDataKeys),
            'source' => EventSource::Web,
        ];
    }

    /** @return array<int, UploadedFile> */
    public function uploadedPhotos(): array
    {
        return array_values(array_filter(
            (array) $this->file('photos', []),
            fn (mixed $photo): bool => $photo instanceof UploadedFile,
        ));
    }

    /** @return array<int, int> */
    public function removedAttachmentIds(): array
    {
        return array_map(
            static fn (mixed $attachmentId): int => (int) $attachmentId,
            $this->validated('removed_attachment_ids', []),
        );
    }

    private function routeEvent(): ?Event
    {
        $event = $this->route('event');

        return $event instanceof Event ? $event : null;
    }

    /** @return array<string, array<int, string>> */
    private function eventDataRules(string $eventTypeSlug): array
    {
        return self::EVENT_DATA_RULES[$eventTypeSlug] ?? [];
    }

    private function resolveSelectedEventType(): ?EventType
    {
        if (! $this->hasResolvedEventType) {
            $this->resolvedEventType = EventType::find($this->input('event_type_id'));
            $this->hasResolvedEventType = true;
        }

        return $this->resolvedEventType;
    }

    private function convertToUtc(string $localDateTime): CarbonImmutable
    {
        return CarbonImmutable::parse($localDateTime, config('app.user_timezone'))->utc();
    }
}
