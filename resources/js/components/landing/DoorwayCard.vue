<template>
    <article
        class="flex h-full flex-col rounded-2xl px-5 py-6 text-[var(--door-ink)] shadow-card md:px-6"
    >
        <DoorwayMark :name="card.illustration" class="text-lagoon-800" />
        <h3 class="mt-4 text-xl font-semibold tracking-tight">
            {{ card.title }}
        </h3>
        <p class="mt-2 text-base leading-relaxed text-[var(--door-muted)]">
            {{ card.body }}
        </p>
        <ul class="mt-5 flex flex-wrap gap-2">
            <li
                v-for="tag in card.tags"
                :key="tag"
                class="rounded-full bg-[var(--door-chip)] px-3 py-1 text-xs font-semibold tracking-wide text-[var(--door-chip-ink)]"
            >
                {{ tag }}
            </li>
        </ul>
    </article>
</template>

<script setup lang="ts">
import DoorwayMark from '@/components/landing/DoorwayMark.vue';
import type { DoorwayCard as DoorwayCardData } from '@/types/landing';

defineProps<{
    card: DoorwayCardData;
}>();
</script>
