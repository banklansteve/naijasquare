<template>
    <section
        class="w-full max-w-full min-w-0 rounded-[1.35rem] border border-line/80 bg-white p-4 shadow-card sm:p-5"
        :aria-labelledby="headingId"
    >
        <div class="flex min-w-0 flex-wrap items-end justify-between gap-2 px-0.5">
            <h2 :id="headingId" class="min-w-0 font-heading text-lg font-bold tracking-tight text-ink">
                {{ title }}
            </h2>
            <Link
                v-if="browseHref && (communities.length > 0 || emptyMessage)"
                :href="browseHref"
                class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-lagoon-800"
            >
                {{ communities.length ? browseLabel : 'Browse all' }}
                <BaseIcon name="arrow" class="size-3.5" />
            </Link>
        </div>

        <ul
            v-if="visibleCommunities.length"
            class="mt-4 space-y-2"
        >
            <li
                v-for="community in visibleCommunities"
                :key="community.id"
                class="flex min-w-0 items-center gap-3 rounded-[1.15rem] border border-line/70 bg-paper/60 p-3 transition hover:border-lagoon-200 hover:bg-lagoon-50/40"
            >
                <HomeMonogram
                    v-if="community.monogram"
                    :monogram="community.monogram"
                    size="sm"
                />
                <HomeCommunityThumb
                    v-else
                    size="sm"
                    :name="community.name"
                    :cover-url="community.cover_url"
                    :tag-keys="community.tag_keys"
                />

                <div class="min-w-0 flex-1">
                    <div class="flex min-w-0 items-center gap-2">
                        <Link
                            :href="community.href"
                            class="min-w-0 truncate font-heading text-sm font-bold text-ink hover:text-lagoon-900"
                        >
                            {{ community.name }}
                        </Link>
                        <span
                            v-if="community.role_label !== 'Member'"
                            class="shrink-0 rounded-full bg-lagoon-100 px-2 py-0.5 text-[11px] font-semibold text-lagoon-800"
                        >
                            {{ community.role_label }}
                        </span>
                    </div>
                    <p class="mt-0.5 truncate text-xs text-ink-soft">
                        {{ community.summary }}
                    </p>
                </div>

                <button
                    v-if="pinnable && community.pin_href"
                    type="button"
                    class="inline-flex size-9 shrink-0 items-center justify-center rounded-full transition"
                    :class="community.pinned
                        ? 'bg-lagoon-100 text-lagoon-800'
                        : 'text-ink-soft hover:bg-white hover:text-lagoon-800'"
                    :aria-label="community.pinned ? 'Unpin community' : 'Pin community'"
                    :disabled="pinningId === community.id"
                    @click="togglePin(community)"
                >
                    <BaseIcon name="bookmark" class="size-4" :bold="community.pinned" />
                </button>
            </li>
        </ul>

        <div
            v-else-if="emptyMessage"
            class="mt-4 flex flex-col items-center justify-center rounded-[1.15rem] border border-dashed border-line bg-paper/50 px-5 py-7 text-center"
        >
            <p class="text-sm leading-6 text-ink-soft">{{ emptyMessage }}</p>
            <Link
                v-if="emptyHref"
                :href="emptyHref"
                class="mt-4 inline-flex h-10 items-center justify-center rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white"
            >
                {{ emptyCta }}
            </Link>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import BaseIcon from '@/components/BaseIcon.vue';
import HomeCommunityThumb from '@/components/home/HomeCommunityThumb.vue';
import HomeMonogram from '@/components/home/HomeMonogram.vue';

export type MembershipCommunityCard = {
    id: number;
    slug: string;
    name: string;
    location_label: string | null;
    cover_url: string | null;
    tag_keys: string[];
    monogram?: { initials: string; tone: string };
    role: string;
    role_label: string;
    pinned?: boolean;
    open_needs_count: number;
    pending_join_requests: number;
    summary: string;
    href: string;
    post_href: string;
    settings_href: string | null;
    pin_href?: string;
};

const props = withDefaults(defineProps<{
    title: string;
    headingId: string;
    communities: MembershipCommunityCard[];
    browseHref?: string | null;
    browseLabel?: string;
    emptyMessage?: string | null;
    emptyHref?: string | null;
    emptyCta?: string;
    limit?: number;
    pinnable?: boolean;
}>(), {
    browseHref: '/communities',
    browseLabel: 'View all',
    emptyMessage: null,
    emptyHref: '/communities',
    emptyCta: 'Find communities',
    limit: 3,
    pinnable: false,
});

const pinningId = ref<number | null>(null);

const visibleCommunities = computed(() => props.communities.slice(0, props.limit));

function togglePin(community: MembershipCommunityCard): void {
    if (! community.pin_href) {
        return;
    }

    pinningId.value = community.id;
    router.post(community.pin_href, {}, {
        preserveScroll: true,
        onFinish: () => {
            pinningId.value = null;
        },
    });
}
</script>
