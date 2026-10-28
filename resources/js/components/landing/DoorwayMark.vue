<template>
    <svg
        viewBox="0 0 168 92"
        class="h-20 w-full max-w-40"
        fill="none"
        stroke="currentColor"
        stroke-width="1.6"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
        focusable="false"
    >
        <template v-if="name === 'table'">
            <path d="M18 58h132" stroke-width="1.8" />
            <path d="M34 58l-6 22M134 58l6 22" />
            <path d="M46 28h36l3 22H43z" fill="currentColor" fill-opacity="0.08" />
            <path d="M54 36h18M52 42h22M54 48h14" />
            <circle cx="108" cy="40" r="7" fill="currentColor" fill-opacity="0.08" />
            <path d="M115 40h10M120 40v5M125 40v5" />
            <path d="M128 34l14 16" />
        </template>
        <template v-else-if="name === 'doorway'">
            <path d="M22 78V36l16-14 16 14v42" fill="currentColor" fill-opacity="0.06" />
            <path d="M22 78V36l16-14 16 14v42" />
            <path d="M32 78V48h12v30" />
            <path d="M70 78V30l20-16 20 16v48" fill="currentColor" fill-opacity="0.1" />
            <path d="M70 78V30l20-16 20 16v48" />
            <path d="M84 78V46h12v32" />
            <path d="M118 78V40l14-12 14 12v38" fill="currentColor" fill-opacity="0.06" />
            <path d="M118 78V40l14-12 14 12v38" />
            <path d="M128 78V52h8v26" />
            <path d="M14 78h140" />
        </template>
        <template v-else-if="name === 'meal'">
            <ellipse cx="84" cy="58" rx="58" ry="16" fill="currentColor" fill-opacity="0.08" />
            <ellipse cx="84" cy="58" rx="58" ry="16" />
            <path d="M42 50c3-12 16-12 18 0" fill="currentColor" fill-opacity="0.1" />
            <path d="M42 50c3-12 16-12 18 0" />
            <path d="M108 50c3-12 16-12 18 0" fill="currentColor" fill-opacity="0.1" />
            <path d="M108 50c3-12 16-12 18 0" />
            <ellipse cx="84" cy="48" rx="14" ry="6" fill="currentColor" fill-opacity="0.12" />
            <ellipse cx="84" cy="48" rx="14" ry="6" />
            <path d="M80 44c2 2 6 2 8 0" />
        </template>
        <template v-else>
            <path d="M28 62h36V34l8-6V28H28z" fill="currentColor" fill-opacity="0.1" />
            <path d="M28 62h36V34l8-6V28H28z" />
            <path d="M36 44h14M36 50h10M40 28v-6h12" />
            <path d="M96 30h40v28H96z" fill="currentColor" fill-opacity="0.08" />
            <path d="M96 30h40v28H96z" />
            <path d="M104 40h24M104 46h16M104 52h18" />
            <path d="M78 24c10 4 16 4 24 0" />
            <path d="M86 24v6M94 24v6" />
        </template>
    </svg>
</template>

<script setup lang="ts">
import type { DoorwayIllustration } from '@/types/landing';

defineProps<{
    name: DoorwayIllustration;
}>();
</script>
