<template>
    <div class="min-h-dvh bg-white text-ink">
        <header class="sticky top-0 z-40 border-b border-line/80 bg-white/95 pt-[env(safe-area-inset-top)] backdrop-blur-md">
            <div class="flex h-16 w-full items-center justify-between px-5 sm:px-8 lg:px-10">
                <Link href="/" class="inline-flex items-center gap-2.5 rounded-full" :aria-label="`${appName} home`">
                    <AppMark class="size-9" />
                    <span class="font-heading text-base font-bold tracking-tight text-gray-800">{{ appName }}</span>
                </Link>
                <div class="flex items-center gap-2 sm:gap-3">
                    <Link
                        href="/explore"
                        class="hidden rounded-full px-3.5 py-2 text-sm font-semibold text-gray-600 transition-colors duration-300 hover:bg-gray-50 hover:text-gray-900 sm:inline-flex"
                        :class="exploreActive ? 'bg-lagoon-50 text-lagoon-800' : ''"
                    >
                        Explore
                    </Link>
                    <Link
                        href="/sign-in"
                        class="inline-flex h-10 items-center justify-center rounded-full px-4 text-sm font-semibold text-gray-700 transition-colors duration-300 hover:bg-gray-50"
                    >
                        Log in
                    </Link>
                    <Link
                        href="/join"
                        class="inline-flex h-10 items-center justify-center rounded-full bg-lagoon-800 px-4 text-sm font-semibold text-white transition hover:bg-lagoon-900"
                    >
                        Sign up
                    </Link>
                </div>
            </div>
        </header>

        <slot />
    </div>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppMark from '@/components/AppMark.vue';
import { appName } from '@/lib/app';

const page = usePage();
const exploreActive = computed(() => String(page.url).startsWith('/explore'));
</script>
