<template>
    <Link
        :href="post.href"
        class="block rounded-[1.25rem] border border-line/90 bg-white p-4 shadow-card transition duration-300 hover:border-lagoon-200 hover:shadow-lift focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lagoon-700 sm:p-5"
    >
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-lagoon-100 px-2.5 py-0.5 text-xs font-semibold text-lagoon-800">
                {{ post.category_label }}
            </span>
            <span class="rounded-full border border-line bg-white px-2.5 py-0.5 text-xs font-medium text-ink-soft">
                {{ post.status_label }}
            </span>
            <span
                v-if="post.urgency === 'urgent'"
                class="rounded-full bg-clay-100 px-2.5 py-0.5 text-xs font-semibold text-clay-800"
            >
                {{ post.urgency_label }}
            </span>
        </div>

        <h3 class="mt-3 font-heading text-base font-bold tracking-tight text-gray-800">
            {{ post.title }}
        </h3>
        <p class="mt-1.5 line-clamp-2 text-sm leading-6 text-gray-600">{{ post.body }}</p>

        <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-medium text-ink-soft">
            <span>{{ post.community.name }}</span>
            <span v-if="post.location_label">{{ post.location_label }}</span>
            <span v-if="post.replies_count > 0">
                {{ post.replies_count }} {{ post.replies_count === 1 ? 'reply' : 'replies' }}
            </span>
            <span v-else-if="showWaiting">Waiting for a reply</span>
        </div>

        <p
            v-if="post.match_reason"
            class="mt-3 text-xs font-semibold text-lagoon-800"
        >
            {{ post.match_reason }}
        </p>
    </Link>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

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

const showWaiting = computed(() => props.post.status === 'open' && props.post.type === 'need');
</script>
