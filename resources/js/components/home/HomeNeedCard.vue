<template>
    <Link
        :href="post.href"
        class="group relative block overflow-hidden rounded-[1.35rem] border border-line/80 bg-white shadow-card transition duration-300 ease-out hover:-translate-y-0.5 hover:border-lagoon-200 hover:shadow-lift focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lagoon-700"
    >
        <span
            class="absolute inset-y-0 left-0 w-[3px]"
            :class="accent.rail"
            aria-hidden="true"
        />

        <div class="px-5 py-4 pl-6 sm:px-6 sm:py-5 sm:pl-7">
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold tracking-wide"
                    :class="accent.pill"
                >
                    <span class="size-1.5 rounded-full" :class="accent.dot" aria-hidden="true" />
                    {{ post.type_label }}
                </span>
                <span class="min-w-0 truncate text-xs font-medium text-ink-soft">
                    {{ post.category_label }}
                </span>

                <span
                    v-if="post.status === 'resolved'"
                    class="ml-auto inline-flex shrink-0 items-center gap-1 rounded-full bg-moss-100 px-2.5 py-1 text-[11px] font-semibold text-moss-800"
                >
                    <BaseIcon name="check" bold class="size-3" />
                    Resolved
                </span>
                <span
                    v-else-if="isUrgent"
                    class="ml-auto inline-flex shrink-0 items-center gap-1 rounded-full bg-clay-100 px-2.5 py-1 text-[11px] font-semibold text-clay-800"
                >
                    <BaseIcon name="clock" bold class="size-3" />
                    {{ post.urgency_label }}
                </span>
            </div>

            <h3 class="mt-3 line-clamp-2 font-heading text-[1.05rem] font-bold tracking-tight text-ink transition-colors group-hover:text-lagoon-900 sm:text-base">
                {{ post.title }}
            </h3>
            <p v-if="post.body" class="mt-1.5 line-clamp-2 text-sm leading-6 text-ink-soft">
                {{ post.body }}
            </p>

            <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs font-medium text-ink-soft">
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <BaseIcon name="place-community" class="size-3.5 shrink-0 text-lagoon-600" />
                    <span class="truncate">{{ post.community.name }}</span>
                </span>
                <span v-if="post.location_label" class="inline-flex items-center gap-1.5">
                    <BaseIcon name="pin" class="size-3.5 shrink-0 text-lagoon-600" />
                    {{ post.location_label }}
                </span>
                <span v-if="post.created_at_human" class="inline-flex items-center gap-1.5">
                    <BaseIcon name="clock" class="size-3.5 shrink-0 text-lagoon-600" />
                    {{ post.created_at_human }}
                </span>
                <span class="ml-auto inline-flex shrink-0 items-center gap-1.5 font-semibold text-ink">
                    <template v-if="post.replies_count > 0">
                        {{ post.replies_count }} {{ post.replies_count === 1 ? 'reply' : 'replies' }}
                    </template>
                    <template v-else-if="showWaiting">
                        <span class="inline-flex items-center gap-1 text-lagoon-800">
                            <BaseIcon name="sprout" class="size-3.5" />
                            Be first
                        </span>
                    </template>
                </span>
            </div>

            <p
                v-if="post.match_reason"
                class="mt-3.5 inline-flex max-w-full items-center gap-1.5 rounded-full bg-lagoon-50 px-3 py-1.5 text-[11px] font-semibold text-lagoon-800"
            >
                <BaseIcon name="sparkles" class="size-3.5 shrink-0" />
                <span class="truncate">{{ post.match_reason }}</span>
            </p>
        </div>
    </Link>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import BaseIcon from '@/components/BaseIcon.vue';

export type HomePostCard = {
    id: number;
    type: string;
    type_label: string;
    title: string;
    body: string;
    category_key: string;
    category_label: string;
    location_label: string | null;
    urgency: string;
    urgency_label: string;
    status: string;
    status_label: string;
    replies_count: number;
    created_at: string | null;
    created_at_human?: string | null;
    href: string;
    community: {
        slug: string;
        name: string;
    };
    match_reason?: string | null;
    author_name?: string | null;
};

const props = defineProps<{
    post: HomePostCard;
}>();

const isOffer = computed(() => props.post.type === 'offer');
const isUrgent = computed(() => props.post.urgency === 'urgent');
const showWaiting = computed(() => props.post.status === 'open' && props.post.type === 'need');

const accent = computed(() => {
    if (isOffer.value) {
        return {
            rail: 'bg-moss-400',
            pill: 'bg-moss-100 text-moss-800',
            dot: 'bg-moss-500',
        };
    }

    return {
        rail: 'bg-lagoon-400',
        pill: 'bg-lagoon-100 text-lagoon-800',
        dot: 'bg-lagoon-500',
    };
});
</script>
