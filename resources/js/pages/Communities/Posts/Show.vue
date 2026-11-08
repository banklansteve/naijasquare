<template>
    <Head :title="post.title" />

    <AppNav>
        <main class="mx-auto w-full max-w-3xl px-5 pt-8 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-12">
            <p v-if="flash?.status" class="mb-6 rounded-2xl border border-lagoon-200 bg-lagoon-50 px-4 py-3 text-sm text-lagoon-900">
                {{ flash.status }}
            </p>

            <div class="flex flex-wrap items-center gap-3">
                <Link
                    :href="`/communities/${community.slug}`"
                    class="inline-flex h-10 items-center gap-2 rounded-full border border-line bg-white px-3.5 text-sm font-semibold text-ink shadow-card transition hover:border-lagoon-300 hover:bg-lagoon-50/50"
                >
                    <BaseIcon name="arrow" class="size-4 rotate-180" />
                    <span class="hidden sm:inline">Back to {{ community.name }}</span>
                    <span class="sm:hidden">Back</span>
                </Link>
                <p class="min-w-0 text-sm font-medium text-ink-soft">
                    <Link :href="`/communities/${community.slug}`" class="text-lagoon-800 hover:underline">
                        {{ community.name }}
                    </Link>
                </p>
            </div>

            <header class="mt-5 pb-6">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-full px-2.5 py-1 text-xs font-semibold"
                        :class="post.type === 'offer' ? 'bg-moss-100 text-moss-800' : 'bg-lagoon-50 text-lagoon-800'"
                    >
                        {{ post.type_label }}
                    </span>
                    <span
                        v-for="label in postCategoryLabels"
                        :key="label"
                        class="rounded-full border border-line bg-paper px-2.5 py-1 text-xs font-medium text-ink-soft"
                    >
                        {{ label }}
                    </span>
                    <span v-if="post.is_resolved" class="rounded-full bg-lagoon-50 px-2.5 py-1 text-xs font-semibold text-lagoon-800">Resolved</span>
                    <span v-else-if="post.is_expired" class="rounded-full border border-line bg-paper px-2.5 py-1 text-xs font-semibold text-ink-soft">Expired</span>
                </div>
                <h1 class="mt-3 font-heading text-3xl font-bold tracking-tight text-balance text-gray-800">
                    {{ post.title }}
                </h1>
                <p class="mt-3 text-sm text-ink-soft">
                    {{ post.author.name }}
                    <template v-if="postLocationText"> · {{ postLocationText }}</template>
                    <template v-if="post.urgency_label"> · {{ post.urgency_label }}</template>
                    <template v-if="post.created_at_human">
                        ·
                        <time :datetime="post.created_at ?? undefined" :title="post.created_at ?? undefined">
                            {{ post.created_at_human }}
                        </time>
                    </template>
                </p>
                <p class="mt-5 whitespace-pre-wrap text-base leading-relaxed text-gray-700">{{ post.body }}</p>

                <div
                    v-if="post.requires_safety_warning && !permissions.is_author"
                    class="mt-5 rounded-2xl border border-clay-200 bg-clay-50 px-4 py-3 text-sm leading-relaxed text-clay-900"
                    role="note"
                >
                    {{ safetyNotice }}
                </div>

                <p
                    v-if="isHidden"
                    class="mt-5 rounded-2xl border border-clay-200 bg-clay-50 px-4 py-3 text-sm text-clay-900"
                    role="status"
                >
                    This post is hidden from members. You can still review it because you can moderate this community.
                </p>

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
                        v-if="permissions.can_report"
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

            <RelatedOffersPanel
                v-if="post.type === 'need' && relatedOffers.length"
                class="mt-2"
                :offers="relatedOffers"
                title="Offers that might already help"
                subtitle="Reply on an Offer if it fits. Or keep the conversation going below."
                heading-id="related-offers-show"
            />

            <div class="border-t border-gray-200/90" />

            <section class="mt-8" aria-labelledby="replies-heading">
                <h2 id="replies-heading" class="text-xl font-semibold tracking-wide text-gray-700">
                    Replies
                </h2>

                <ul v-if="replies.length" class="mt-4 space-y-3">
                    <li
                        v-for="reply in replies"
                        :key="reply.id"
                        class="rounded-[1.25rem] border px-4 py-4"
                        :class="reply.is_helped
                            ? 'border-moss-200 bg-moss-50/40'
                            : 'border-gray-200/80 bg-white'"
                    >
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-semibold text-gray-800">{{ reply.author.name }}</p>
                            <span
                                v-if="reply.is_helped"
                                class="inline-flex items-center gap-1 rounded-full bg-moss-100 px-2.5 py-0.5 text-xs font-semibold text-moss-800"
                            >
                                <BaseIcon name="check" class="size-3.5" />
                                Helped
                            </span>
                            <time
                                v-if="reply.created_at_human"
                                class="text-xs font-medium text-ink-soft"
                                :datetime="reply.created_at ?? undefined"
                                :title="reply.created_at ?? undefined"
                            >
                                {{ reply.created_at_human }}
                            </time>
                        </div>
                        <p
                            v-if="reply.is_hidden"
                            class="mt-2 text-sm italic text-ink-soft"
                        >
                            This reply was removed by community admins.
                        </p>
                        <template v-else>
                            <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-gray-700">{{ reply.body }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <button
                                    v-if="permissions.can_mark_helped && !reply.is_helped"
                                    type="button"
                                    class="text-xs font-semibold text-moss-800"
                                    @click="markHelped(reply.id)"
                                >
                                    This helped
                                </button>
                                <button
                                    v-else-if="permissions.can_mark_helped && reply.is_helped"
                                    type="button"
                                    class="text-xs font-semibold text-ink-soft"
                                    @click="unmarkHelped(reply.id)"
                                >
                                    Remove helpful mark
                                </button>
                                <button
                                    v-if="canReportReply(reply)"
                                    type="button"
                                    class="text-xs font-semibold text-clay-800"
                                    @click="submitReport('reply', reply.id)"
                                >
                                    Report reply
                                </button>
                            </div>
                        </template>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-gray-500">
                    {{ emptyRepliesCopy }}
                </p>

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
                <p v-else-if="permissions.is_author" class="mt-6 text-sm text-ink-soft">
                    {{ post.type === 'offer'
                        ? 'People who need this help will reply here. You cannot reply on your own Offer.'
                        : 'People who can help will reply here. You cannot reply on your own Need.' }}
                </p>
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
import BaseIcon from '@/components/BaseIcon.vue';
import RelatedOffersPanel, { type RelatedOfferCard } from '@/components/home/RelatedOffersPanel.vue';
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
        category_keys?: string[];
        category_label: string;
        category_labels?: string[];
        location_label: string | null;
        location_labels?: string[];
        location_specific?: boolean;
        urgency: string;
        urgency_label: string | null;
        expires_at: string | null;
        resolved_at: string | null;
        is_expired: boolean;
        is_resolved: boolean;
        requires_safety_warning: boolean;
        created_at: string | null;
        created_at_human?: string | null;
        author: { id: number; name: string };
        replies_count: number;
    };
    replies: Array<{
        id: number;
        body: string;
        is_hidden?: boolean;
        is_helped?: boolean;
        helped_at?: string | null;
        created_at: string | null;
        created_at_human?: string | null;
        author: { id: number; name: string };
    }>;
    permissions: {
        can_reply: boolean;
        can_report: boolean;
        can_mark_helped?: boolean;
        can_resolve: boolean;
        can_moderate?: boolean;
        is_author?: boolean;
    };
    reportReasons: Record<string, string>;
    safetyNotice: string;
    isHidden?: boolean;
    relatedOffers?: RelatedOfferCard[];
}>();

const relatedOffers = computed(() => props.relatedOffers ?? []);

const postCategoryLabels = computed(() => {
    if (props.post.category_labels?.length) {
        return props.post.category_labels;
    }

    return [props.post.category_label];
});

const postLocationText = computed(() => {
    const labels = (props.post.location_labels ?? []).filter(Boolean);
    if (labels.length > 0) {
        return labels.join(', ');
    }

    if (props.post.location_label) {
        return props.post.location_label;
    }

    if (props.post.type === 'offer' && props.post.location_specific === false) {
        return 'Anywhere';
    }

    return null;
});

const emptyRepliesCopy = computed(() => {
    if (props.post.type === 'offer') {
        return 'No replies yet. People who need this help can reply here.';
    }

    return 'No replies yet. Be the first to help.';
});

const page = usePage();
const flash = computed(() => (page.props.flash as { status?: string } | undefined));
const authUserId = computed(() => {
    const auth = page.props.auth as { user?: { id?: number } | null } | undefined;

    return auth?.user?.id ?? null;
});
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

function canReportReply(reply: { author: { id: number } }): boolean {
    return authUserId.value !== null && authUserId.value !== reply.author.id;
}

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

function markHelped(replyId: number): void {
    useForm({}).post(
        `/communities/${props.community.slug}/posts/${props.post.id}/replies/${replyId}/helped`,
        { preserveScroll: true },
    );
}

function unmarkHelped(replyId: number): void {
    useForm({}).delete(
        `/communities/${props.community.slug}/posts/${props.post.id}/replies/${replyId}/helped`,
        { preserveScroll: true },
    );
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
