<template>
    <header>
        <h1 class="font-heading text-[1.75rem] font-bold tracking-tight text-balance text-ink sm:text-3xl lg:text-[2.25rem] lg:leading-tight">
            {{ greeting }}, {{ firstName }}.
        </h1>
        <div class="mt-3 flex flex-wrap items-center gap-2.5">
            <span
                v-if="city"
                class="inline-flex items-center gap-1.5 rounded-full border border-line bg-white px-3 py-1 text-xs font-semibold text-ink-soft shadow-sm"
            >
                <BaseIcon name="pin" class="size-3.5 shrink-0 text-lagoon-700" />
                {{ city }}
            </span>
            <p
                v-if="unansweredNeedsCount > 0"
                class="text-sm font-medium text-ink-soft"
            >
                You have {{ unansweredNeedsCount }}
                unanswered {{ unansweredNeedsCount === 1 ? 'Need' : 'Needs' }} in your communities
            </p>
        </div>
    </header>
</template>

<script setup lang="ts">
import BaseIcon from '@/components/BaseIcon.vue';

defineProps<{
    greeting: string;
    firstName: string;
    city: string | null;
    unansweredNeedsCount: number;
}>();
</script>
