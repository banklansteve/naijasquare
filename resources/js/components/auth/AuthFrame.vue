<template>
    <div class="min-h-dvh bg-paper lg:grid lg:grid-cols-[minmax(18rem,0.9fr)_minmax(0,1.1fr)]">
        <aside class="relative overflow-hidden bg-linear-to-b from-lagoon-600 via-lagoon-800 to-lagoon-900 px-6 pt-[max(1.5rem,env(safe-area-inset-top))] pb-8 text-white lg:flex lg:min-h-dvh lg:flex-col lg:px-12 lg:py-14">
            <Link href="/" class="relative inline-flex items-center gap-3 rounded-full">
                <span class="grid size-10 place-items-center rounded-xl bg-white">
                    <AppMark class="size-10" />
                </span>
                <span class="font-heading text-lg font-bold tracking-tight">{{ appName }}</span>
            </Link>
            <div class="relative mt-8 lg:flex lg:flex-1 lg:flex-col lg:items-center lg:justify-center lg:text-center">
                <p v-if="kicker" class="text-sm font-semibold tracking-wide text-lagoon-100">{{ kicker }}</p>
                <h1 class="max-w-md text-4xl font-bold tracking-tight text-balance sm:text-5xl lg:max-w-lg lg:text-6xl lg:leading-[1.05]" :class="kicker ? 'mt-3' : ''">{{ title }}</h1>
            </div>
            <p v-if="step && total" class="relative mt-6 text-sm text-white/75 lg:mt-0 lg:text-center">
                Step {{ step }} of {{ total }}
            </p>
        </aside>
        <main class="px-5 py-8 pb-[max(2rem,env(safe-area-inset-bottom))] sm:px-10 sm:py-12 lg:flex lg:items-center lg:px-16">
            <div class="mx-auto w-full max-w-md">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import AppMark from '@/components/AppMark.vue';
import { appName } from '@/lib/app';

defineProps<{
    kicker: string;
    title: string;
    step?: number;
    total?: number;
}>();
</script>
