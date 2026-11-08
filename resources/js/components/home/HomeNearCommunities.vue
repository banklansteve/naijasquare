<template>
    <section class="w-full max-w-full min-w-0" aria-labelledby="near-communities">
        <div class="flex min-w-0 items-end justify-between gap-2">
            <h2 id="near-communities" class="font-heading text-lg font-bold tracking-tight text-ink">
                Communities near you
            </h2>
            <Link
                href="/communities"
                class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-lagoon-800"
            >
                Browse
                <BaseIcon name="arrow" class="size-3.5" />
            </Link>
        </div>

        <ul v-if="communities.length" class="mt-4 space-y-2.5">
            <li
                v-for="community in communities"
                :key="community.id"
            >
                <Link
                    :href="community.join_href"
                    class="flex min-w-0 items-center gap-3 rounded-[1.15rem] border border-line/90 bg-white p-3.5 shadow-card transition hover:shadow-lift"
                >
                    <HomeMonogram :monogram="community.monogram" size="sm" />
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-heading text-sm font-bold text-ink">
                            {{ community.name }}
                        </span>
                        <span class="mt-0.5 block truncate text-xs text-ink-soft">
                            {{ community.members }}
                            {{ community.members === 1 ? 'member' : 'members' }}
                        </span>
                    </span>
                </Link>
            </li>
        </ul>

        <div
            v-else-if="empty"
            class="mt-4 rounded-[1.15rem] border border-dashed border-line bg-white px-4 py-6 text-center shadow-card"
        >
            <CommunitiesEmptyArt class="mx-auto max-w-[9rem] text-lagoon-500" />
            <p class="mt-3 text-sm font-semibold text-ink">No nearby communities yet</p>
            <p class="mt-1 text-sm leading-6 text-ink-soft">
                Be the first — start one for your city or circle.
            </p>
            <Link
                href="/communities/create"
                class="mt-4 inline-flex h-10 items-center justify-center rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white"
            >
                Start the first one
            </Link>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import BaseIcon from '@/components/BaseIcon.vue';
import CommunitiesEmptyArt from '@/components/home/CommunitiesEmptyArt.vue';
import HomeMonogram from '@/components/home/HomeMonogram.vue';

defineProps<{
    communities: Array<{
        id: number;
        slug: string;
        name: string;
        members: number;
        monogram: { initials: string; tone: string };
        join_href: string;
    }>;
    empty: boolean;
}>();
</script>
