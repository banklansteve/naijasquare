<template>
    <span
        class="grid shrink-0 place-items-center rounded-2xl font-heading font-bold tracking-tight text-white"
        :class="[sizeClass, toneClass]"
        aria-hidden="true"
    >
        {{ monogram.initials }}
    </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
    monogram: { initials: string; tone: string };
    size?: 'sm' | 'md';
}>(), {
    size: 'md',
});

const sizeClass = computed(() => (props.size === 'sm' ? 'size-11 text-sm' : 'size-12 text-base'));

const toneClass = computed(() => {
    switch (props.monogram.tone) {
        case 'moss':
            return 'bg-moss-700';
        case 'clay':
            return 'bg-clay-700';
        case 'honey':
            return 'bg-honey-200 text-lagoon-900';
        default:
            return 'bg-lagoon-800';
    }
});
</script>
