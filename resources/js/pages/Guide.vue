<template>
    <Head :title="title" />

    <AppNav>
        <main class="mx-auto flex w-full max-w-lg flex-col px-5 pt-12 pb-[max(3rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-20">
            <h1 class="font-heading text-3xl font-bold tracking-tight text-gray-800 sm:text-4xl">{{ title }}</h1>
            <p class="mt-4 text-lg font-normal leading-relaxed text-gray-600">{{ body }}</p>
            <Link
                href="/home"
                class="mt-8 inline-flex h-11 items-center self-start rounded-full px-1 font-heading text-sm font-semibold text-lagoon-700"
            >
                Back to home
            </Link>
        </main>
    </AppNav>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import AppNav from '@/components/AppNav.vue';

defineProps<{
    title: string;
    body: string;
}>();
</script>
