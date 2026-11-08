<template>
    <section
        class="relative w-full max-w-full overflow-hidden rounded-[1.35rem] border border-line/90 bg-white shadow-card"
        aria-labelledby="post-hero"
    >
        <div class="grid min-w-0 gap-5 p-5 sm:grid-cols-[1fr_auto] sm:items-center sm:gap-8 sm:p-7">
            <div class="min-w-0">
                <h2 id="post-hero" class="font-heading text-xl font-bold tracking-tight text-ink sm:text-2xl">
                    Need a hand — or lending one?
                </h2>
                <p class="mt-2 max-w-md text-sm leading-6 text-ink-soft">
                    Post a Need when something’s stuck. Post an Offer when you can help someone nearby.
                </p>
                <div class="mt-5 flex flex-wrap gap-2.5">
                    <Link
                        :href="askHref"
                        class="inline-flex h-11 items-center justify-center rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white transition hover:bg-lagoon-900"
                    >
                        Post a Need
                    </Link>
                    <Link
                        :href="offerHref"
                        class="inline-flex h-11 items-center justify-center rounded-full border border-line bg-paper px-5 font-heading text-sm font-semibold text-ink transition hover:border-lagoon-300 hover:bg-lagoon-50"
                    >
                        Post an Offer
                    </Link>
                </div>
            </div>

            <HomePostsEmptyArt class="mx-auto max-w-[8.5rem] text-lagoon-600 sm:mx-0 sm:max-w-[10rem]" />
        </div>
    </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import HomePostsEmptyArt from '@/components/home/HomePostsEmptyArt.vue';

defineProps<{
    askHref: string;
    offerHref: string;
}>();
</script>
