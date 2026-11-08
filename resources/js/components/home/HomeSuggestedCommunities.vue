<template>
    <section class="w-full max-w-full min-w-0" aria-labelledby="communities-for-you">
        <div class="flex min-w-0 flex-wrap items-end justify-between gap-3">
            <h2 id="communities-for-you" class="font-heading text-xl font-bold tracking-tight text-ink">
                Communities to explore
            </h2>
            <Link
                href="/communities"
                class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-lagoon-800"
            >
                Browse all
                <BaseIcon name="arrow" class="size-3.5" />
            </Link>
        </div>

        <ul
            v-if="communities.length"
            class="mt-4 grid w-full min-w-0 grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-3"
        >
            <li
                v-for="community in communities"
                :key="community.id"
                class="min-w-0"
            >
                <Link
                    :href="`/communities/${community.slug}`"
                    class="flex h-full min-w-0 items-start gap-4 rounded-[1.35rem] border border-line/90 bg-white p-4 shadow-card transition duration-300 hover:shadow-lift focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lagoon-700 sm:p-5"
                >
                    <HomeCommunityThumb
                        size="lg"
                        :name="community.name"
                        :cover-url="community.cover_url"
                        :tag-keys="community.tag_keys"
                    />
                    <span class="flex min-w-0 flex-1 flex-col py-0.5">
                        <span class="line-clamp-2 font-heading text-base font-bold tracking-tight text-ink">
                            {{ community.name }}
                        </span>
                        <span class="mt-1.5 line-clamp-2 grow text-sm leading-6 text-ink-soft">
                            {{ community.description }}
                        </span>
                        <span class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-ink-soft">
                            <BaseIcon name="users" class="size-3.5 shrink-0 text-lagoon-700" />
                            {{ membersLabel(community.members) }}
                        </span>
                    </span>
                </Link>
            </li>
        </ul>

        <div
            v-else
            class="mt-4 rounded-[1.35rem] border border-dashed border-line bg-white px-5 py-8 text-center"
        >
            <CommunitiesEmptyArt />
            <p class="mx-auto mt-5 max-w-sm text-sm leading-6 text-ink-soft">
                We'll show relevant communities here once they match your city and interests.
            </p>
            <Link
                href="/communities"
                class="mt-4 inline-flex h-11 items-center justify-center rounded-full border border-line bg-white px-5 font-heading text-sm font-semibold text-ink"
            >
                Browse communities
            </Link>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import BaseIcon from '@/components/BaseIcon.vue';
import CommunitiesEmptyArt from '@/components/home/CommunitiesEmptyArt.vue';
import HomeCommunityThumb from '@/components/home/HomeCommunityThumb.vue';

defineProps<{
    communities: Array<{
        id: number;
        slug: string;
        name: string;
        description: string;
        members: number;
        access_mode: string;
        cover_url: string | null;
        tag_keys: string[];
    }>;
}>();

function membersLabel(count: number): string {
    if (count >= 1000) {
        const thousands = count / 1000;

        return `${thousands % 1 === 0 ? thousands.toFixed(0) : thousands.toFixed(1)}k members`;
    }

    return `${count} ${count === 1 ? 'member' : 'members'}`;
}
</script>
