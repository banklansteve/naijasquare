<template>
    <Head title="Communities" />

    <AppNav>
        <main class="mx-auto w-full max-w-6xl px-5 pt-8 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-12">
            <p v-if="flash?.status" class="mb-6 rounded-2xl border border-lagoon-200 bg-lagoon-50 px-4 py-3 text-sm text-lagoon-900">
                {{ flash.status }}
            </p>

            <header class="flex flex-wrap items-end justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs font-semibold tracking-[0.14em] text-lagoon-700 uppercase">
                        Belonging
                    </p>
                    <h1 class="mt-2 font-heading text-3xl font-bold tracking-tight text-balance text-ink sm:text-[2.5rem] sm:leading-tight">
                        Communities
                    </h1>
                    <p class="mt-2 max-w-xl text-base leading-relaxed text-ink-soft">
                        Groups you run and belong to — and new ones worth discovering nearby.
                    </p>
                </div>
                <Link
                    href="/communities/create"
                    class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white transition hover:bg-lagoon-900"
                >
                    <BaseIcon name="plus" bold class="size-4" />
                    Create community
                </Link>
            </header>

            <form
                class="mt-8 grid gap-3 rounded-[1.4rem] border border-line/80 bg-white p-4 shadow-card sm:grid-cols-[minmax(0,1.4fr)_repeat(3,minmax(0,0.7fr))_auto] sm:items-end sm:p-5"
                @submit.prevent="applyFilters"
            >
                <div class="min-w-0 sm:col-span-1">
                    <UiTextField
                        v-model="form.q"
                        label="Search"
                        name="q"
                        type="search"
                        inputmode="search"
                        compact
                        placeholder="Name, place, or description"
                    />
                </div>
                <div class="relative z-10 min-w-0">
                    <UiSelect v-model="form.tag" label="Tag" compact :options="tagOptions" />
                </div>
                <div class="relative z-10 min-w-0">
                    <UiSelect v-model="form.access_mode" label="Access" compact :options="accessOptions" />
                </div>
                <div class="relative z-10 min-w-0">
                    <UiSelect v-model="form.sort" label="Sort" compact :options="sortOptions" />
                </div>
                <div class="flex gap-2 sm:pb-0.5">
                    <button
                        type="submit"
                        class="inline-flex h-11 flex-1 items-center justify-center rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white transition hover:bg-lagoon-900 sm:flex-none"
                    >
                        Apply
                    </button>
                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        class="inline-flex h-11 items-center justify-center rounded-full border border-line bg-paper px-4 font-heading text-sm font-semibold text-ink transition hover:border-lagoon-300"
                        @click="clearFilters"
                    >
                        Clear
                    </button>
                </div>
            </form>

            <section v-if="administered.length" class="mt-12" aria-labelledby="you-run">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 id="you-run" class="font-heading text-xl font-bold tracking-tight text-ink">Communities you run</h2>
                    <span class="rounded-full bg-lagoon-100 px-2.5 py-0.5 text-xs font-semibold text-lagoon-800">
                        {{ administered.length }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-ink-soft">You keep these welcoming and useful.</p>
                <ul class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <li v-for="community in administered" :key="community.id" class="min-w-0">
                        <CommunityCard :community="community" variant="run" />
                    </li>
                </ul>
            </section>

            <section class="mt-12" aria-labelledby="you-belong">
                <h2 id="you-belong" class="font-heading text-xl font-bold tracking-tight text-ink">Communities you’re in</h2>
                <p class="mt-1 text-sm text-ink-soft">Where you post Needs, offer help, and reply to neighbours.</p>

                <ul v-if="memberOf.length" class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <li v-for="community in memberOf" :key="community.id" class="min-w-0">
                        <CommunityCard :community="community" variant="member" />
                    </li>
                </ul>
                <div
                    v-else
                    class="mt-5 flex flex-col items-center rounded-[1.4rem] border border-dashed border-line bg-white px-5 py-12 text-center shadow-card"
                >
                    <span class="grid size-12 place-items-center rounded-full bg-lagoon-50 text-lagoon-700 ring-1 ring-lagoon-100">
                        <BaseIcon name="users" class="size-6" light />
                    </span>
                    <p class="mt-4 text-sm font-semibold text-ink">
                        {{ hasActiveFilters ? 'No joined communities match these filters' : 'You haven’t joined any yet' }}
                    </p>
                    <p class="mt-1 max-w-sm text-sm leading-6 text-ink-soft">
                        {{ hasActiveFilters
                            ? 'Try clearing the filters to see everything you belong to.'
                            : 'Join one below to start posting Needs and answering others — or start your own.' }}
                    </p>
                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        class="mt-5 inline-flex h-10 items-center justify-center rounded-full border border-line bg-white px-5 font-heading text-sm font-semibold text-ink transition hover:border-lagoon-300 hover:bg-lagoon-50"
                        @click="clearFilters"
                    >
                        Clear filters
                    </button>
                    <a
                        v-else
                        href="#discover"
                        class="mt-5 inline-flex h-10 items-center justify-center rounded-full border border-line bg-white px-5 font-heading text-sm font-semibold text-ink transition hover:border-lagoon-300 hover:bg-lagoon-50"
                    >
                        Browse communities
                    </a>
                </div>
            </section>

            <section id="discover" class="mt-14 scroll-mt-24" aria-labelledby="discover-heading">
                <div class="flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <h2 id="discover-heading" class="font-heading text-xl font-bold tracking-tight text-ink">Discover</h2>
                        <p class="mt-1 inline-flex items-center gap-1.5 text-sm text-ink-soft">
                            <BaseIcon name="sparkles" class="size-3.5 text-lagoon-600" />
                            {{ discoverHint }}
                        </p>
                    </div>
                </div>
                <ul v-if="discover.length" class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <li v-for="community in discover" :key="community.id" class="min-w-0">
                        <CommunityCard :community="community" variant="discover" />
                    </li>
                </ul>
                <div v-else class="mt-5 rounded-[1.4rem] border border-dashed border-line bg-white px-5 py-12 text-center shadow-card">
                    <p class="text-sm text-ink-soft">No communities nearby match your interests and filters yet.</p>
                    <Link href="/communities/create" class="mt-3 inline-block text-sm font-semibold text-lagoon-800">
                        Start one for your city
                    </Link>
                </div>
            </section>
        </main>
    </AppNav>
</template>

<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

import AppNav from '@/components/AppNav.vue';
import BaseIcon from '@/components/BaseIcon.vue';
import CommunityCard from '@/components/communities/CommunityCard.vue';
import UiSelect from '@/components/ui/UiSelect.vue';
import UiTextField from '@/components/ui/UiTextField.vue';

type JoinedCommunity = {
    id: number;
    slug: string;
    name: string;
    description: string;
    role: string;
    location_label: string | null;
    members?: number;
    cover_url: string | null;
    tag_keys: string[];
};

const props = defineProps<{
    joined: JoinedCommunity[];
    discover: Array<{
        id: number;
        slug: string;
        name: string;
        description: string;
        members: number;
        access_mode: string;
        location_label: string | null;
        cover_url: string | null;
        tag_keys: string[];
    }>;
    discoverHint: string;
    filters: {
        q: string;
        tag: string | null;
        access_mode: string | null;
        sort: string;
    };
    tags: Record<string, string>;
    accessModes: Record<string, { label: string; description: string }>;
}>();

const page = usePage();
const flash = computed(() => (page.props.flash as { status?: string } | undefined));

const administered = computed(() => props.joined.filter((community) => community.role === 'owner' || community.role === 'moderator'));
const memberOf = computed(() => props.joined.filter((community) => community.role === 'member'));

const form = reactive({
    q: props.filters.q ?? '',
    tag: props.filters.tag ?? '',
    access_mode: props.filters.access_mode ?? '',
    sort: props.filters.sort ?? 'members',
});

const tagOptions = computed(() => [
    { value: '', label: 'All tags' },
    ...Object.entries(props.tags).map(([value, label]) => ({ value, label })),
]);

const accessOptions = computed(() => [
    { value: '', label: 'Any' },
    ...Object.entries(props.accessModes).map(([value, mode]) => ({ value, label: mode.label })),
]);

const sortOptions = [
    { value: 'members', label: 'Most members' },
    { value: 'name', label: 'Name' },
    { value: 'newest', label: 'Newest' },
];

const hasActiveFilters = computed(() =>
    form.q.trim() !== '' || form.tag !== '' || form.access_mode !== '' || form.sort !== 'members',
);

function applyFilters(): void {
    router.get('/communities', {
        q: form.q.trim() || undefined,
        tag: form.tag || undefined,
        access_mode: form.access_mode || undefined,
        sort: form.sort !== 'members' ? form.sort : undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}

function clearFilters(): void {
    form.q = '';
    form.tag = '';
    form.access_mode = '';
    form.sort = 'members';
    router.get('/communities', {}, {
        preserveState: true,
        replace: true,
    });
}
</script>
