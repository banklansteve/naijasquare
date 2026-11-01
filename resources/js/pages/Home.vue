<template>
    <Head title="Home" />

    <AppNav>
        <main class="mx-auto w-full max-w-5xl px-5 pt-8 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-12">
            <header class="pb-8">
                <h1 class="font-heading text-3xl font-bold tracking-tight text-balance text-gray-800 sm:text-[2.5rem] sm:leading-tight">
                    Welcome, {{ firstName }}.
                </h1>
                <p class="mt-3 max-w-xl text-lg font-normal leading-relaxed text-gray-600">
                    <template v-if="city">You're in {{ city }}. </template>Let's get you connected to the right people and help.
                </p>
            </header>

            <div class="border-t border-gray-200/90" />

            <section class="mt-8" aria-labelledby="start-here">
                <h2 id="start-here" class="text-xl font-semibold tracking-wide text-gray-700">
                    Start here
                </h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-3 sm:gap-4">
                    <Link
                        href="/communities"
                        class="group flex min-h-44 flex-col rounded-[1.35rem] border border-gray-200/80 bg-white p-5 shadow-[0_1px_2px_rgb(15_23_42/0.04),0_12px_28px_-18px_rgb(15_23_42/0.18)] transition duration-500 ease-out hover:shadow-[0_2px_8px_rgb(15_23_42/0.05),0_18px_36px_-18px_rgb(15_23_42/0.22)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lagoon-700 sm:p-6"
                    >
                        <span class="grid size-14 place-items-center rounded-full bg-lagoon-100 text-lagoon-700">
                            <BaseIcon name="users" class="size-7" />
                        </span>
                        <span class="mt-5 font-heading text-base font-bold tracking-tight text-gray-800">Find your communities</span>
                        <span class="mt-2 text-sm font-normal leading-6 text-gray-600">Join groups that match your location and interests.</span>
                    </Link>
                    <Link
                        href="/needs"
                        class="group flex min-h-44 flex-col rounded-[1.35rem] border border-gray-200/80 bg-white p-5 shadow-[0_1px_2px_rgb(15_23_42/0.04),0_12px_28px_-18px_rgb(15_23_42/0.18)] transition duration-500 ease-out hover:shadow-[0_2px_8px_rgb(15_23_42/0.05),0_18px_36px_-18px_rgb(15_23_42/0.22)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lagoon-700 sm:p-6"
                    >
                        <span class="grid size-14 place-items-center rounded-full bg-lagoon-100 text-lagoon-700">
                            <BaseIcon name="help" class="size-7" />
                        </span>
                        <span class="mt-5 font-heading text-base font-bold tracking-tight text-gray-800">Ask for help</span>
                        <span class="mt-2 text-sm font-normal leading-6 text-gray-600">Post a Need — housing, jobs, advice, services…</span>
                    </Link>
                    <Link
                        href="/resources"
                        class="group flex min-h-44 flex-col rounded-[1.35rem] border border-gray-200/80 bg-white p-5 shadow-[0_1px_2px_rgb(15_23_42/0.04),0_12px_28px_-18px_rgb(15_23_42/0.18)] transition duration-500 ease-out hover:shadow-[0_2px_8px_rgb(15_23_42/0.05),0_18px_36px_-18px_rgb(15_23_42/0.22)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lagoon-700 sm:p-6"
                    >
                        <span class="grid size-14 place-items-center rounded-full bg-lagoon-100 text-lagoon-700">
                            <BaseIcon name="book" class="size-7" />
                        </span>
                        <span class="mt-5 font-heading text-base font-bold tracking-tight text-gray-800">Explore local resources</span>
                        <span class="mt-2 text-sm font-normal leading-6 text-gray-600">See trusted recommendations near you.</span>
                    </Link>
                </div>
            </section>

            <section id="communities" class="mt-12 scroll-mt-24" aria-labelledby="communities-for-you">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 id="communities-for-you" class="text-xl font-semibold tracking-wide text-gray-700">
                        Communities for you
                    </h2>
                    <div class="flex flex-wrap gap-3 text-sm font-semibold">
                        <Link href="/communities" class="text-lagoon-800">Browse</Link>
                        <Link href="/communities/create" class="text-lagoon-800">Create</Link>
                    </div>
                </div>

                <ul
                    v-if="communities.length"
                    class="mt-5 grid gap-4 sm:grid-cols-2"
                >
                    <li
                        v-for="community in communities"
                        :key="community.id"
                        class="overflow-hidden rounded-[1.35rem] border border-gray-200/80 bg-white shadow-[0_1px_2px_rgb(15_23_42/0.04),0_12px_28px_-18px_rgb(15_23_42/0.18)]"
                    >
                        <CommunityCoverMedia
                            :name="community.name"
                            :cover-url="community.cover_url"
                            :tag-keys="community.tag_keys"
                        >
                            <span class="absolute -bottom-5 left-4 grid size-11 place-items-center rounded-full bg-gray-800 text-white shadow-md ring-4 ring-white">
                                <BaseIcon name="place-community" class="size-5" />
                            </span>
                        </CommunityCoverMedia>

                        <div class="flex flex-1 flex-col px-4 pt-8 pb-4 sm:px-5 sm:pb-5">
                            <h3 class="font-heading text-base font-bold tracking-tight text-gray-800">{{ community.name }}</h3>
                            <p class="mt-1.5 line-clamp-2 text-sm font-normal leading-6 text-gray-600">{{ community.description }}</p>
                            <p class="mt-3 flex items-center gap-1.5 text-xs font-medium text-gray-500">
                                <BaseIcon name="users" class="size-3.5 shrink-0" />
                                <span>{{ membersLabel(community.members) }}</span>
                            </p>
                            <Link
                                :href="`/communities/${community.slug}`"
                                class="mt-4 inline-flex h-11 w-full items-center justify-center rounded-full bg-lagoon-800 font-heading text-sm font-semibold text-white transition duration-500 ease-out hover:bg-lagoon-900"
                            >
                                {{ community.access_mode === 'open' ? 'View & join' : 'Request to join' }}
                            </Link>
                        </div>
                    </li>
                </ul>

                <div
                    v-else
                    class="mt-5 rounded-[1.35rem] border border-gray-200/80 bg-white px-6 py-10 text-center shadow-[0_1px_2px_rgb(15_23_42/0.04),0_12px_28px_-18px_rgb(15_23_42/0.18)] sm:px-10"
                >
                    <CommunitiesEmptyArt />
                    <p class="mx-auto mt-6 max-w-sm text-sm font-normal leading-6 text-gray-600">
                        We'll show relevant communities here once they match your city and interests.
                    </p>
                    <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                        <Link
                            href="/communities/create"
                            class="inline-flex h-11 items-center justify-center rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white"
                        >
                            Create a community
                        </Link>
                        <Link
                            href="/communities"
                            class="inline-flex h-11 items-center justify-center rounded-full border border-line bg-white px-5 font-heading text-sm font-semibold text-gray-800"
                        >
                            Browse communities
                        </Link>
                    </div>
                </div>
            </section>

            <div class="mt-10 flex items-start gap-2.5 rounded-2xl border border-gray-200/70 bg-white/70 px-4 py-3.5">
                <BaseIcon name="info" class="mt-0.5 size-4 shrink-0 text-gray-400" />
                <p class="text-sm font-normal leading-6 text-gray-500">
                    Most people start by joining 1 or 2 communities, then posting or answering a Need.
                </p>
            </div>
        </main>
    </AppNav>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import AppNav from '@/components/AppNav.vue';
import BaseIcon from '@/components/BaseIcon.vue';
import CommunityCoverMedia from '@/components/communities/CommunityCoverMedia.vue';
import CommunitiesEmptyArt from '@/components/home/CommunitiesEmptyArt.vue';

defineProps<{
    firstName: string;
    city: string | null;
    communities: Array<{
        id: number;
        slug: string;
        name: string;
        description: string;
        members: number;
        access_mode: string;
        cover_url: string | null;
        tag_keys: string[];
    }>;
}>();

function membersLabel(count: number): string {
    if (count >= 1000) {
        const thousands = count / 1000;

        return `${thousands % 1 === 0 ? thousands.toFixed(0) : thousands.toFixed(1)}k members`;
    }

    return `${count} ${count === 1 ? 'member' : 'members'}`;
}
</script>
