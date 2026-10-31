<template>
    <article
        class="relative rounded-sm bg-[#fffdf8] px-4 pt-4 pb-3 shadow-card ring-1 ring-[#e6dfd2] lg:px-5 lg:pt-5 lg:pb-4"
        :class="tilt"
    >
        <span
            class="absolute -top-1.5 left-1/2 size-3.5 -translate-x-1/2 rounded-full bg-lagoon-800 shadow-[0_2px_0_rgb(20_66_72_/_0.25)] ring-4 ring-[#fffdf8]"
            aria-hidden="true"
        />
        <header class="flex items-start justify-between gap-3">
            <p class="text-[11px] font-semibold tracking-[0.14em] text-lagoon-800 uppercase">
                {{ card.category }}
                <span class="text-ink-soft" aria-hidden="true"> · </span>
                {{ card.place }}
            </p>
            <span class="shrink-0 rounded-full bg-lagoon-50 px-2 py-0.5 text-[10px] font-semibold tracking-[0.12em] text-lagoon-800 uppercase">
                {{ card.kind }}
            </span>
        </header>
        <p class="mt-2 text-[15px] font-normal leading-snug text-ink lg:mt-3 lg:text-[16px]">
            “{{ card.line }}”
        </p>
        <p class="mt-4 hidden border-t border-[#ebe4d6] pt-3 text-xs font-medium tracking-wide text-ink-soft lg:block">
            {{ card.detail }}
        </p>
    </article>
</template>

<script setup lang="ts">
import type { BoardCard } from '@/types/landing';

defineProps<{
    card: BoardCard;
    tilt?: string;
}>();
</script>
