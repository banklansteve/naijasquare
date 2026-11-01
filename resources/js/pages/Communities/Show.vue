<template>
    <Head :title="community.name" />

    <AppNav>
        <main class="mx-auto w-full max-w-5xl px-5 pt-8 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-12">
            <p v-if="flash?.status" class="mb-6 rounded-2xl border border-lagoon-200 bg-lagoon-50 px-4 py-3 text-sm text-lagoon-900">
                {{ flash.status }}
            </p>

            <header class="pb-8">
                <div
                    v-if="community.cover_url"
                    class="mb-6 overflow-hidden rounded-[1.35rem] border border-gray-200/80"
                >
                    <img :src="community.cover_url" :alt="`${community.name} cover`" class="h-44 w-full object-cover sm:h-56">
                </div>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-ink-soft">{{ community.location_label }}</p>
                        <h1 class="mt-2 font-heading text-3xl font-bold tracking-tight text-balance text-gray-800 sm:text-[2.5rem] sm:leading-tight">
                            {{ community.name }}
                        </h1>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-line bg-white px-3 py-1 text-xs font-semibold text-ink-soft">
                                {{ community.access_label }}
                            </span>
                            <span class="text-xs font-medium text-ink-soft">
                                {{ community.members_count }} {{ community.members_count === 1 ? 'member' : 'members' }}
                            </span>
                            <span
                                v-if="membership.role_label"
                                class="rounded-full bg-lagoon-100 px-3 py-1 text-xs font-semibold text-lagoon-800"
                            >
                                {{ membership.role_label }}
                            </span>
                        </div>
                    </div>
                    <Link
                        v-if="membership.can_manage"
                        :href="`/communities/${community.slug}/settings`"
                        class="inline-flex h-11 items-center justify-center rounded-full border border-line bg-white px-5 font-heading text-sm font-semibold text-gray-800"
                    >
                        Settings
                    </Link>
                </div>
                <p class="mt-5 max-w-2xl text-base leading-relaxed text-gray-600">{{ community.description }}</p>
                <div v-if="community.tags.length" class="mt-4 flex flex-wrap gap-2">
                    <span
                        v-for="tag in community.tags"
                        :key="tag"
                        class="rounded-full border border-line bg-white px-3 py-1 text-xs font-medium text-ink-soft"
                    >
                        {{ tag }}
                    </span>
                </div>
            </header>

            <div class="border-t border-gray-200/90" />

            <section v-if="!membership.is_member" class="mt-8 rounded-[1.35rem] border border-gray-200/80 bg-white p-5 sm:p-6">
                <h2 class="font-heading text-lg font-bold text-gray-800">Join this community</h2>

                <template v-if="community.access_mode === 'invite_only'">
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">
                        This community is invite only. Ask a member to invite you, or accept an invitation if you have one.
                    </p>
                </template>

                <template v-else-if="membership.pending_request">
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">
                        Your request to join is waiting for an admin to review it.
                    </p>
                </template>

                <template v-else>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">
                        <template v-if="community.access_mode === 'open'">
                            Anyone can join. Welcome in when you're ready.
                        </template>
                        <template v-else>
                            Request to join. Admins will review your request.
                        </template>
                    </p>

                    <div v-if="community.guidelines" class="mt-4 rounded-2xl border border-line bg-paper/80 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Guidelines</p>
                        <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-gray-600">{{ community.guidelines }}</p>
                    </div>

                    <div v-if="community.require_guidelines_accept" class="mt-4">
                        <UiCheckbox
                            v-model="joinForm.accept_guidelines"
                            label="I accept the community guidelines"
                            :error="joinForm.errors.accept_guidelines"
                        />
                    </div>

                    <div v-if="community.access_mode === 'request'" class="mt-4">
                        <UiTextarea
                            v-model="joinForm.note"
                            label="Note for admins"
                            optional
                            compact
                            :rows="3"
                            placeholder="A short note about why you'd like to join"
                        />
                    </div>

                    <button
                        type="button"
                        class="mt-5 inline-flex h-11 items-center justify-center rounded-full bg-lagoon-800 px-6 font-heading text-sm font-semibold text-white disabled:opacity-60"
                        :disabled="joinForm.processing"
                        @click="submitJoin"
                    >
                        {{ community.access_mode === 'open' ? 'Join community' : 'Request to join' }}
                    </button>
                </template>
            </section>

            <section
                v-else-if="community.require_guidelines_accept && !membership.guidelines_accepted"
                class="mt-8 rounded-[1.35rem] border border-gray-200/80 bg-white p-5 sm:p-6"
            >
                <h2 class="font-heading text-lg font-bold text-gray-800">Accept the guidelines</h2>
                <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-gray-600">{{ community.guidelines }}</p>
                <button
                    type="button"
                    class="mt-5 inline-flex h-11 items-center justify-center rounded-full bg-lagoon-800 px-6 font-heading text-sm font-semibold text-white"
                    @click="acceptGuidelines"
                >
                    I accept
                </button>
            </section>

            <section
                v-if="membership.is_member && membership.guidelines_accepted"
                class="mt-8"
                aria-labelledby="compose-heading"
            >
                <div class="overflow-hidden rounded-[1.5rem] border border-lagoon-200/70 bg-gradient-to-br from-lagoon-50 via-white to-white p-5 shadow-[0_18px_40px_-28px_rgb(12_44_49/0.45)] sm:p-6">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div class="max-w-xl">
                            <p class="text-xs font-semibold tracking-[0.14em] text-lagoon-700 uppercase">Post in this community</p>
                            <h2 id="compose-heading" class="mt-2 font-heading text-2xl font-bold tracking-tight text-gray-800">
                                Need help, or ready to offer it?
                            </h2>
                            <p class="mt-2 text-sm leading-relaxed text-gray-600">
                                Replies stay in the thread. Keep it practical, local, and kind.
                            </p>
                        </div>
                    </div>

                    <div v-if="membership.can_post" class="mt-5 grid gap-3 sm:grid-cols-2">
                        <Link
                            :href="`/communities/${community.slug}/posts/create?type=need`"
                            class="group relative flex min-h-[7.5rem] flex-col justify-between overflow-hidden rounded-[1.25rem] bg-lagoon-800 px-5 py-5 text-white transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-lagoon-900"
                        >
                            <span class="text-xs font-semibold tracking-[0.12em] text-lagoon-100 uppercase">Need</span>
                            <span>
                                <span class="block font-heading text-xl font-bold">Ask for help</span>
                                <span class="mt-1 block text-sm text-lagoon-100/90">Housing, paperwork, settling in…</span>
                            </span>
                            <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-white">
                                Start a Need
                                <svg class="size-4 transition-transform duration-300 group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M7.5 5 12.5 10 7.5 15" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </Link>

                        <Link
                            :href="`/communities/${community.slug}/posts/create?type=offer`"
                            class="group relative flex min-h-[7.5rem] flex-col justify-between overflow-hidden rounded-[1.25rem] border border-line bg-white px-5 py-5 text-gray-800 transition duration-300 ease-out hover:-translate-y-0.5 hover:border-lagoon-300 hover:bg-lagoon-50/40"
                        >
                            <span class="text-xs font-semibold tracking-[0.12em] text-ink-soft uppercase">Offer</span>
                            <span>
                                <span class="block font-heading text-xl font-bold">Offer help</span>
                                <span class="mt-1 block text-sm text-gray-600">Share time, advice, or a local tip</span>
                            </span>
                            <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-lagoon-800">
                                Start an Offer
                                <svg class="size-4 transition-transform duration-300 group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M7.5 5 12.5 10 7.5 15" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </Link>
                    </div>

                    <p v-else class="mt-5 rounded-2xl border border-line bg-white/80 px-4 py-3 text-sm text-ink-soft">
                        Only admins can start new posts here. You can still reply to open Needs and Offers below.
                    </p>
                </div>
            </section>

            <section class="mt-10" aria-labelledby="feed-heading">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 id="feed-heading" class="text-xl font-semibold tracking-wide text-gray-700">Needs & offers</h2>
                        <p class="mt-1 text-sm text-ink-soft">Open asks and offers from members here.</p>
                    </div>
                    <Link
                        v-if="membership.can_post"
                        :href="`/communities/${community.slug}/posts/create?type=need`"
                        class="inline-flex h-10 items-center justify-center rounded-full border border-line bg-white px-4 text-sm font-semibold text-gray-800 transition hover:border-lagoon-300 hover:text-lagoon-800"
                    >
                        New post
                    </Link>
                </div>

                <ul v-if="membership.is_member && posts.length" class="mt-4 space-y-3">
                    <li
                        v-for="post in posts"
                        :key="post.id"
                        class="rounded-[1.35rem] border border-gray-200/80 bg-white px-4 py-4 shadow-[0_1px_2px_rgb(15_23_42/0.04)] transition duration-300 hover:border-lagoon-200 sm:px-5"
                    >
                        <Link :href="`/communities/${community.slug}/posts/${post.id}`" class="block">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="post.type === 'offer' ? 'bg-moss-100 text-moss-800' : 'bg-lagoon-50 text-lagoon-800'"
                                >
                                    {{ post.type_label }}
                                </span>
                                <span class="text-xs font-medium text-ink-soft">{{ post.category_label }}</span>
                                <span v-if="post.is_resolved" class="text-xs font-semibold text-lagoon-800">Resolved</span>
                                <span v-else-if="post.is_expired" class="text-xs font-semibold text-ink-soft">Expired</span>
                            </div>
                            <h3 class="mt-2 font-heading text-base font-bold text-gray-800">{{ post.title }}</h3>
                            <p class="mt-2 text-xs font-medium text-ink-soft">
                                {{ post.author_name }}
                                <template v-if="post.location_label"> · {{ post.location_label }}</template>
                                · {{ post.urgency_label }}
                                · {{ post.replies_count }} {{ post.replies_count === 1 ? 'reply' : 'replies' }}
                            </p>
                        </Link>
                    </li>
                </ul>
                <div
                    v-else-if="membership.is_member"
                    class="mt-4 rounded-[1.35rem] border border-dashed border-gray-200 bg-white/70 px-5 py-10 text-center"
                >
                    <p class="text-sm text-gray-500">Nothing here yet. Use Ask for help or Offer help above to start the first post.</p>
                </div>
                <div
                    v-else
                    class="mt-4 rounded-[1.35rem] border border-dashed border-gray-200 bg-white/70 px-5 py-10 text-center"
                >
                    <p class="text-sm text-gray-500">Join this community to see Needs and Offers.</p>
                </div>
            </section>

            <section v-if="admins.length" class="mt-10 pb-4" aria-labelledby="admins-heading">
                <h2 id="admins-heading" class="text-xl font-semibold tracking-wide text-gray-700">Admins</h2>
                <ul class="mt-4 space-y-2">
                    <li
                        v-for="admin in admins"
                        :key="admin.id"
                        class="flex items-center justify-between rounded-2xl border border-gray-200/80 bg-white px-4 py-3"
                    >
                        <span class="text-sm font-medium text-gray-800">{{ admin.name }}</span>
                        <span class="text-xs font-semibold text-ink-soft">{{ admin.role_label }}</span>
                    </li>
                </ul>
            </section>
        </main>
    </AppNav>
</template>

<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppNav from '@/components/AppNav.vue';
import UiCheckbox from '@/components/ui/UiCheckbox.vue';
import UiTextarea from '@/components/ui/UiTextarea.vue';

const props = defineProps<{
    community: {
        id: number;
        slug: string;
        name: string;
        description: string;
        access_mode: string;
        access_label: string;
        location_label: string | null;
        guidelines: string | null;
        require_guidelines_accept: boolean;
        members_count: number;
        tags: string[];
        cover_url: string | null;
    };
    membership: {
        is_member: boolean;
        role: string | null;
        role_label: string | null;
        pending_request: boolean;
        guidelines_accepted: boolean;
        can_manage: boolean;
        can_post: boolean;
    };
    posts: Array<{
        id: number;
        type: string;
        type_label: string;
        title: string;
        category_label: string;
        location_label: string | null;
        urgency_label: string;
        is_resolved: boolean;
        is_expired: boolean;
        replies_count: number;
        author_name: string;
        created_at: string | null;
    }>;
    admins: Array<{ id: number; name: string; role: string; role_label: string }>;
}>();

const page = usePage();
const flash = computed(() => (page.props.flash as { status?: string } | undefined));

const joinForm = useForm({
    note: '',
    accept_guidelines: false,
});

function submitJoin(): void {
    joinForm.post(`/communities/${props.community.slug}/join`, { preserveScroll: true });
}

function acceptGuidelines(): void {
    useForm({}).post(`/communities/${props.community.slug}/accept-guidelines`, { preserveScroll: true });
}
</script>
