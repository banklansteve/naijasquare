<template>
    <section class="w-full max-w-full min-w-0" aria-labelledby="my-posts">
        <div class="flex min-w-0 flex-wrap items-end justify-between gap-2">
            <h2 id="my-posts" class="font-heading text-xl font-bold tracking-tight text-ink">
                Your open Needs &amp; Offers
            </h2>
            <Link
                v-if="posts.length"
                :href="listHref"
                class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-lagoon-800"
            >
                View all
                <BaseIcon name="arrow" class="size-3.5" />
            </Link>
        </div>

        <ul
            v-if="visiblePosts.length"
            class="mt-4 grid min-w-0 grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4"
        >
            <li
                v-for="post in visiblePosts"
                :key="post.id"
                class="min-w-0"
            >
                <Link
                    :href="post.href"
                    class="flex h-full min-w-0 items-center gap-4 rounded-[1.35rem] border border-line/90 bg-white p-5 shadow-card transition duration-300 hover:shadow-lift focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lagoon-700 sm:p-6"
                >
                    <span class="grid aspect-square w-[30%] max-w-[5.75rem] min-w-[4.25rem] shrink-0 place-items-center rounded-2xl bg-lagoon-100 text-lagoon-800">
                        <BaseIcon :name="post.type === 'offer' ? 'users' : 'help'" class="size-7" light />
                    </span>
                    <span class="flex min-w-0 flex-1 flex-col">
                        <span class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-lagoon-100 px-2.5 py-0.5 text-xs font-semibold text-lagoon-800">
                                {{ post.category_label }}
                            </span>
                            <span class="rounded-full border border-line bg-paper px-2.5 py-0.5 text-xs font-medium text-ink-soft">
                                {{ post.status_label }}
                            </span>
                        </span>
                        <span class="mt-2.5 line-clamp-2 font-heading text-base font-bold tracking-tight text-ink">
                            {{ post.title }}
                        </span>
                        <span class="mt-1.5 line-clamp-2 text-sm leading-6 text-ink-soft">
                            {{ post.body }}
                        </span>
                        <span class="mt-2.5 text-xs font-medium text-ink-soft">
                            {{ post.community.name }}
                            <template v-if="post.location_label"> · {{ post.location_label }}</template>
                        </span>
                    </span>
                </Link>
            </li>
        </ul>

        <div
            v-else
            class="mt-4 flex flex-col rounded-[1.35rem] border border-dashed border-line bg-white px-5 py-8 shadow-card sm:px-6"
        >
            <div class="flex flex-1 flex-col items-center justify-center text-center">
                <span class="grid size-14 place-items-center rounded-full bg-lagoon-100 text-lagoon-700 ring-1 ring-lagoon-100">
                    <HomePostsEmptyArt class="max-w-[2.75rem] text-lagoon-600" />
                </span>
                <p class="mt-4 text-sm font-semibold text-ink">Nothing open yet</p>
                <p class="mt-1 max-w-xs text-sm leading-6 text-ink-soft">
                    Post a Need when you need help — or an Offer when you can give it.
                </p>
            </div>
            <Link
                :href="emptyHref"
                class="mt-6 inline-flex h-12 w-full items-center justify-between gap-2 rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white transition hover:bg-lagoon-900"
            >
                <span>Post your first Need</span>
                <BaseIcon name="arrow" class="size-4 shrink-0" />
            </Link>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import BaseIcon from '@/components/BaseIcon.vue';
import type { HomePostCard } from '@/components/home/HomeNeedCard.vue';
import HomePostsEmptyArt from '@/components/home/HomePostsEmptyArt.vue';

const props = withDefaults(defineProps<{
    posts: HomePostCard[];
    emptyHref: string;
    listHref?: string;
}>(), {
    listHref: '/communities',
});

const visiblePosts = computed(() => props.posts.slice(0, 2));
</script>
