<template>
    <Head :title="community.name" />

    <AppNav>
        <main class="mx-auto w-full max-w-5xl px-5 pt-8 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-12">
            <p v-if="flash?.status" class="mb-6 rounded-2xl border border-lagoon-200 bg-lagoon-50 px-4 py-3 text-sm text-lagoon-900">
                {{ flash.status }}
            </p>

            <Link
                href="/communities"
                class="group inline-flex h-10 items-center gap-2 rounded-full border border-line bg-white px-3.5 text-sm font-semibold text-ink shadow-card transition hover:border-lagoon-300 hover:bg-lagoon-50/50"
            >
                <BaseIcon name="arrow" class="size-4 rotate-180 transition-transform group-hover:-translate-x-0.5" />
                All communities
            </Link>

            <header class="mt-5 pb-8">
                <div
                    class="mb-6 overflow-hidden rounded-[1.5rem] border border-line/80 shadow-card"
                >
                    <img
                        v-if="community.cover_url"
                        :src="community.cover_url"
                        :alt="`${community.name} cover`"
                        class="h-44 w-full object-cover sm:h-56"
                    >
                    <div
                        v-else
                        class="flex h-36 items-end bg-linear-to-br from-lagoon-100 via-lagoon-50 to-white px-5 py-5 sm:h-44 sm:px-7"
                    >
                        <p class="font-heading text-lg font-bold text-lagoon-900/80">{{ community.name }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <p
                            v-if="community.location_label"
                            class="inline-flex items-center gap-1.5 rounded-full border border-line/80 bg-white px-3 py-1 text-sm font-medium text-ink-soft shadow-card"
                        >
                            <BaseIcon name="pin" class="size-3.5 text-lagoon-600" />
                            {{ community.location_label }}
                        </p>
                        <h1 class="mt-3 font-heading text-3xl font-bold tracking-tight text-balance text-ink sm:text-[2.5rem] sm:leading-tight">
                            {{ community.name }}
                        </h1>
                        <div class="mt-3.5 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-line bg-white px-3 py-1 text-xs font-semibold text-ink-soft">
                                <BaseIcon :name="community.access_mode === 'open' ? 'users' : 'lock'" class="size-3" />
                                {{ community.access_label }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-line bg-white px-3 py-1 text-xs font-medium text-ink-soft">
                                <BaseIcon name="users" class="size-3.5 text-lagoon-600" />
                                {{ community.members_count }} {{ community.members_count === 1 ? 'member' : 'members' }}
                            </span>
                            <span
                                v-if="membership.role_label"
                                class="inline-flex items-center gap-1 rounded-full bg-lagoon-100 px-3 py-1 text-xs font-semibold text-lagoon-800"
                            >
                                <BaseIcon name="verified" bold class="size-3" />
                                {{ membership.role_label }}
                            </span>
                        </div>
                    </div>
                    <Link
                        v-if="membership.can_manage"
                        :href="`/communities/${community.slug}/settings`"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-full border border-line bg-white px-5 font-heading text-sm font-semibold text-ink transition hover:border-lagoon-300 hover:bg-lagoon-50"
                    >
                        <BaseIcon name="gear" class="size-4" />
                        Settings
                    </Link>
                </div>
                <p v-if="community.description" class="mt-5 max-w-2xl text-base leading-relaxed text-ink-soft">
                    {{ community.description }}
                </p>
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

            <div class="border-t border-line" />

            <section v-if="!membership.is_member" class="mt-8 rounded-[1.35rem] border border-line/90 bg-white p-5 sm:p-6">
                <h2 class="font-heading text-lg font-bold text-ink">Join this community</h2>

                <template v-if="community.access_mode === 'invite_only'">
                    <p class="mt-2 text-sm leading-relaxed text-ink-soft">
                        This community is invite only. Ask a member to invite you, or accept an invitation if you have one.
                    </p>
                </template>

                <template v-else-if="membership.pending_request">
                    <p class="mt-2 text-sm leading-relaxed text-ink-soft">
                        Your request to join is waiting for an admin to review it.
                    </p>
                </template>

                <template v-else>
                    <p class="mt-2 text-sm leading-relaxed text-ink-soft">
                        <template v-if="community.access_mode === 'open'">
                            Anyone can join. Welcome in when you're ready.
                        </template>
                        <template v-else>
                            Request to join. Admins will review your request.
                        </template>
                    </p>

                    <div v-if="community.guidelines" class="mt-4 rounded-2xl border border-line bg-paper/80 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Guidelines</p>
                        <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-ink-soft">{{ community.guidelines }}</p>
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
                class="mt-8 rounded-[1.35rem] border border-line/90 bg-white p-5 sm:p-6"
            >
                <h2 class="font-heading text-lg font-bold text-ink">Accept the guidelines</h2>
                <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-ink-soft">{{ community.guidelines }}</p>
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
                <div class="overflow-hidden rounded-[1.5rem] border border-lagoon-200/70 bg-linear-to-br from-lagoon-50 via-white to-white p-5 shadow-card sm:p-6">
                    <div class="max-w-xl">
                        <p class="text-xs font-semibold tracking-[0.14em] text-lagoon-700 uppercase">Post in this community</p>
                        <h2 id="compose-heading" class="mt-2 font-heading text-2xl font-bold tracking-tight text-ink">
                            Need help, or ready to offer it?
                        </h2>
                        <p class="mt-2 text-sm leading-relaxed text-ink-soft">
                            Replies stay in the thread. Keep it practical, local, and kind.
                        </p>
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
                                <BaseIcon name="arrow" class="size-4 transition-transform duration-300 group-hover:translate-x-0.5" />
                            </span>
                        </Link>

                        <Link
                            :href="`/communities/${community.slug}/posts/create?type=offer`"
                            class="group relative flex min-h-[7.5rem] flex-col justify-between overflow-hidden rounded-[1.25rem] border border-line bg-white px-5 py-5 text-ink transition duration-300 ease-out hover:-translate-y-0.5 hover:border-lagoon-300 hover:bg-lagoon-50/40"
                        >
                            <span class="text-xs font-semibold tracking-[0.12em] text-ink-soft uppercase">Offer</span>
                            <span>
                                <span class="block font-heading text-xl font-bold">Offer help</span>
                                <span class="mt-1 block text-sm text-ink-soft">Share time, advice, or a local tip</span>
                            </span>
                            <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-lagoon-800">
                                Start an Offer
                                <BaseIcon name="arrow" class="size-4 transition-transform duration-300 group-hover:translate-x-0.5" />
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
                        <h2 id="feed-heading" class="font-heading text-xl font-bold tracking-tight text-ink">Needs &amp; offers</h2>
                        <p class="mt-1 text-sm text-ink-soft">Open asks and offers from members here.</p>
                    </div>
                    <Link
                        v-if="membership.can_post"
                        :href="`/communities/${community.slug}/posts/create?type=need`"
                        class="inline-flex h-10 items-center justify-center rounded-full border border-line bg-white px-4 text-sm font-semibold text-ink transition hover:border-lagoon-300 hover:text-lagoon-800"
                    >
                        New post
                    </Link>
                </div>

                <ul v-if="membership.is_member && posts.length" class="mt-5 grid gap-3 sm:gap-4">
                    <li v-for="post in posts" :key="post.id" class="min-w-0">
                        <Link
                            :href="`/communities/${community.slug}/posts/${post.id}`"
                            class="group relative block overflow-hidden rounded-[1.35rem] border border-line/80 bg-white shadow-card transition duration-300 ease-out hover:-translate-y-0.5 hover:border-lagoon-200 hover:shadow-lift focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lagoon-700"
                        >
                            <span
                                class="absolute inset-y-0 left-0 w-[3px]"
                                :class="post.type === 'offer' ? 'bg-moss-400' : 'bg-lagoon-400'"
                                aria-hidden="true"
                            />
                            <div class="px-5 py-4 pl-6 sm:px-6 sm:py-5 sm:pl-7">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold"
                                        :class="post.type === 'offer' ? 'bg-moss-100 text-moss-800' : 'bg-lagoon-100 text-lagoon-800'"
                                    >
                                        <span class="size-1.5 rounded-full" :class="post.type === 'offer' ? 'bg-moss-500' : 'bg-lagoon-500'" aria-hidden="true" />
                                        {{ post.type_label }}
                                    </span>
                                    <span class="min-w-0 truncate text-xs font-medium text-ink-soft">{{ post.category_label }}</span>
                                    <span
                                        v-if="post.is_resolved"
                                        class="ml-auto inline-flex shrink-0 items-center gap-1 rounded-full bg-moss-100 px-2.5 py-1 text-[11px] font-semibold text-moss-800"
                                    >
                                        <BaseIcon name="check" bold class="size-3" />
                                        Resolved
                                    </span>
                                    <span
                                        v-else-if="post.is_expired"
                                        class="ml-auto shrink-0 rounded-full border border-line bg-paper px-2.5 py-1 text-[11px] font-semibold text-ink-soft"
                                    >
                                        Expired
                                    </span>
                                    <span
                                        v-else-if="post.type === 'need' && post.urgency_label === 'Urgent'"
                                        class="ml-auto inline-flex shrink-0 items-center gap-1 rounded-full bg-clay-100 px-2.5 py-1 text-[11px] font-semibold text-clay-800"
                                    >
                                        <BaseIcon name="clock" bold class="size-3" />
                                        Urgent
                                    </span>
                                </div>

                                <h3 class="mt-3 line-clamp-2 font-heading text-[1.05rem] font-bold tracking-tight text-ink transition-colors group-hover:text-lagoon-900">
                                    {{ post.title }}
                                </h3>

                                <div class="mt-3.5 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs font-medium text-ink-soft">
                                    <span class="inline-flex min-w-0 items-center gap-1.5">
                                        <span class="grid size-5 shrink-0 place-items-center rounded-full bg-lagoon-100 text-[10px] font-bold text-lagoon-800" aria-hidden="true">
                                            {{ authorInitial(post.author_name) }}
                                        </span>
                                        <span class="truncate">{{ post.author_name }}</span>
                                    </span>
                                    <span v-if="post.location_label" class="inline-flex items-center gap-1.5">
                                        <BaseIcon name="pin" class="size-3.5 shrink-0 text-lagoon-600" />
                                        {{ post.location_label }}
                                    </span>
                                    <time
                                        v-if="post.created_at_human"
                                        class="inline-flex items-center gap-1.5"
                                        :datetime="post.created_at ?? undefined"
                                    >
                                        <BaseIcon name="clock" class="size-3.5 shrink-0 text-lagoon-600" />
                                        {{ post.created_at_human }}
                                    </time>
                                    <span class="ml-auto shrink-0 font-semibold text-ink">
                                        {{ post.replies_count }} {{ post.replies_count === 1 ? 'reply' : 'replies' }}
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </li>
                </ul>
                <div
                    v-else-if="membership.is_member"
                    class="mt-5 rounded-[1.4rem] border border-dashed border-line bg-white px-5 py-12 text-center shadow-card"
                >
                    <p class="text-sm font-semibold text-ink">Nothing here yet</p>
                    <p class="mt-1 text-sm text-ink-soft">Use Ask for help or Offer help above to start the first post.</p>
                </div>
                <div
                    v-else
                    class="mt-5 rounded-[1.4rem] border border-dashed border-line bg-white px-5 py-12 text-center shadow-card"
                >
                    <p class="text-sm text-ink-soft">Join this community to see Needs and Offers.</p>
                </div>
            </section>

            <section v-if="admins.length" class="mt-10 pb-4" aria-labelledby="admins-heading">
                <h2 id="admins-heading" class="font-heading text-xl font-bold tracking-tight text-ink">Admins</h2>
                <ul class="mt-4 space-y-2">
                    <li
                        v-for="admin in admins"
                        :key="admin.id"
                        class="flex items-center justify-between rounded-2xl border border-line/90 bg-white px-4 py-3"
                    >
                        <span class="text-sm font-medium text-ink">{{ admin.name }}</span>
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
import BaseIcon from '@/components/BaseIcon.vue';
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
        created_at_human?: string | null;
    }>;
    admins: Array<{ id: number; name: string; role: string; role_label: string }>;
}>();

function authorInitial(name: string): string {
    return name.trim().charAt(0).toUpperCase() || '?';
}

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
