<template>
    <div
        class="relative shrink-0 overflow-hidden rounded-2xl"
        :class="[layoutClass, tone.shell]"
    >
        <img
            v-if="coverUrl"
            :src="coverUrl"
            :alt="`${name} cover`"
            class="absolute inset-0 size-full object-cover"
        >
        <template v-else>
            <div class="absolute inset-0 bg-linear-to-br" :class="tone.gradient" />
            <div class="absolute inset-0 grid place-items-center text-white/90">
                <BaseIcon name="place-community" :class="iconClass" />
            </div>
        </template>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

import BaseIcon from '@/components/BaseIcon.vue';

const props = withDefaults(defineProps<{
    name: string;
    coverUrl?: string | null;
    tagKeys?: string[] | null;
    size?: 'sm' | 'md' | 'lg' | 'card';
}>(), {
    coverUrl: null,
    tagKeys: null,
    size: 'md',
});

const layoutClass = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'size-14';
        case 'lg':
            return 'size-[4.75rem] sm:size-24';
        case 'card':
            // ~1/3 of card content width, square — not stretched tall
            return 'aspect-square w-[31%] max-w-[6.75rem] min-w-[4.75rem]';
        default:
            return 'size-16 sm:size-[4.5rem]';
    }
});

const iconClass = computed(() => (props.size === 'lg' || props.size === 'card' ? 'size-7 opacity-90' : 'size-5 opacity-90'));

const tones: Record<string, { shell: string; gradient: string }> = {
    housing: { shell: 'bg-clay-100', gradient: 'from-clay-200 to-honey-100' },
    healthcare: { shell: 'bg-moss-100', gradient: 'from-moss-300 to-lagoon-50' },
    professionals: { shell: 'bg-lagoon-50', gradient: 'from-lagoon-300 to-lagoon-100' },
    students: { shell: 'bg-lagoon-100', gradient: 'from-lagoon-400 to-moss-50' },
    new_arrivals: { shell: 'bg-lagoon-50', gradient: 'from-lagoon-300 to-honey-50' },
    default: { shell: 'bg-lagoon-50', gradient: 'from-lagoon-200 to-lagoon-100' },
};

const tone = computed(() => {
    const keys = props.tagKeys ?? [];
    for (const key of keys) {
        if (tones[key]) {
            return tones[key];
        }
    }

    return tones.default;
});
</script>
