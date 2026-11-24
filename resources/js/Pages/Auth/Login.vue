<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Sign in" />

    <main class="min-vh-100 bg-light d-flex align-items-center py-5">
        <div class="container" style="max-width: 24rem">
            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    <h1 class="h3 mb-2">Life Log</h1>
                    <p class="text-body-secondary mb-4">Your private personal log.</p>

                    <a href="/auth/google/redirect" class="btn btn-primary w-100">
                        Sign in with Google
                    </a>
                </div>
            </div>
        </div>
    </main>
</template>
