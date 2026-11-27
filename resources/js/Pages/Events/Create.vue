<script setup>
import EventForm from '../../Components/EventForm.vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
    selectedTypeSlug: { type: String, default: '' },
});

const page = usePage();
const eventTypes = computed(() => page.props.catalogs.eventTypes);
const userTimezone = computed(() => page.props.app.userTimezone);
const selectedType = computed(
    () => eventTypes.value.find((eventType) => eventType.slug === props.selectedTypeSlug) ?? null,
);
</script>

<template>
    <Head title="Capture" />

    <div class="event-form-shell">
        <div class="card event-form-card">
            <div class="card-body p-3 p-sm-4">
                <EventForm
                    :event-types="eventTypes"
                    :selected-type="selectedType"
                    :user-timezone="userTimezone"
                />
            </div>
        </div>
    </div>
</template>
