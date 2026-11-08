<template>
    <header class="min-w-0">
        <div class="flex min-w-0 flex-wrap items-end justify-between gap-4">
            <div class="min-w-0">
                <h1 class="font-heading text-[1.75rem] font-bold tracking-tight text-balance text-ink sm:text-3xl lg:text-[2.25rem] lg:leading-tight">
                    {{ greeting }}, {{ firstName }}.
                </h1>
                <p
                    v-if="city"
                    class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft"
                >
                    <BaseIcon name="pin" class="size-3.5 shrink-0 text-lagoon-700" />
                    {{ city }}
                </p>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <Link
                    :href="askHref"
                    class="inline-flex h-11 items-center justify-center rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white transition hover:bg-lagoon-900"
                >
                    Ask
                </Link>
                <Link
                    :href="offerHref"
                    class="inline-flex h-11 items-center justify-center rounded-full border border-line bg-white px-5 font-heading text-sm font-semibold text-ink transition hover:border-lagoon-300 hover:bg-lagoon-50"
                >
                    Offer
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
