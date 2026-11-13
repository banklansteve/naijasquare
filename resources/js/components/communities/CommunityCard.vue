<template>
    <article
        class="group relative flex h-full flex-col overflow-hidden rounded-[1.4rem] border border-line/80 bg-white shadow-card transition duration-300 ease-out hover:-translate-y-0.5 hover:border-lagoon-200 hover:shadow-lift"
    >
        <div class="relative">
            <CommunityCoverMedia
                :name="community.name"
                :cover-url="community.cover_url"
                :tag-keys="community.tag_keys"
            />
            <span
                v-if="roleBadge"
                class="absolute top-3 left-3 inline-flex items-center gap-1 rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-semibold text-lagoon-800 shadow-sm backdrop-blur-sm"
            >
                <BaseIcon name="verified" bold class="size-3" />
                {{ roleBadge }}
            </span>
        </div>

        <div class="flex min-w-0 flex-1 flex-col p-5">
            <h3 class="font-heading text-base font-bold tracking-tight text-ink">
                <Link
                    :href="href"
                    class="transition-colors before:absolute before:inset-0 before:content-[''] group-hover:text-lagoon-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lagoon-700"
                >
                    {{ community.name }}
                </Link>
            </h3>
            <p class="mt-1.5 line-clamp-2 min-h-[2.75rem] text-sm leading-6 text-ink-soft">
                {{ community.description }}
            </p>

            <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-medium text-ink-soft">
                <span v-if="community.location_label" class="inline-flex items-center gap-1.5">
                    <BaseIcon name="pin" class="size-3.5 shrink-0 text-lagoon-600" />
                    {{ community.location_label }}
                </span>
                <span v-if="community.members != null" class="inline-flex items-center gap-1.5">
                    <BaseIcon name="users" class="size-3.5 shrink-0 text-lagoon-600" />
                    {{ community.members }} {{ community.members === 1 ? 'member' : 'members' }}
                </span>
            </div>

            <Link
                v-if="variant === 'discover'"
                :href="href"
                class="relative z-10 mt-5 inline-flex h-11 w-full items-center justify-center rounded-full bg-lagoon-800 font-heading text-sm font-semibold text-white transition hover:bg-lagoon-900"
            >
                {{ community.access_mode === 'open' ? 'View & join' : 'Request to join' }}
            </Link>
            <div
                v-else
                class="mt-5 flex items-center justify-between gap-2 border-t border-line/70 pt-4"
            >
                <span class="text-xs font-semibold text-lagoon-800">
                    {{ variant === 'run' ? 'Manage community' : 'Open community' }}
                </span>
                <BaseIcon name="arrow" class="size-3.5 text-lagoon-700 transition group-hover:translate-x-0.5" />
            </div>
        </div>
    </article>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import BaseIcon from '@/components/BaseIcon.vue';
import CommunityCoverMedia from '@/components/communities/CommunityCoverMedia.vue';

export type CommunityCardData = {
    id: number;
    slug: string;
    name: string;
    description: string;
    role?: string;
    members?: number | null;
    access_mode?: string;
    location_label: string | null;
    cover_url: string | null;
    tag_keys: string[];
};

const props = withDefaults(defineProps<{
    community: CommunityCardData;
    variant?: 'member' | 'run' | 'discover';
}>(), {
    variant: 'member',
});

const href = computed(() => `/communities/${props.community.slug}`);

const roleBadge = computed(() => {
    if (props.variant !== 'run') {
        return null;
    }

    return props.community.role === 'owner' ? 'Owner' : 'Admin';
});
</script>
