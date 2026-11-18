<script setup>
import { Head } from '@inertiajs/vue3';

const plannedEntries = [
    { label: 'Gasto', icon: '💰' },
    { label: 'Caminata', icon: '🚶' },
    { label: 'Pensamiento', icon: '💭' },
    { label: 'Estado diario', icon: '🙂' },
];
</script>

<template>
    <Head title="Inicio" />

    <main class="min-vh-100 bg-light py-5">
        <div class="container">
            <header class="mb-5">
                <span class="badge bg-primary mb-3">Esqueleto inicial</span>
                <h1 class="display-5 fw-semibold">Life Log</h1>
                <p class="lead text-body-secondary mb-0">
                    Registro personal rápido, privado y preparado para evolucionar.
                </p>
            </header>

            <section aria-labelledby="planned-entries-title">
                <h2 id="planned-entries-title" class="h4 mb-3">
                    Registros previstos
                </h2>

                <div class="row g-3">
                    <div
                        v-for="entry in plannedEntries"
                        :key="entry.label"
                        class="col-12 col-sm-6 col-lg-3"
                    >
                        <article class="card h-100 shadow-sm">
                            <div class="card-body">
                                <span class="fs-2" aria-hidden="true">{{ entry.icon }}</span>
                                <h3 class="h6 mt-3 mb-0">{{ entry.label }}</h3>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>
