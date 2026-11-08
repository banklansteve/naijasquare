<template>
    <section class="w-full max-w-full min-w-0" aria-labelledby="helpable-needs">
        <div class="flex flex-wrap items-end justify-between gap-2">
            <div class="min-w-0">
                <h2 id="helpable-needs" class="font-heading text-xl font-bold tracking-tight text-ink">
                    Needs you could help with
                </h2>
                <p class="mt-1 text-sm text-ink-soft">
                    From your communities and nearby — ranked by how well they fit you.
                </p>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <Link
                href="/home"
                class="rounded-full border px-3.5 py-2 text-sm font-medium transition"
                :class="!categoryFilter
                    ? 'border-lagoon-700 bg-lagoon-800 text-white'
                    : 'border-line bg-white text-ink-soft hover:border-lagoon-300 hover:text-ink'"
                preserve-scroll
            >
                All
            </Link>
            <Link
                v-for="filter in categoryFilters"
                :key="filter.key"
                :href="`/home?category=${filter.key}`"
                class="rounded-full border px-3.5 py-2 text-sm font-medium transition"
                :class="categoryFilter === filter.key
                    ? 'border-lagoon-700 bg-lagoon-800 text-white'
                    : 'border-line bg-white text-ink-soft hover:border-lagoon-300 hover:text-ink'"
                preserve-scroll
            >
                {{ filter.label }}
            </Link>
        </div>

        <ul v-if="posts.length" class="mt-4 grid min-w-0 gap-3">
            <li
                v-for="post in posts"
                :key="post.id"
                class="min-w-0"
            >
                <HomeNeedCard :post="post" />
            </li>
        </ul>

        <p
            v-else
            class="mt-4 rounded-[1.35rem] border border-dashed border-line bg-white/70 px-5 py-8 text-center text-sm leading-6 text-ink-soft"
        >
            No open Needs match this view right now. Check back soon, or join another community nearby.
        </p>
    </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import HomeNeedCard, { type HomePostCard } from '@/components/home/HomeNeedCard.vue';

defineProps<{
    posts: HomePostCard[];
    categoryFilter: string | null;
    categoryFilters: Array<{ key: string; label: string }>;
}>();
</script>
