<template>
    <section aria-labelledby="values-heading" class="bg-linear-to-b from-paper via-lagoon-100 to-paper">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-24">
            <h2 id="values-heading" class="max-w-xl text-3xl font-semibold tracking-tight text-ink sm:text-4xl">
                {{ values.heading }}
            </h2>
            <p class="mt-3 max-w-xl text-lg leading-relaxed text-ink-soft">
                {{ values.subline }}
            </p>
            <ul class="mt-10 grid grid-cols-1 gap-5 sm:mt-12 lg:grid-cols-3 lg:gap-6">
                <li v-for="point in values.points" :key="point.title" class="min-w-0">
                    <article class="flex h-full items-start gap-4 rounded-[1.75rem] bg-white p-5 shadow-card ring-1 ring-black/5 sm:gap-5 sm:p-7">
                        <span class="grid h-12 w-14 shrink-0 place-items-center rounded-xl bg-lagoon-50 text-lagoon-800 sm:h-14 sm:w-16">
                            <ValueMark :name="point.icon" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-xl font-bold tracking-tight text-ink">
                                {{ point.title }}
                            </h3>
                            <p class="mt-2.5 text-base leading-relaxed text-ink-soft">
                                {{ point.body }}
                            </p>
                        </div>
                    </article>
                </li>
            </ul>
        </div>
    </section>
</template>

<script setup lang="ts">
import ValueMark from '@/components/landing/ValueMark.vue';
import type { ValueSection } from '@/types/landing';

defineProps<{
    values: ValueSection;
}>();
</script>
