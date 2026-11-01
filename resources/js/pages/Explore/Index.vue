<template>
    <Head title="Explore communities">
        <meta head-key="description" name="description" :content="description" />
    </Head>

    <PublicShell>
        <main class="mx-auto w-full max-w-5xl px-5 pt-8 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-12">
            <header class="pb-10">
                <h1 class="font-heading text-3xl font-bold tracking-tight text-gray-800 sm:text-[2.5rem]">
                    Explore communities
                </h1>
                <p class="mt-3 max-w-2xl text-base text-gray-600">
                    Find groups by city and interest. Start where you live, then browse topics like professionals, housing, and new arrivals.
                </p>
            </header>

            <section aria-labelledby="places-heading">
                <h2 id="places-heading" class="text-xl font-semibold tracking-wide text-gray-700">By place</h2>
                <ul v-if="places.length" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="place in places" :key="place.slug">
                        <Link
                            :href="`/explore/${place.slug}`"
                            class="flex items-center justify-between rounded-[1.25rem] border border-gray-200/80 bg-white px-4 py-4 transition hover:border-lagoon-300"
                        >
                            <span class="font-heading text-base font-bold text-gray-800">{{ place.label }}</span>
                            <span class="text-xs font-medium text-ink-soft">{{ place.count }}</span>
                        </Link>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-gray-500">No public communities listed yet.</p>
            </section>

            <section class="mt-12" aria-labelledby="tags-heading">
                <h2 id="tags-heading" class="text-xl font-semibold tracking-wide text-gray-700">By interest</h2>
                <ul v-if="tags.length" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="tag in tags" :key="tag.key">
                        <Link
                            :href="`/explore/tag/${tag.key}`"
                            class="flex items-center justify-between rounded-[1.25rem] border border-gray-200/80 bg-white px-4 py-4 transition hover:border-lagoon-300"
                        >
                            <span class="font-heading text-base font-bold text-gray-800">{{ tag.label }}</span>
                            <span class="text-xs font-medium text-ink-soft">{{ tag.count }}</span>
                        </Link>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-gray-500">No tagged communities listed yet.</p>
            </section>
        </main>
    </PublicShell>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import PublicShell from '@/components/PublicShell.vue';

defineProps<{
    places: Array<{ slug: string; label: string; count: number }>;
    tags: Array<{ key: string; label: string; count: number }>;
}>();

const description = 'Find Nigerian diaspora communities by city and interest across the UK. Join local groups for housing, work, and everyday help.';
</script>
