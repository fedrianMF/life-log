<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>

    <Head title="Entrar" />

    <main class="min-vh-100 bg-light d-flex align-items-center py-5">
        <div class="container" style="max-width: 24rem">
            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    <h1 class="h3 mb-2">Life Log</h1>
                    <p class="text-body-secondary mb-4">Registro personal privado.</p>

                    <a href="/auth/google/redirect" class="btn btn-primary w-100">
                        Entrar con Google
                    </a>
                </div>
            </div>
        </div>
    </main>
</template>
