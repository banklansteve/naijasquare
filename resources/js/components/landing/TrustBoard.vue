<template>
    <section aria-labelledby="trust-heading">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-24">
            <h2 id="trust-heading" class="max-w-3xl text-3xl font-semibold tracking-tight text-ink sm:text-4xl">
                {{ trust.heading }}
            </h2>
            <p class="mt-3 max-w-xl text-lg leading-relaxed text-ink-soft">
                {{ trust.subline }}
            </p>

            <ul class="mt-10 grid grid-cols-1 gap-4 sm:mt-12 sm:gap-5 lg:grid-cols-2">
                <li v-for="promise in trust.promises" :key="promise.title" class="min-w-0">
                    <TrustFace
                        :promise="promise"
                        class="h-full shadow-card ring-1 ring-black/5"
                        :class="promise.notice ? 'bg-clay-100' : promise.privacy ? 'bg-lagoon-50' : 'bg-white'"
                    />
                </li>
            </ul>
        </div>
    </section>
</template>

<script setup lang="ts">
import TrustFace from '@/components/landing/TrustFace.vue';
import type { TrustSection } from '@/types/landing';

defineProps<{
    trust: TrustSection;
}>();
</script>
