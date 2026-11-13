<template>
    <section class="w-full max-w-full min-w-0" aria-labelledby="my-posts">
        <div class="flex min-w-0 flex-wrap items-end justify-between gap-2">
            <div class="min-w-0">
                <h2 id="my-posts" class="font-heading text-xl font-bold tracking-tight text-ink">
                    Your open Needs &amp; Offers
                </h2>
                <p class="mt-1 text-sm text-ink-soft">
                    Things you’ve asked for — and help you’ve put on offer.
                </p>
            </div>
            <Link
                v-if="posts.length"
                :href="listHref"
                class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-lagoon-800 transition hover:text-lagoon-900"
            >
                View all
                <BaseIcon name="arrow" class="size-3.5" />
            </Link>
        </div>

        <ul
            v-if="visiblePosts.length"
            class="mt-5 grid min-w-0 grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4"
        >
            <li
                v-for="post in visiblePosts"
                :key="post.id"
                class="min-w-0"
            >
                <HomeNeedCard :post="post" />
            </li>
        </ul>

        <div
            v-else
            class="mt-5 flex flex-col rounded-[1.35rem] border border-dashed border-line/90 bg-white px-5 py-10 text-center shadow-card sm:px-8"
        >
            <span class="mx-auto grid size-14 place-items-center rounded-full bg-lagoon-50 text-lagoon-700 ring-1 ring-lagoon-100">
                <HomePostsEmptyArt class="max-w-[2.75rem] text-lagoon-600" />
            </span>
            <p class="mt-4 text-sm font-semibold text-ink">Nothing open yet</p>
            <p class="mt-1.5 mx-auto max-w-sm text-sm leading-6 text-ink-soft">
                Post a Need when you need help — or an Offer when you can give it.
            </p>
            <Link
                :href="emptyHref"
                class="mt-6 inline-flex h-11 items-center justify-center gap-2 self-center rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white transition hover:bg-lagoon-900"
            >
                Post your first Need
                <BaseIcon name="arrow" class="size-4 shrink-0" />
            </Link>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import BaseIcon from '@/components/BaseIcon.vue';
import HomeNeedCard, { type HomePostCard } from '@/components/home/HomeNeedCard.vue';
import HomePostsEmptyArt from '@/components/home/HomePostsEmptyArt.vue';

const props = withDefaults(defineProps<{
    posts: HomePostCard[];
    emptyHref: string;
    listHref?: string;
}>(), {
    listHref: '/communities',
});

const visiblePosts = computed(() => props.posts.slice(0, 4));
</script>
