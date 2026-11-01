<template>
    <Head title="Communities" />

    <AppNav>
        <main class="mx-auto w-full max-w-5xl px-5 pt-8 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-12">
            <p v-if="flash?.status" class="mb-6 rounded-2xl border border-lagoon-200 bg-lagoon-50 px-4 py-3 text-sm text-lagoon-900">
                {{ flash.status }}
            </p>

            <header class="flex flex-wrap items-end justify-between gap-4 pb-8">
                <div>
                    <h1 class="font-heading text-3xl font-bold tracking-tight text-gray-800 sm:text-[2.5rem]">Communities</h1>
                    <p class="mt-3 max-w-xl text-base text-gray-600">Your groups, and ones you can discover nearby.</p>
                </div>
                <Link
                    href="/communities/create"
                    class="inline-flex h-11 items-center justify-center rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white"
                >
                    Create community
                </Link>
            </header>

            <form class="flex flex-col gap-3 overflow-visible rounded-[1.35rem] border border-gray-200/80 bg-white p-4 sm:p-5" @submit.prevent="applyFilters">
                <div class="min-w-0 w-full">
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
                <div class="grid grid-cols-1 gap-3 overflow-visible sm:grid-cols-3 sm:items-end">
                    <div class="relative z-10 min-w-0">
                        <UiSelect
                            v-model="form.tag"
                            label="Tag"
                            compact
                            :options="tagOptions"
                        />
                    </div>
                    <div class="relative z-10 min-w-0">
                        <UiSelect
                            v-model="form.access_mode"
                            label="Access"
                            compact
                            :options="accessOptions"
                        />
                    </div>
                    <div class="relative z-10 min-w-0">
                        <UiSelect
                            v-model="form.sort"
                            label="Sort"
                            compact
                            :options="sortOptions"
                        />
                    </div>
                </div>
                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white"
                    >
                        Apply
                    </button>
                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        class="inline-flex h-11 items-center justify-center rounded-full border border-line bg-white px-5 font-heading text-sm font-semibold text-gray-800"
                        @click="clearFilters"
                    >
                        Clear
                    </button>
                </div>
            </form>

            <section class="mt-10" aria-labelledby="yours">
                <h2 id="yours" class="text-xl font-semibold tracking-wide text-gray-700">Yours</h2>
                <ul v-if="joined.length" class="mt-4 grid gap-4 sm:grid-cols-2">
                    <li
                        v-for="community in joined"
                        :key="community.id"
                        class="overflow-hidden rounded-[1.35rem] border border-gray-200/80 bg-white shadow-[0_1px_2px_rgb(15_23_42/0.04),0_12px_28px_-18px_rgb(15_23_42/0.18)]"
                    >
                        <Link :href="`/communities/${community.slug}`" class="block">
                            <CommunityCoverMedia
                                :name="community.name"
                                :cover-url="community.cover_url"
                                :tag-keys="community.tag_keys"
                            />
                            <div class="px-4 py-4 sm:px-5">
                                <h3 class="font-heading text-base font-bold text-gray-800">{{ community.name }}</h3>
                                <p class="mt-1.5 line-clamp-2 text-sm text-gray-600">{{ community.description }}</p>
                                <p class="mt-3 text-xs font-medium text-ink-soft">
                                    {{ community.location_label }}
                                    <template v-if="community.members != null"> · {{ community.members }} members</template>
                                </p>
                            </div>
                        </Link>
                    </li>
                </ul>
                <div v-else class="mt-4 rounded-[1.35rem] border border-dashed border-gray-200 bg-white/70 px-5 py-8 text-center">
                    <p class="text-sm text-gray-500">No communities in Yours match these filters.</p>
                    <Link href="/communities/create" class="mt-3 inline-block text-sm font-semibold text-lagoon-800">
                        Create one
                    </Link>
                </div>
            </section>

            <section class="mt-12" aria-labelledby="discover">
                <div class="flex flex-wrap items-end justify-between gap-2">
                    <h2 id="discover" class="text-xl font-semibold tracking-wide text-gray-700">Discover</h2>
                    <p class="text-sm text-ink-soft">{{ discoverHint }}</p>
                </div>
                <ul v-if="discover.length" class="mt-4 grid gap-4 sm:grid-cols-2">
                    <li
                        v-for="community in discover"
                        :key="community.id"
                        class="overflow-hidden rounded-[1.35rem] border border-gray-200/80 bg-white shadow-[0_1px_2px_rgb(15_23_42/0.04),0_12px_28px_-18px_rgb(15_23_42/0.18)]"
                    >
                        <CommunityCoverMedia
                            :name="community.name"
                            :cover-url="community.cover_url"
                            :tag-keys="community.tag_keys"
                        />
                        <div class="px-4 pt-4 pb-4 sm:px-5 sm:pb-5">
                            <h3 class="font-heading text-base font-bold text-gray-800">{{ community.name }}</h3>
                            <p class="mt-1.5 line-clamp-2 text-sm text-gray-600">{{ community.description }}</p>
                            <p class="mt-3 text-xs font-medium text-ink-soft">
                                {{ community.members }} members · {{ community.location_label }}
                            </p>
                            <Link
                                :href="`/communities/${community.slug}`"
                                class="mt-4 inline-flex h-11 w-full items-center justify-center rounded-full bg-lagoon-800 font-heading text-sm font-semibold text-white transition hover:bg-lagoon-900"
                            >
                                {{ community.access_mode === 'open' ? 'View & join' : 'Request to join' }}
                            </Link>
                        </div>
                    </li>
                </ul>
                <div v-else class="mt-4 rounded-[1.35rem] border border-dashed border-gray-200 bg-white/70 px-5 py-8 text-center">
                    <p class="text-sm text-gray-500">No communities nearby match your interests and filters yet.</p>
                </div>
            </section>
        </main>
    </AppNav>
</template>

<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

import AppNav from '@/components/AppNav.vue';
import CommunityCoverMedia from '@/components/communities/CommunityCoverMedia.vue';
import UiSelect from '@/components/ui/UiSelect.vue';
import UiTextField from '@/components/ui/UiTextField.vue';

const props = defineProps<{
    joined: Array<{
        id: number;
        slug: string;
        name: string;
        description: string;
        role: string;
        location_label: string | null;
        members?: number;
        cover_url: string | null;
        tag_keys: string[];
    }>;
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
