<template>
    <section class="w-full max-w-full min-w-0" aria-labelledby="helpable-needs">
        <div class="flex flex-wrap items-end justify-between gap-2">
            <div class="min-w-0">
                <h2 id="helpable-needs" class="font-heading text-xl font-bold tracking-tight text-ink">
                    Needs you could help with
                </h2>
                <p class="mt-1 text-sm text-ink-soft">
                    From your communities — ranked by how well they fit you.
                </p>
            </div>
            <Link
                href="/communities"
                class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-lagoon-800"
            >
                Browse communities
                <BaseIcon name="arrow" class="size-3.5" />
            </Link>
        </div>

        <div class="mt-5 flex flex-wrap gap-2">
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
                v-for="filter in visibleFilters"
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
            <button
                v-if="hiddenFilterCount > 0"
                type="button"
                class="rounded-full border border-dashed border-lagoon-300 bg-white px-3.5 py-2 text-sm font-semibold text-lagoon-800 transition hover:border-lagoon-400"
                @click="showAllFilters = !showAllFilters"
            >
                {{ showAllFilters ? 'Fewer' : `More (${hiddenFilterCount})` }}
            </button>
        </div>

        <ul v-if="visiblePosts.length" class="mt-5 grid min-w-0 gap-3">
            <li
                v-for="post in visiblePosts"
                :key="post.id"
                class="min-w-0"
            >
                <HomeNeedCard :post="post" />
            </li>
        </ul>

        <div v-if="posts.length > PREVIEW_COUNT" class="mt-4">
            <button
                type="button"
                class="inline-flex h-10 w-full items-center justify-center rounded-full border border-line bg-white px-5 font-heading text-sm font-semibold text-ink transition hover:border-lagoon-300 hover:bg-lagoon-50 sm:w-auto"
                @click="expanded = !expanded"
            >
                {{ expanded ? 'Show fewer' : `Show more (${Math.min(posts.length, MAX_COUNT) - PREVIEW_COUNT})` }}
            </button>
        </div>

        <div
            v-else-if="posts.length === 0"
            class="mt-5 flex flex-col items-center rounded-[1.4rem] border border-dashed border-line bg-white px-5 py-12 text-center shadow-card"
        >
            <span class="grid size-12 place-items-center rounded-full bg-lagoon-50 text-lagoon-700 ring-1 ring-lagoon-100">
                <BaseIcon name="sprout" class="size-6" light />
            </span>
            <p class="mt-4 text-sm font-semibold text-ink">
                {{ categoryFilter ? 'Nothing in this category right now' : 'Nothing to help with right now' }}
            </p>
            <p class="mt-1 max-w-sm text-sm leading-6 text-ink-soft">
                {{ categoryFilter
                    ? 'Try another category, or clear the filter to see everything that fits you.'
                    : 'When someone nearby posts a Need that fits you, it will show up here.' }}
            </p>
            <Link
                v-if="categoryFilter"
                href="/home"
                class="mt-5 inline-flex h-10 items-center justify-center rounded-full border border-line bg-white px-5 font-heading text-sm font-semibold text-ink transition hover:border-lagoon-300 hover:bg-lagoon-50"
                preserve-scroll
            >
                Clear filter
            </Link>
            <Link
                v-else
                href="/communities"
                class="mt-5 inline-flex h-10 items-center justify-center rounded-full border border-line bg-white px-5 font-heading text-sm font-semibold text-ink transition hover:border-lagoon-300 hover:bg-lagoon-50"
            >
                Find communities
            </Link>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import BaseIcon from '@/components/BaseIcon.vue';
import HomeNeedCard, { type HomePostCard } from '@/components/home/HomeNeedCard.vue';

const FILTER_PREVIEW_COUNT = 6;
const PREVIEW_COUNT = 3;
const MAX_COUNT = 6;

const props = defineProps<{
    posts: HomePostCard[];
    categoryFilter: string | null;
    categoryFilters: Array<{ key: string; label: string }>;
}>();

const showAllFilters = ref(false);
const expanded = ref(false);

const previewFilters = computed(() => {
    const preview = props.categoryFilters.slice(0, FILTER_PREVIEW_COUNT);

    if (props.categoryFilter && ! preview.some((filter) => filter.key === props.categoryFilter)) {
        const active = props.categoryFilters.find((filter) => filter.key === props.categoryFilter);

        if (active) {
            preview.push(active);
        }
    }

    return preview;
});

const visibleFilters = computed(() => (showAllFilters.value ? props.categoryFilters : previewFilters.value));
const hiddenFilterCount = computed(() => props.categoryFilters.length - previewFilters.value.length);

const visiblePosts = computed(() => {
    const capped = props.posts.slice(0, MAX_COUNT);

    return expanded.value ? capped : capped.slice(0, PREVIEW_COUNT);
});
</script>
