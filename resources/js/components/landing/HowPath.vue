<template>
    <section aria-labelledby="steps-heading" class="overflow-x-clip bg-linear-to-b from-paper via-lagoon-100 to-paper">
        <div class="mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-24">
            <h2 id="steps-heading" class="max-w-xl text-3xl font-semibold tracking-tight text-ink sm:text-4xl">
                {{ how.heading }}
            </h2>
            <p class="mt-3 max-w-xl text-lg leading-relaxed text-ink-soft">
                {{ how.subline }}
            </p>

            <div class="mt-12 lg:mt-16">
                <ol class="relative lg:grid lg:grid-cols-3 lg:items-stretch lg:gap-x-10">
                    <span
                        class="pointer-events-none absolute top-5 bottom-6 left-[1.375rem] w-px -translate-x-1/2 border-l-2 border-dashed border-lagoon-300 lg:hidden"
                        aria-hidden="true"
                    />
                    <li
                        v-for="(step, index) in how.steps"
                        :id="index === 1 ? 'find-communities' : undefined"
                        :key="step.title"
                        class="relative grid grid-cols-[2.75rem_minmax(0,1fr)] gap-x-4 gap-y-6 pb-16 last:pb-0 lg:h-full lg:grid-cols-1 lg:grid-rows-[auto_1fr_auto] lg:gap-y-8 lg:pb-0"
                    >
                        <span
                            v-if="index < how.steps.length - 1"
                            class="pointer-events-none absolute top-[1.375rem] left-[1.375rem] z-0 hidden w-[calc(100%+2.5rem)] -translate-y-1/2 border-t-2 border-dashed border-lagoon-300 lg:block"
                            aria-hidden="true"
                        />
                        <span
                            class="z-10 col-start-1 row-start-1 grid size-11 place-items-center justify-self-center rounded-full text-sm font-semibold shadow-[0_2px_0_rgb(12_44_49/0.18)] ring-4 ring-paper lg:col-start-auto lg:row-start-auto lg:justify-self-start"
                            :class="dots[index]"
                            aria-hidden="true"
                        >
                            {{ index + 1 }}
                        </span>
                        <div class="col-start-2 row-start-1 min-w-0 pt-1.5 lg:col-start-auto lg:row-start-auto lg:pt-0">
                            <h3 class="text-xl font-semibold tracking-tight text-ink">
                                {{ step.title }}
                            </h3>
                            <p class="mt-2 text-base leading-relaxed text-ink-soft">
                                {{ step.body }}
                            </p>
                        </div>
                        <PathScene
                            :screen="step.screen"
                            :tilt="tilts[index]"
                            class="col-span-2 col-start-1 row-start-2 w-full sm:max-w-md lg:col-span-1 lg:col-start-auto lg:row-start-auto lg:max-w-none lg:self-end"
                        />
                    </li>
                </ol>

                <div class="mt-10 flex flex-col items-center text-center lg:mt-14">
                    <p class="mb-4 text-2xl font-medium text-ink-soft">
                        {{ how.note }}
                    </p>
                    <Link
                        :href="how.signupHref"
                        class="inline-flex min-h-11 w-full items-center justify-center rounded-2xl bg-lagoon-700 px-8 py-3 text-base font-semibold text-white shadow-lift transition-[background-color,box-shadow] duration-500 ease-out hover:bg-lagoon-800 hover:shadow-card sm:w-auto"
                    >
                        {{ how.cta }}
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import PathScene from '@/components/landing/PathScene.vue';
import type { HowItWorks } from '@/types/landing';

defineProps<{
    how: HowItWorks;
}>();

const dots = [
    'bg-lagoon-800 text-white',
    'bg-lagoon-300 text-lagoon-900',
    'bg-lagoon-600 text-white',
];

const tilts = [
    'origin-center -rotate-[1.2deg]',
    'origin-center rotate-[1deg]',
    'origin-center -rotate-[0.8deg]',
];
</script>
