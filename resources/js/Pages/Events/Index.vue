<script setup>
import Icon from '../../Components/Icon.vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import {
    calculateDurationMinutes,
    formatLocalDate,
    formatLocalDayLabel,
    formatLocalTime,
} from '../../dateTime';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { cilDinner, cilNotes, cilWalk, cilWallet, cilWeightlifitng } from '@coreui/icons';
import { computed } from 'vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
    events: { type: Object, required: true },
});

const page = usePage();
const userTimezone = computed(() => page.props.app.userTimezone);
const eventTypes = computed(() => page.props.catalogs.eventTypes);

const typeIcons = {
    walk: cilWalk,
    meal: cilDinner,
    exercise: cilWeightlifitng,
    expense: cilWallet,
};

const eventTypeDescriptions = {
    expense: 'Record a purchase',
    meal: 'Log what you ate',
    walk: 'Track distance and time',
    exercise: 'Save a workout',
};

const groups = computed(() => groupEventsByLocalDay(props.events.data));

function capitalize(value) {
    return value ? value.charAt(0).toUpperCase() + value.slice(1) : '';
}

function formatTimelineEvent(event) {
    return {
        ...event,
        type: event.event_type,
        localTime: formatLocalTime(event.started_at, userTimezone.value),
        durationMinutes: calculateDurationMinutes(event.started_at, event.ended_at),
    };
}

function groupEventsByLocalDay(events) {
    const eventsByLocalDay = new Map();

    for (const event of events) {
        const localDate = formatLocalDate(event.started_at, userTimezone.value);
        const eventsForDay = eventsByLocalDay.get(localDate) ?? [];
        eventsForDay.push(formatTimelineEvent(event));
        eventsByLocalDay.set(localDate, eventsForDay);
    }

    return Array.from(eventsByLocalDay, ([date, eventsForDay]) => ({
        date,
        label: formatLocalDayLabel(date, userTimezone.value),
        events: eventsForDay,
    }));
}

function summarizeEvent(event) {
    const eventData = event.data ?? {};

    if (event.type.slug === 'expense') {
        const formattedAmount = (eventData.amount_cents / 100).toFixed(2);
        return [`Bs ${formattedAmount}`, eventData.category].filter(Boolean).join(' · ');
    }

    if (event.type.slug === 'meal') {
        return [capitalize(eventData.meal), eventData.description].filter(Boolean).join(' · ');
    }

    if (event.type.slug === 'walk') {
        return [
            eventData.distance_km ? `${eventData.distance_km} km` : null,
            event.durationMinutes ? `${event.durationMinutes} min` : null,
            eventData.steps ? `${Number(eventData.steps).toLocaleString()} steps` : null,
        ]
            .filter(Boolean)
            .join(' · ');
    }

    return [
        eventData.kind,
        eventData.intensity ? capitalize(eventData.intensity) : null,
        event.durationMinutes ? `${event.durationMinutes} min` : null,
    ]
        .filter(Boolean)
        .join(' · ');
}
</script>

<template>
    <Head title="Timeline" />

    <template v-if="groups.length">
        <section v-for="group in groups" :key="group.date" class="timeline-day">
            <h2 class="timeline-day-heading">{{ group.label }}</h2>
            <div class="timeline-list card list-group list-group-flush">
                <Link
                    v-for="event in group.events"
                    :key="event.id"
                    :href="`/events/${event.id}/edit`"
                    class="timeline-row list-group-item list-group-item-action"
                >
                    <span class="event-type-icon" :class="`event-type-${event.type.slug}`">
                        <Icon :icon="typeIcons[event.type.slug]" :size="24" />
                    </span>
                    <span class="timeline-row-content">
                        <span class="timeline-row-heading">
                            <strong>{{ event.type.name }}</strong>
                            <time :datetime="event.started_at">{{ event.localTime }}</time>
                        </span>
                        <span v-if="summarizeEvent(event)" class="timeline-summary">{{
                            summarizeEvent(event)
                        }}</span>
                        <span v-if="event.notes" class="timeline-notes">{{ event.notes }}</span>
                        <span v-if="event.attachments.length" class="timeline-photos">
                            <img
                                v-for="photo in event.attachments"
                                :key="photo.id"
                                :src="`/attachments/${photo.id}`"
                                alt=""
                                loading="lazy"
                            />
                        </span>
                    </span>
                </Link>
            </div>
        </section>

        <div v-if="events.next_page_url" class="text-center mt-4">
            <Link :href="events.next_page_url" class="btn btn-outline-primary"
                >Load older entries</Link
            >
        </div>
    </template>

    <section v-else class="timeline-empty card" aria-labelledby="empty-title">
        <div class="card-body p-4 p-sm-5">
            <span class="timeline-empty-icon" aria-hidden="true"
                ><Icon :icon="cilNotes" :size="34"
            /></span>
            <h2 id="empty-title" class="h4 mt-3 mb-1">Start your timeline</h2>
            <p class="text-body-secondary mb-4">
                Choose what happened. Most entries take just a few taps.
            </p>
            <div class="capture-type-grid">
                <Link
                    v-for="eventType in eventTypes"
                    :key="eventType.slug"
                    :href="`/events/create?type=${eventType.slug}`"
                    class="capture-type-button card text-decoration-none"
                >
                    <span class="event-type-icon" :class="`event-type-${eventType.slug}`">
                        <Icon :icon="typeIcons[eventType.slug]" :size="28" />
                    </span>
                    <span>
                        <strong>{{ eventType.name }}</strong>
                        <small>{{ eventTypeDescriptions[eventType.slug] }}</small>
                    </span>
                </Link>
            </div>
        </div>
    </section>
</template>
