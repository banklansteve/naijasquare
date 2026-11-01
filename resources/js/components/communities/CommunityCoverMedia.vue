<template>
    <div class="relative h-28 overflow-hidden sm:h-32" :class="tone.shell">
        <img
            v-if="coverUrl"
            :src="coverUrl"
            :alt="`${name} cover`"
            class="absolute inset-0 size-full object-cover"
        >
        <template v-else>
            <div class="absolute inset-0 bg-linear-to-br" :class="tone.gradient" />
            <div class="absolute inset-0 opacity-[0.22]" :class="tone.pattern">
                <svg class="size-full" viewBox="0 0 320 140" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                    <path fill="currentColor" :d="tone.path" />
                </svg>
            </div>
            <div class="absolute inset-0 flex items-end justify-end p-3">
                <span class="rounded-full bg-white/25 px-2.5 py-1 text-[10px] font-semibold tracking-wide text-white uppercase backdrop-blur-sm">
                    {{ tone.label }}
                </span>
            </div>
        </template>
        <slot />
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    name: string;
    coverUrl?: string | null;
    tagKeys?: string[] | null;
}>();

type Tone = {
    key: string;
    label: string;
    shell: string;
    gradient: string;
    pattern: string;
    path: string;
};

const tones: Record<string, Tone> = {
    housing: {
        key: 'housing',
        label: 'Housing',
        shell: 'bg-clay-100',
        gradient: 'from-clay-200 via-[#f0d9c8] to-honey-100',
        pattern: 'text-clay-800',
        path: 'M40 90h36v36H40Zm48-28h40v64H88Zm52 16h34v48h-34Zm46-24h38v72h-38ZM0 120h320v20H0Z',
    },
    healthcare: {
        key: 'healthcare',
        label: 'Healthcare',
        shell: 'bg-moss-100',
        gradient: 'from-moss-300 via-moss-100 to-lagoon-50',
        pattern: 'text-moss-800',
        path: 'M150 28h20v36h36v20h-36v36h-20v-36h-36V64h36Z',
    },
    faith: {
        key: 'faith',
        label: 'Faith',
        shell: 'bg-lagoon-100',
        gradient: 'from-lagoon-400 via-lagoon-200 to-honey-100',
        pattern: 'text-lagoon-900',
        path: 'M160 24c28 36 56 52 56 84a56 56 0 1 1-112 0c0-32 28-48 56-84Z',
    },
    professionals: {
        key: 'professionals',
        label: 'Professionals',
        shell: 'bg-lagoon-50',
        gradient: 'from-[#c5d5db] via-lagoon-100 to-white',
        pattern: 'text-lagoon-800',
        path: 'M60 100h48v28H60Zm72-40h56v68h-56Zm80-24h48v92h-48ZM0 118h320v22H0Z',
    },
    parents: {
        key: 'parents',
        label: 'Parents',
        shell: 'bg-honey-50',
        gradient: 'from-honey-200 via-moss-100 to-lagoon-50',
        pattern: 'text-moss-700',
        path: 'M110 78a18 18 0 1 1 0.1 0Zm50-10a22 22 0 1 1 0.1 0Zm50 10a18 18 0 1 1 0.1 0ZM70 120c20-28 50-40 90-40s70 12 90 40',
    },
    students: {
        key: 'students',
        label: 'Students',
        shell: 'bg-lagoon-100',
        gradient: 'from-lagoon-300 via-lagoon-100 to-moss-50',
        pattern: 'text-lagoon-900',
        path: 'M40 78 160 40l120 38-120 38Zm40 20v28c0 12 36 22 80 22s80-10 80-22v-28',
    },
    events: {
        key: 'events',
        label: 'Events',
        shell: 'bg-lagoon-50',
        gradient: 'from-lagoon-500 via-lagoon-200 to-honey-100',
        pattern: 'text-white',
        path: 'M70 48h180v16H70Zm0 28h180v52H70Zm24-40v16m132-16v16',
    },
    ethnic_cultural: {
        key: 'ethnic_cultural',
        label: 'Culture',
        shell: 'bg-moss-50',
        gradient: 'from-moss-400 via-lagoon-200 to-honey-100',
        pattern: 'text-moss-900',
        path: 'M0 96c28-18 52-18 80 0s52 18 80 0 52-18 80 0 52 18 80 0v44H0Z',
    },
    new_arrivals: {
        key: 'new_arrivals',
        label: 'New arrivals',
        shell: 'bg-lagoon-50',
        gradient: 'from-lagoon-300 via-[#d7ebe8] to-honey-50',
        pattern: 'text-lagoon-800',
        path: 'M48 100c24-40 56-60 112-60s88 20 112 60M100 78l28 18 44-34',
    },
    local_services: {
        key: 'local_services',
        label: 'Local',
        shell: 'bg-moss-50',
        gradient: 'from-moss-200 via-lagoon-100 to-white',
        pattern: 'text-moss-800',
        path: 'M160 34 250 108H70Z M118 108v24h84v-24',
    },
    women: {
        key: 'women',
        label: 'Women',
        shell: 'bg-clay-50',
        gradient: 'from-clay-200 via-honey-100 to-lagoon-50',
        pattern: 'text-clay-800',
        path: 'M160 36a22 22 0 1 1 0.1 0Zm0 34c-30 0-48 18-48 44h96c0-26-18-44-48-44Z',
    },
    men: {
        key: 'men',
        label: 'Men',
        shell: 'bg-lagoon-50',
        gradient: 'from-lagoon-400 via-lagoon-100 to-[#e8eef0]',
        pattern: 'text-lagoon-900',
        path: 'M160 36a22 22 0 1 1 0.1 0Zm-34 78 18-40h32l18 40m-50-40 34-28 34 28',
    },
    default: {
        key: 'default',
        label: 'Community',
        shell: 'bg-lagoon-50',
        gradient: 'from-lagoon-200 via-lagoon-100 to-lagoon-50',
        pattern: 'text-white',
        path: 'M0 96c28-18 52-18 80 0s52 18 80 0 52-18 80 0 52 18 80 0v44H0ZM40 70h18v26H40Zm28-18h22v44H68Zm30 10h16v34H98Zm70-16h20v50h-20Zm28 12h18v38h-18Z',
    },
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
