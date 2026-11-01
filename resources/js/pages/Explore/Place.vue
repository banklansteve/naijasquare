<template>
    <Head :title="`Communities in ${place.label}`">
        <meta
            head-key="description"
            name="description"
            :content="`Discover Nigerian diaspora communities in ${place.label}. Find groups for housing, professionals, new arrivals, and local support.`"
        />
    </Head>

    <PublicShell>
        <main class="mx-auto w-full max-w-5xl px-5 pt-8 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-12">
            <p class="text-sm font-medium text-ink-soft">
                <Link href="/explore" class="text-lagoon-800 hover:underline">Explore</Link>
                <span class="mx-1.5 text-gray-300">/</span>
                {{ place.label }}
            </p>

            <header class="mt-4 pb-8">
                <h1 class="font-heading text-3xl font-bold tracking-tight text-gray-800 sm:text-[2.5rem]">
                    Communities in {{ place.label }}
                </h1>
                <p class="mt-3 max-w-2xl text-base text-gray-600">
                    Public groups near {{ place.label }} you can join or request to join.
                </p>
            </header>

            <ul v-if="communities.length" class="grid gap-4 sm:grid-cols-2">
                <li
                    v-for="community in communities"
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
                            <h2 class="font-heading text-base font-bold text-gray-800">{{ community.name }}</h2>
                            <p class="mt-1.5 line-clamp-2 text-sm text-gray-600">{{ community.description }}</p>
                            <p class="mt-3 text-xs font-medium text-ink-soft">
                                {{ community.members }} members
                                <template v-if="community.location_label"> · {{ community.location_label }}</template>
                            </p>
                        </div>
                    </Link>
                </li>
            </ul>
        </main>
    </PublicShell>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import CommunityCoverMedia from '@/components/communities/CommunityCoverMedia.vue';
import PublicShell from '@/components/PublicShell.vue';

defineProps<{
    place: { slug: string; label: string };
    communities: Array<{
        id: number;
        slug: string;
        name: string;
        description: string;
        members: number;
        location_label: string | null;
        cover_url: string | null;
        tag_keys: string[];
    }>;
}>();
</script>
