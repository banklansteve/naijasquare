<template>
    <Head title="Post removed" />

    <AppNav>
        <main class="mx-auto w-full max-w-3xl px-5 pt-8 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-12">
            <p class="text-sm font-medium text-ink-soft">
                <Link :href="`/communities/${community.slug}`" class="text-lagoon-800 hover:underline">
                    {{ community.name }}
                </Link>
            </p>

            <div class="mt-8 rounded-[1.35rem] border border-line bg-white px-5 py-10 text-center shadow-card sm:px-8">
                <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">
                    This post was removed
                </h1>
                <p class="mt-3 text-sm leading-6 text-ink-soft">
                    Community admins removed this Need or Offer after a report. It is no longer visible to members.
                </p>
                <Link
                    :href="`/communities/${community.slug}`"
                    class="mt-6 inline-flex h-11 items-center justify-center rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white"
                >
                    Back to community
                </Link>
            </div>
        </main>
    </AppNav>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import AppNav from '@/components/AppNav.vue';

defineProps<{
    community: {
        slug: string;
        name: string;
    };
}>();
</script>
