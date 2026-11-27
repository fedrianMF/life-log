<script setup>
import Icon from './Icon.vue';
import { formatDateTimeInput } from '../dateTime';
import { useForm } from '@inertiajs/vue3';
import { cilCamera, cilDinner, cilWalk, cilWallet, cilWeightlifitng } from '@coreui/icons';
import { computed, onBeforeUnmount, ref } from 'vue';

const props = defineProps({
    eventTypes: { type: Array, required: true },
    selectedType: { type: Object, default: null },
    event: { type: Object, default: null },
    userTimezone: { type: String, required: true },
});

const typeIcons = {
    walk: cilWalk,
    meal: cilDinner,
    exercise: cilWeightlifitng,
    expense: cilWallet,
};

const typeDescriptions = {
    expense: 'Amount and category',
    meal: 'What and when',
    walk: 'Distance and duration',
    exercise: 'Activity and intensity',
};

const activeEventType = ref(
    props.eventTypes.find((eventType) => eventType.id === props.event?.event_type_id) ??
        props.selectedType,
);
const expenseAmount = ref(
    props.event?.data?.amount_cents ? (props.event.data.amount_cents / 100).toFixed(2) : '',
);
const existingPhotos = ref(
    (props.event?.attachments ?? []).map((photo) => ({
        ...photo,
        url: `/attachments/${photo.id}`,
    })),
);
const removedAttachmentIds = ref([]);
const newPhotoPreviews = ref([]);
const photoInputElement = ref(null);
const isPreparingPhotos = ref(false);

const form = useForm({
    event_type_id: activeEventType.value?.id ?? '',
    started_at: formatDateTimeInput(props.event?.started_at ?? new Date(), props.userTimezone),
    ended_at: props.event?.ended_at
        ? formatDateTimeInput(props.event.ended_at, props.userTimezone)
        : '',
    notes: props.event?.notes ?? '',
    event_data: { ...(props.event?.data ?? {}) },
    photos: [],
    removed_attachment_ids: [],
});

const visibleExistingPhotos = computed(() =>
    existingPhotos.value.filter((photo) => !removedAttachmentIds.value.includes(photo.id)),
);
const availablePhotoSlots = computed(
    () => 4 - visibleExistingPhotos.value.length - form.photos.length,
);
const canChangeType = computed(() => !props.event && !props.selectedType);
const photoError = computed(() => {
    const errorKey = Object.keys(form.errors).find(
        (key) => key === 'photos' || key.startsWith('photos.'),
    );

    return errorKey ? form.errors[errorKey] : null;
});

function selectEventType(eventType) {
    activeEventType.value = eventType;
    form.event_type_id = eventType.id;
    form.ended_at = eventType.has_duration ? form.ended_at : '';
    form.clearErrors();
}

function validationErrorFor(field) {
    return form.errors[field];
}

async function resizePhoto(file) {
    const sourcePhotoUrl = URL.createObjectURL(file);

    try {
        const sourceImage = await new Promise((resolve, reject) => {
            const candidateImage = new Image();
            candidateImage.onload = () => resolve(candidateImage);
            candidateImage.onerror = () => reject(new Error('Photo decode failed.'));
            candidateImage.src = sourcePhotoUrl;
        });
        const resizeScale = Math.min(
            1,
            1600 / Math.max(sourceImage.naturalWidth, sourceImage.naturalHeight),
        );
        const resizeCanvas = document.createElement('canvas');
        resizeCanvas.width = Math.round(sourceImage.naturalWidth * resizeScale);
        resizeCanvas.height = Math.round(sourceImage.naturalHeight * resizeScale);
        resizeCanvas
            .getContext('2d')
            .drawImage(sourceImage, 0, 0, resizeCanvas.width, resizeCanvas.height);

        const resizedPhotoBlob = await new Promise((resolve, reject) => {
            resizeCanvas.toBlob(
                (result) => (result ? resolve(result) : reject(new Error('Photo resize failed.'))),
                'image/jpeg',
                0.8,
            );
        });

        return new File([resizedPhotoBlob], `${file.name.replace(/\.[^.]+$/, '')}.jpg`, {
            type: 'image/jpeg',
        });
    } finally {
        URL.revokeObjectURL(sourcePhotoUrl);
    }
}

async function prepareSelectedPhotos(changeEvent) {
    const selectedPhotoFiles = Array.from(changeEvent.target.files ?? []).slice(
        0,
        availablePhotoSlots.value,
    );
    isPreparingPhotos.value = true;

    try {
        for (const selectedPhotoFile of selectedPhotoFiles) {
            const resizedPhoto = await resizePhoto(selectedPhotoFile);
            form.photos.push(resizedPhoto);
            newPhotoPreviews.value.push(URL.createObjectURL(resizedPhoto));
        }
    } catch {
        form.setError('photos', 'One of the photos could not be prepared. Choose another image.');
    } finally {
        isPreparingPhotos.value = false;
        changeEvent.target.value = '';
    }
}

function removeNewPhoto(index) {
    URL.revokeObjectURL(newPhotoPreviews.value[index]);
    newPhotoPreviews.value.splice(index, 1);
    form.photos.splice(index, 1);
}

function removeExistingPhoto(id) {
    removedAttachmentIds.value.push(id);
    form.removed_attachment_ids = [...removedAttachmentIds.value];
}

function buildEventDataForSelectedType() {
    if (activeEventType.value.slug === 'expense') {
        return {
            amount_cents: Math.round(Number(expenseAmount.value) * 100),
            currency: 'BOB',
            category: form.event_data.category || null,
            description: form.event_data.description || null,
        };
    }

    if (activeEventType.value.slug === 'meal') {
        return {
            meal: form.event_data.meal,
            description: form.event_data.description || null,
        };
    }

    if (activeEventType.value.slug === 'walk') {
        return {
            distance_km: form.event_data.distance_km || null,
            steps: form.event_data.steps || null,
        };
    }

    return {
        kind: form.event_data.kind,
        intensity: form.event_data.intensity || null,
    };
}

function submitEvent() {
    form.transform((values) => {
        const { event_data, ...payload } = values;

        return {
            ...payload,
            ...(props.event ? { _method: 'put' } : {}),
            ended_at: activeEventType.value.has_duration ? values.ended_at || null : null,
            notes: values.notes || null,
            data: buildEventDataForSelectedType(),
        };
    }).post(props.event ? `/events/${props.event.id}` : '/events', {
        forceFormData: form.photos.length > 0,
        preserveScroll: true,
    });
}

onBeforeUnmount(() =>
    newPhotoPreviews.value.forEach((previewUrl) => URL.revokeObjectURL(previewUrl)),
);
</script>

<template>
    <div v-if="!activeEventType" class="capture-type-picker" aria-labelledby="type-heading">
        <div class="mb-4">
            <h2 id="type-heading" class="h4 mb-1">What are you logging?</h2>
            <p class="text-body-secondary mb-0">One tap, then only the essentials.</p>
        </div>

        <div class="capture-type-grid">
            <button
                v-for="eventType in eventTypes"
                :key="eventType.id"
                type="button"
                class="capture-type-button"
                @click="selectEventType(eventType)"
            >
                <span class="event-type-icon" :class="`event-type-${eventType.slug}`">
                    <Icon :icon="typeIcons[eventType.slug]" :size="30" />
                </span>
                <span>
                    <strong>{{ eventType.name }}</strong>
                    <small>{{ typeDescriptions[eventType.slug] }}</small>
                </span>
            </button>
        </div>
    </div>

    <form v-else class="event-form" @submit.prevent="submitEvent">
        <div class="event-form-heading">
            <span class="event-type-icon" :class="`event-type-${activeEventType.slug}`">
                <Icon :icon="typeIcons[activeEventType.slug]" :size="28" />
            </span>
            <div>
                <h2 class="h4 mb-0">{{ activeEventType.name }}</h2>
                <button
                    v-if="canChangeType"
                    type="button"
                    class="btn btn-link p-0 small"
                    @click="activeEventType = null"
                >
                    Change type
                </button>
            </div>
        </div>

        <div v-if="activeEventType.slug === 'expense'" class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <div class="input-group input-group-lg">
                <span class="input-group-text">Bs</span>
                <input
                    id="amount"
                    v-model="expenseAmount"
                    type="number"
                    inputmode="decimal"
                    min="0.01"
                    step="0.01"
                    class="form-control expense-amount"
                    :class="{ 'is-invalid': validationErrorFor('data.amount_cents') }"
                    placeholder="0.00"
                    autofocus
                    required
                />
                <div v-if="validationErrorFor('data.amount_cents')" class="invalid-feedback">
                    {{ validationErrorFor('data.amount_cents') }}
                </div>
            </div>
            <label for="expense-category" class="form-label mt-3">Category</label>
            <input
                id="expense-category"
                v-model="form.event_data.category"
                type="text"
                maxlength="100"
                class="form-control"
                :class="{ 'is-invalid': validationErrorFor('data.category') }"
                placeholder="Optional"
            />
            <div v-if="validationErrorFor('data.category')" class="invalid-feedback">
                {{ validationErrorFor('data.category') }}
            </div>
        </div>

        <template v-if="activeEventType.slug === 'meal'">
            <div class="mb-3">
                <label for="meal" class="form-label">Meal</label>
                <select
                    id="meal"
                    v-model="form.event_data.meal"
                    class="form-select"
                    :class="{ 'is-invalid': validationErrorFor('data.meal') }"
                    required
                >
                    <option disabled value="">Choose a meal</option>
                    <option value="breakfast">Breakfast</option>
                    <option value="lunch">Lunch</option>
                    <option value="dinner">Dinner</option>
                    <option value="snack">Snack</option>
                </select>
                <div v-if="validationErrorFor('data.meal')" class="invalid-feedback">
                    {{ validationErrorFor('data.meal') }}
                </div>
            </div>
            <div class="mb-3">
                <label for="meal-description" class="form-label">Description</label>
                <input
                    id="meal-description"
                    v-model="form.event_data.description"
                    type="text"
                    maxlength="500"
                    class="form-control"
                    :class="{ 'is-invalid': validationErrorFor('data.description') }"
                    placeholder="What did you eat?"
                />
                <div v-if="validationErrorFor('data.description')" class="invalid-feedback">
                    {{ validationErrorFor('data.description') }}
                </div>
            </div>
        </template>

        <template v-if="activeEventType.slug === 'walk'">
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label for="distance" class="form-label">Distance (km)</label>
                    <input
                        id="distance"
                        v-model="form.event_data.distance_km"
                        type="number"
                        inputmode="decimal"
                        min="0"
                        max="100"
                        step="0.1"
                        class="form-control"
                        :class="{ 'is-invalid': validationErrorFor('data.distance_km') }"
                    />
                    <div v-if="validationErrorFor('data.distance_km')" class="invalid-feedback">
                        {{ validationErrorFor('data.distance_km') }}
                    </div>
                </div>
                <div class="col-6">
                    <label for="steps" class="form-label">Steps</label>
                    <input
                        id="steps"
                        v-model="form.event_data.steps"
                        type="number"
                        inputmode="numeric"
                        min="0"
                        max="200000"
                        class="form-control"
                        :class="{ 'is-invalid': validationErrorFor('data.steps') }"
                    />
                    <div v-if="validationErrorFor('data.steps')" class="invalid-feedback">
                        {{ validationErrorFor('data.steps') }}
                    </div>
                </div>
            </div>
        </template>

        <template v-if="activeEventType.slug === 'exercise'">
            <div class="mb-3">
                <label for="exercise-kind" class="form-label">Activity</label>
                <input
                    id="exercise-kind"
                    v-model="form.event_data.kind"
                    type="text"
                    maxlength="100"
                    class="form-control"
                    :class="{ 'is-invalid': validationErrorFor('data.kind') }"
                    placeholder="Strength, cycling, yoga…"
                    required
                />
                <div v-if="validationErrorFor('data.kind')" class="invalid-feedback">
                    {{ validationErrorFor('data.kind') }}
                </div>
            </div>
            <div class="mb-3">
                <label for="intensity" class="form-label">Intensity</label>
                <select
                    id="intensity"
                    v-model="form.event_data.intensity"
                    class="form-select"
                    :class="{ 'is-invalid': validationErrorFor('data.intensity') }"
                >
                    <option value="">Not specified</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
                <div v-if="validationErrorFor('data.intensity')" class="invalid-feedback">
                    {{ validationErrorFor('data.intensity') }}
                </div>
            </div>
        </template>

        <div class="row g-3 mb-3">
            <div :class="activeEventType.has_duration ? 'col-6' : 'col-12'">
                <label for="started-at" class="form-label">Started</label>
                <input
                    id="started-at"
                    v-model="form.started_at"
                    type="datetime-local"
                    class="form-control"
                    :class="{ 'is-invalid': validationErrorFor('started_at') }"
                    required
                />
                <div v-if="validationErrorFor('started_at')" class="invalid-feedback">
                    {{ validationErrorFor('started_at') }}
                </div>
            </div>
            <div v-if="activeEventType.has_duration" class="col-6">
                <label for="ended-at" class="form-label">Ended</label>
                <input
                    id="ended-at"
                    v-model="form.ended_at"
                    type="datetime-local"
                    class="form-control"
                    :class="{ 'is-invalid': validationErrorFor('ended_at') }"
                />
                <div v-if="validationErrorFor('ended_at')" class="invalid-feedback">
                    {{ validationErrorFor('ended_at') }}
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea
                id="notes"
                v-model="form.notes"
                rows="2"
                maxlength="2000"
                class="form-control"
                :class="{ 'is-invalid': validationErrorFor('notes') }"
                placeholder="Optional"
            ></textarea>
            <div v-if="validationErrorFor('notes')" class="invalid-feedback">
                {{ validationErrorFor('notes') }}
            </div>
        </div>

        <div class="mb-4">
            <span class="form-label d-block">Photos</span>
            <div
                v-if="visibleExistingPhotos.length || newPhotoPreviews.length"
                class="photo-preview-grid mb-2"
            >
                <div v-for="photo in visibleExistingPhotos" :key="photo.id" class="photo-preview">
                    <img :src="photo.url" alt="Event attachment" />
                    <button
                        type="button"
                        class="photo-remove"
                        aria-label="Remove photo"
                        @click="removeExistingPhoto(photo.id)"
                    >
                        ×
                    </button>
                </div>
                <div
                    v-for="(previewUrl, index) in newPhotoPreviews"
                    :key="previewUrl"
                    class="photo-preview"
                >
                    <img :src="previewUrl" alt="New attachment preview" />
                    <button
                        type="button"
                        class="photo-remove"
                        aria-label="Remove photo"
                        @click="removeNewPhoto(index)"
                    >
                        ×
                    </button>
                </div>
            </div>
            <input
                ref="photoInputElement"
                type="file"
                accept="image/*"
                capture="environment"
                multiple
                class="visually-hidden"
                @change="prepareSelectedPhotos"
            />
            <button
                v-if="availablePhotoSlots > 0"
                type="button"
                class="btn btn-outline-secondary photo-button"
                @click="photoInputElement.click()"
            >
                <Icon :icon="cilCamera" :size="20" />
                Add photo
            </button>
            <div v-if="photoError" class="text-danger small mt-1">
                {{ photoError }}
            </div>
        </div>

        <div class="event-form-actions">
            <button
                type="submit"
                class="btn btn-primary btn-lg w-100"
                :disabled="form.processing || isPreparingPhotos"
            >
                {{
                    isPreparingPhotos
                        ? 'Preparing photos…'
                        : form.processing
                          ? 'Saving…'
                          : event
                            ? 'Save changes'
                            : 'Save entry'
                }}
            </button>
        </div>
    </form>
</template>
