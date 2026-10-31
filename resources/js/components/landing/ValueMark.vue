<template>
    <svg
        viewBox="0 0 128 88"
        class="h-8 w-12 sm:h-9 sm:w-14"
        fill="none"
        stroke="currentColor"
        stroke-width="2.2"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
        focusable="false"
    >
        <template v-if="name === 'match'">
            <path d="M16 72h96" />
            <path d="M22 72c0-22 18-34 42-34s42 12 42 34" fill="currentColor" fill-opacity="0.07" />
            <path d="M22 72c0-22 18-34 42-34s42 12 42 34" />
            <circle cx="40" cy="46" r="5" fill="currentColor" fill-opacity="0.12" />
            <circle cx="40" cy="46" r="5" />
            <path d="M32 68c1-8 14-8 16 0" />
            <circle cx="64" cy="40" r="5.5" fill="currentColor" fill-opacity="0.16" />
            <circle cx="64" cy="40" r="5.5" />
            <path d="M54 68c1-10 18-10 20 0" />
            <circle cx="88" cy="46" r="5" fill="currentColor" fill-opacity="0.12" />
            <circle cx="88" cy="46" r="5" />
            <path d="M80 68c1-8 14-8 16 0" />
        </template>
        <template v-else-if="name === 'help'">
            <path d="M18 16h58l18 18v42H18z" fill="currentColor" fill-opacity="0.08" />
            <path d="M18 16h58l18 18v42H18z" />
            <path d="M76 16v18h18" />
            <path d="M30 44h28M30 52h20M30 60h14" />
            <path d="M58 46h46v30H58z" fill="currentColor" fill-opacity="0.14" />
            <path d="M58 46h46v30H58z" />
            <path d="M70 61l7 7 14-15" />
        </template>
        <template v-else>
            <path d="M24 74V38L64 16l40 22v36" fill="currentColor" fill-opacity="0.08" />
            <path d="M24 74V38L64 16l40 22v36" />
            <path d="M12 74h104" />
            <path d="M36 50h10v10H36zM82 50h10v10H82z" fill="currentColor" fill-opacity="0.1" />
            <path d="M36 50h10v10H36zM82 50h10v10H82z" />
            <path d="M54 74V52h20v22" />
            <circle cx="64" cy="40" r="8" fill="currentColor" fill-opacity="0.14" />
            <circle cx="64" cy="40" r="8" />
            <path d="M60 40.2 63 43.2 68.5 37" />
        </template>
    </svg>
</template>

<script setup lang="ts">
import type { ValueIcon } from '@/types/landing';

defineProps<{
    name: ValueIcon;
}>();
</script>
