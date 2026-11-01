<template>
    <Head :title="post.title" />

    <AppNav>
        <main class="mx-auto w-full max-w-3xl px-5 pt-8 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-12">
            <p v-if="flash?.status" class="mb-6 rounded-2xl border border-lagoon-200 bg-lagoon-50 px-4 py-3 text-sm text-lagoon-900">
                {{ flash.status }}
            </p>

            <p class="text-sm font-medium text-ink-soft">
                <Link :href="`/communities/${community.slug}`" class="text-lagoon-800 hover:underline">
                    {{ community.name }}
                </Link>
            </p>

            <header class="mt-4 pb-6">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-lagoon-50 px-2.5 py-1 text-xs font-semibold text-lagoon-800">
                        {{ post.type_label }}
                    </span>
                    <span class="text-xs font-medium text-ink-soft">{{ post.category_label }}</span>
                    <span v-if="post.is_resolved" class="text-xs font-semibold text-lagoon-800">Resolved</span>
                    <span v-else-if="post.is_expired" class="text-xs font-semibold text-ink-soft">Expired</span>
                </div>
                <h1 class="mt-3 font-heading text-3xl font-bold tracking-tight text-balance text-gray-800">
                    {{ post.title }}
                </h1>
                <p class="mt-3 text-sm text-ink-soft">
                    {{ post.author.name }}
                    <template v-if="post.location_label"> · {{ post.location_label }}</template>
                    · {{ post.urgency_label }}
                </p>
                <p class="mt-5 whitespace-pre-wrap text-base leading-relaxed text-gray-700">{{ post.body }}</p>

                <div
                    v-if="post.requires_safety_warning"
                    class="mt-5 rounded-2xl border border-clay-200 bg-clay-50 px-4 py-3 text-sm leading-relaxed text-clay-900"
                    role="note"
                >
                    {{ safetyNotice }}
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button
                        v-if="permissions.can_resolve && !post.is_resolved"
                        type="button"
                        class="inline-flex h-10 items-center justify-center rounded-full border border-line bg-white px-4 text-sm font-semibold text-gray-800"
                        @click="resolve"
                    >
                        Mark resolved
                    </button>
                    <button
                        type="button"
                        class="inline-flex h-10 items-center justify-center rounded-full border border-line bg-white px-4 text-sm font-semibold text-clay-800"
                        @click="reportOpen = !reportOpen"
                    >
                        Report
                    </button>
                </div>

                <form
                    v-if="reportOpen"
                    class="mt-4 space-y-3 rounded-2xl border border-line bg-white p-4"
                    @submit.prevent="submitReport('post', post.id)"
                >
                    <UiSelect
                        v-model="reportForm.reason_key"
                        label="Why are you reporting this?"
                        compact
                        :options="reportReasonOptions"
                        :error="reportForm.errors.reason_key"
                    />
                    <UiTextarea
                        v-model="reportForm.note"
                        label="More detail"
                        optional
                        compact
                        :rows="3"
                        :error="reportForm.errors.note"
                    />
                    <p v-if="reportForm.errors.report" class="text-sm text-clay-800">{{ reportForm.errors.report }}</p>
                    <button
                        type="submit"
                        class="inline-flex h-10 items-center justify-center rounded-full bg-lagoon-800 px-4 text-sm font-semibold text-white disabled:opacity-60"
                        :disabled="reportForm.processing"
                    >
                        Send report
                    </button>
                </form>
            </header>

            <div class="border-t border-gray-200/90" />

            <section class="mt-8" aria-labelledby="replies-heading">
                <h2 id="replies-heading" class="text-xl font-semibold tracking-wide text-gray-700">
                    Replies
                </h2>

                <ul v-if="replies.length" class="mt-4 space-y-3">
                    <li
                        v-for="reply in replies"
                        :key="reply.id"
                        class="rounded-[1.25rem] border border-gray-200/80 bg-white px-4 py-4"
                    >
                        <p class="text-sm font-semibold text-gray-800">{{ reply.author.name }}</p>
                        <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-gray-700">{{ reply.body }}</p>
                        <button
                            type="button"
                            class="mt-3 text-xs font-semibold text-clay-800"
                            @click="submitReport('reply', reply.id)"
                        >
                            Report reply
                        </button>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-gray-500">No replies yet. Be the first to help.</p>

                <form
                    v-if="permissions.can_reply"
                    class="mt-6 space-y-3"
                    @submit.prevent="submitReply"
                >
                    <UiTextarea
                        v-model="replyForm.body"
                        label="Your reply"
                        name="body"
                        :rows="4"
                        :error="replyForm.errors.body"
                        placeholder="Keep it useful and kind. Stay in this thread."
                    />
                    <button
                        type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white disabled:opacity-60"
                        :disabled="replyForm.processing"
                    >
                        Post reply
                    </button>
                </form>
                <p v-else class="mt-6 text-sm text-ink-soft">
                    Accept the community guidelines to reply.
                </p>
            </section>
        </main>
    </AppNav>
</template>

<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import AppNav from '@/components/AppNav.vue';
import UiSelect from '@/components/ui/UiSelect.vue';
import UiTextarea from '@/components/ui/UiTextarea.vue';

const props = defineProps<{
    community: {
        id: number;
        slug: string;
        name: string;
    };
    post: {
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
        expires_at: string | null;
        resolved_at: string | null;
        is_expired: boolean;
        is_resolved: boolean;
        requires_safety_warning: boolean;
        created_at: string | null;
        author: { id: number; name: string };
        replies_count: number;
    };
    replies: Array<{
        id: number;
        body: string;
        created_at: string | null;
        author: { id: number; name: string };
    }>;
    permissions: {
        can_reply: boolean;
        can_resolve: boolean;
    };
    reportReasons: Record<string, string>;
    safetyNotice: string;
}>();

const page = usePage();
const flash = computed(() => (page.props.flash as { status?: string } | undefined));
const reportOpen = ref(false);

const replyForm = useForm({
    body: '',
});

const reportForm = useForm({
    reportable_type: 'post',
    reportable_id: props.post.id,
    reason_key: Object.keys(props.reportReasons)[0] ?? 'spam',
    note: '',
});

const reportReasonOptions = computed(() =>
    Object.entries(props.reportReasons).map(([value, label]) => ({ value, label })),
);

function submitReply(): void {
    replyForm.post(`/communities/${props.community.slug}/posts/${props.post.id}/replies`, {
        preserveScroll: true,
        onSuccess: () => replyForm.reset('body'),
    });
}

function resolve(): void {
    useForm({}).post(`/communities/${props.community.slug}/posts/${props.post.id}/resolve`, {
        preserveScroll: true,
    });
}

function submitReport(type: 'post' | 'reply', id: number): void {
    reportForm.reportable_type = type;
    reportForm.reportable_id = id;
    reportForm.post('/reports', {
        preserveScroll: true,
        onSuccess: () => {
            reportOpen.value = false;
            reportForm.reset('note');
        },
    });
}
</script>
