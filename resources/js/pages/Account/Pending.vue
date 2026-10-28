<template>
    <Head :title="title" />

    <div class="flex min-h-dvh flex-col px-6 pt-[max(1.25rem,env(safe-area-inset-top))] pb-[max(1.75rem,env(safe-area-inset-bottom))]">
        <header class="mx-auto flex w-full max-w-md items-center">
            <Link
                href="/"
                class="-ml-2 inline-flex items-center gap-1.5 rounded-full px-2 py-2 text-sm font-semibold text-lagoon-700 hover:bg-lagoon-100"
            >
                <BaseIcon name="arrow" class="size-4 rotate-180" />
                Back
            </Link>
        </header>

        <main class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center text-center">
            <AppMark class="animate-settle mx-auto size-16" />
            <h1 class="mt-6 text-2xl font-bold tracking-tight text-ink">{{ title }}</h1>
            <p class="mt-3 leading-relaxed text-ink-soft">{{ body }}</p>
        </main>
    </div>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import AppMark from '@/components/AppMark.vue';
import BaseIcon from '@/components/BaseIcon.vue';

defineProps<{
    intent: 'join' | 'sign-in';
    title: string;
    body: string;
}>();
</script>
