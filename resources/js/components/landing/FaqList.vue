<template>
    <section aria-labelledby="faq-heading">
        <div class="mx-auto max-w-4xl px-5 py-16 sm:px-8 sm:py-24">
            <h2 id="faq-heading" class="text-3xl font-semibold tracking-tight text-ink sm:text-4xl">
                {{ faq.heading }}
            </h2>
            <p class="mt-3 max-w-xl text-lg leading-relaxed text-ink-soft">
                {{ faq.subline }}
            </p>

            <div class="mt-10 overflow-hidden rounded-[1.75rem] bg-white shadow-card ring-1 ring-black/5 sm:mt-12">
                <div
                    v-for="(item, index) in faq.items"
                    :key="item.question"
                    class="border-b border-line last:border-b-0"
                >
                    <h3>
                        <button
                            type="button"
                            class="flex min-h-14 w-full items-center justify-between gap-4 px-5 py-4 text-left transition-colors duration-500 ease-out hover:bg-lagoon-50/50 sm:px-8 sm:py-5"
                            :aria-expanded="open === index"
                            :aria-controls="`faq-${index}`"
                            @click="toggle(index)"
                        >
                            <span class="text-base font-semibold tracking-tight text-ink sm:text-lg">{{ item.question }}</span>
                            <span
                                class="grid size-8 shrink-0 place-items-center rounded-full bg-lagoon-50 text-lagoon-800 transition-transform duration-500 ease-out motion-reduce:transition-none"
                                :class="open === index ? 'rotate-180' : ''"
                                aria-hidden="true"
                            >
                                <svg class="size-4" viewBox="0 0 24 24" fill="none">
                                    <path d="M6 9.5 12 15.5 18 9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </button>
                    </h3>
                    <div
                        :id="`faq-${index}`"
                        class="grid transition-[grid-template-rows] duration-500 ease-out motion-reduce:transition-none"
                        :class="open === index ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
                    >
                        <div class="min-h-0 overflow-hidden" :inert="open !== index">
                            <p class="px-5 pb-5 text-base leading-relaxed text-ink-soft sm:px-8 sm:pb-6">
                                {{ item.answer }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref } from 'vue';

import type { FaqSection } from '@/types/landing';

defineProps<{
    faq: FaqSection;
}>();

const open = ref<number | null>(null);

function toggle(index: number): void {
    open.value = open.value === index ? null : index;
}
</script>
