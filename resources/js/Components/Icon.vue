<script setup>
defineProps({
    icon: {
        type: Array,
        required: true,
    },
    size: {
        type: Number,
        default: 24,
    },
});
</script>

<template>
    <svg
        class="icon"
        :width="size"
        :height="size"
        :viewBox="`0 0 ${icon[0]}`"
        aria-hidden="true"
        focusable="false"
        v-html="icon[1]"
    ></svg>
</template>
