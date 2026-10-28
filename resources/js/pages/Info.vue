<template>
    <Head :title="title" />

    <div class="min-h-dvh px-6 pt-[max(1.25rem,env(safe-area-inset-top))] pb-[max(2rem,env(safe-area-inset-bottom))]">
        <header class="mx-auto flex w-full max-w-xl items-center">
            <Link
                href="/"
                class="-ml-2 inline-flex items-center gap-1.5 rounded-full px-2 py-2 text-sm font-semibold text-lagoon-700 hover:bg-white"
            >
                <BaseIcon name="arrow" class="size-4 rotate-180" />
                Back
            </Link>
        </header>

        <main class="mx-auto mt-10 w-full max-w-xl">
            <h1 class="text-3xl font-bold tracking-tight text-ink">{{ title }}</h1>
            <div class="mt-6 space-y-4 text-base leading-relaxed text-ink-soft">
                <p v-for="paragraph in paragraphs" :key="paragraph">{{ paragraph }}</p>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import BaseIcon from '@/components/BaseIcon.vue';

defineProps<{
    title: string;
    paragraphs: string[];
}>();
</script>
