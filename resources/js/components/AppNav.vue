<template>
    <div class="min-h-dvh bg-paper text-ink">
        <header
            class="sticky top-0 z-30 border-b border-line/80 bg-paper/90 backdrop-blur-md transition-shadow duration-300"
            :class="scrolled ? 'shadow-[0_10px_28px_-18px_rgb(12_44_49/0.45)]' : 'shadow-none'"
        >
            <div class="mx-auto flex h-16 w-full max-w-5xl items-center px-5 pt-[env(safe-area-inset-top)] sm:px-8">
                <Link href="/home" class="inline-flex items-center gap-2.5 rounded-full py-1 pr-2">
                    <AppMark class="size-9" />
                    <span class="font-heading text-base font-bold tracking-tight text-gray-800">{{ appName }}</span>
                </Link>
            </div>
        </header>

        <slot />
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

import AppMark from '@/components/AppMark.vue';
import { appName } from '@/lib/app';

const scrolled = ref(false);

function onScroll(): void {
    scrolled.value = window.scrollY > 8;
}

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
});
</script>
