<template>
    <header class="min-w-0">
        <div class="flex min-w-0 flex-wrap items-end justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs font-semibold tracking-[0.14em] text-lagoon-700 uppercase">
                    Home
                </p>
                <h1 class="mt-2 font-heading text-[1.85rem] font-bold tracking-tight text-balance text-ink sm:text-3xl lg:text-[2.35rem] lg:leading-[1.15]">
                    {{ greeting }}, {{ firstName }}.
                </h1>
                <p
                    v-if="city"
                    class="mt-2.5 inline-flex items-center gap-1.5 rounded-full border border-line/80 bg-white px-3 py-1 text-sm font-medium text-ink-soft shadow-card"
                >
                    <BaseIcon name="pin" class="size-3.5 shrink-0 text-lagoon-700" />
                    {{ city }}
                </p>
            </div>

            <div class="flex w-full shrink-0 items-center gap-2 sm:w-auto">
                <Link
                    :href="askHref"
                    class="inline-flex h-11 flex-1 items-center justify-center rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white transition hover:bg-lagoon-900 sm:flex-none"
                >
                    Ask for help
                </Link>
                <Link
                    :href="offerHref"
                    class="inline-flex h-11 flex-1 items-center justify-center rounded-full border border-line bg-white px-5 font-heading text-sm font-semibold text-ink transition hover:border-lagoon-300 hover:bg-lagoon-50 sm:flex-none"
                >
                    Offer help
                </Link>
            </div>
        </div>
    </header>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import BaseIcon from '@/components/BaseIcon.vue';

defineProps<{
    greeting: string;
    firstName: string;
    city: string | null;
    askHref: string;
    offerHref: string;
}>();
</script>
