<script setup>
import Icon from '../Components/Icon.vue';
import AppLayout from '../Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { cilMoodGood } from '@coreui/icons/dist/esm/free/cil-mood-good.js';
import { cilNotes } from '@coreui/icons/dist/esm/free/cil-notes.js';
import { cilWalk } from '@coreui/icons/dist/esm/free/cil-walk.js';
import { cilWallet } from '@coreui/icons/dist/esm/free/cil-wallet.js';

defineOptions({ layout: AppLayout });

const plannedEntries = [
    { label: 'Expense', description: 'Record a purchase', icon: cilWallet },
    { label: 'Walk', description: 'Log time and distance', icon: cilWalk },
    { label: 'Thought', description: 'Save what is on your mind', icon: cilNotes },
    { label: 'Daily mood', description: 'Check in with yourself', icon: cilMoodGood },
];
</script>

<template>
    <Head title="Timeline" />

    <section aria-labelledby="capture-title">
        <div class="mb-3">
            <h2 id="capture-title" class="h4 mb-1">What would you like to log?</h2>
            <p class="text-body-secondary mb-0">Choose an entry type to get started.</p>
        </div>

        <div class="row g-3">
            <div
                v-for="entry in plannedEntries"
                :key="entry.label"
                class="col-12 col-sm-6 col-xl-3"
            >
                <a href="#" class="entry-card card h-100 text-decoration-none">
                    <div class="card-body d-flex align-items-center gap-3 p-3 p-lg-4">
                        <span class="entry-card-icon" aria-hidden="true">
                            <Icon :icon="entry.icon" :size="28" />
                        </span>
                        <span>
                            <span class="d-block fw-semibold text-body">{{ entry.label }}</span>
                            <span class="small text-body-secondary">{{ entry.description }}</span>
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <section class="timeline-empty text-center" aria-labelledby="empty-timeline-title">
        <span class="timeline-empty-icon" aria-hidden="true">
            <Icon :icon="cilNotes" :size="34" />
        </span>
        <h2 id="empty-timeline-title" class="h5 mt-3 mb-2">No entries yet</h2>
        <p class="text-body-secondary mb-0">
            Your expenses, walks, thoughts, and daily moods will appear here.
        </p>
    </section>
</template>
