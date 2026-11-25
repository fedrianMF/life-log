<script setup>
import Icon from '../../Components/Icon.vue';
import GuestLayout from '../../Layouts/GuestLayout.vue';
import { Head } from '@inertiajs/vue3';
import { cibGoogle } from '@coreui/icons/dist/esm/brand/cib-google.js';

defineOptions({ layout: GuestLayout });
</script>

<template>
    <Head title="Sign in" />

    <div class="text-center">
        <h1 class="h2 mb-2">Life Log</h1>
        <p class="text-body-secondary mb-4">Your private place for everyday moments.</p>

        <a
            href="/auth/google/redirect"
            class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2"
        >
            <Icon :icon="cibGoogle" :size="20" />
            Sign in with Google
        </a>
    </div>
</template>
