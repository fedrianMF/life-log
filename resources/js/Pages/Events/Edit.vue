<script setup>
import EventForm from '../../Components/EventForm.vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
    event: { type: Object, required: true },
});

const page = usePage();
const eventTypes = computed(() => page.props.catalogs.eventTypes);
const userTimezone = computed(() => page.props.app.userTimezone);
const isConfirmingDelete = ref(false);

function deleteEvent() {
    router.delete(`/events/${props.event.id}`);
}
</script>

<template>
    <Head title="Edit entry" />

    <div class="event-form-shell">
        <div class="card event-form-card">
            <div class="card-body p-3 p-sm-4">
                <EventForm :event-types="eventTypes" :event="event" :user-timezone="userTimezone" />
            </div>
        </div>

        <section class="delete-entry card mt-4" aria-labelledby="delete-entry-title">
            <div class="card-body p-3 p-sm-4">
                <template v-if="!isConfirmingDelete">
                    <button
                        type="button"
                        class="btn btn-outline-danger"
                        @click="isConfirmingDelete = true"
                    >
                        Delete entry
                    </button>
                </template>
                <div v-else class="delete-confirmation">
                    <div>
                        <h2 id="delete-entry-title" class="h6 mb-1">Delete this entry?</h2>
                        <p class="small text-body-secondary mb-0">
                            Its photos will also be deleted.
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button
                            type="button"
                            class="btn btn-light"
                            @click="isConfirmingDelete = false"
                        >
                            Keep it
                        </button>
                        <button type="button" class="btn btn-danger" @click="deleteEvent">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
