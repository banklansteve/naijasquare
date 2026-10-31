<template>
    <article class="flex h-full flex-col rounded-[1.75rem] p-6 sm:p-7">
        <h3 class="text-xl font-semibold tracking-tight text-ink">
            {{ promise.title }}
        </h3>
        <p class="mt-2 text-base leading-relaxed text-ink-soft">
            {{ promise.body }}
        </p>

        <div v-if="promise.notice" class="mt-8 lg:mt-auto lg:pt-8">
            <div class="rounded-2xl bg-white p-3.5 shadow-sm ring-1 ring-black/5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-lagoon-100 text-lagoon-800" aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="9" r="3.2" stroke="currentColor" stroke-width="1.6" />
                                <path d="M6.2 19.2c.8-3.4 2.8-5 5.8-5s5 1.6 5.8 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm leading-snug font-medium text-ink">{{ promise.notice.line }}</p>
                            <p class="mt-1 text-xs text-ink-soft">{{ promise.notice.meta }}</p>
                        </div>
                    </div>
                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-clay-50 px-2 py-1 text-[11px] font-semibold text-clay-800 ring-1 ring-clay-200">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6 3.5v17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            <path d="M6 4.5h12.2L15.2 9l3 4.2H6V4.5Z" fill="currentColor" />
                        </svg>
                        {{ promise.notice.status }}
                    </span>
                </div>
            </div>
        </div>

        <div v-if="promise.badge" class="mt-6 flex justify-center lg:mt-auto lg:pt-6">
            <span class="inline-flex items-center gap-3 rounded-full bg-lagoon-50 py-1.5 pr-5 pl-1.5 text-sm font-semibold text-lagoon-900 ring-1 ring-lagoon-200">
                <span class="grid size-10 place-items-center rounded-full bg-lagoon-700 text-white" aria-hidden="true">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none">
                        <path d="M6.8 12.4 10.2 15.8 17.2 8.4" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                {{ promise.badge }}
            </span>
        </div>

        <ul v-if="promise.modes" class="mt-6 flex flex-wrap items-center gap-x-2 gap-y-2 lg:mt-auto lg:pt-4">
            <li v-for="(mode, index) in promise.modes" :key="mode" class="flex items-center gap-2">
                <span class="inline-flex min-h-10 items-center rounded-full bg-lagoon-50 px-3.5 text-sm font-medium text-lagoon-800">
                    {{ mode }}
                </span>
                <span v-if="index < promise.modes.length - 1" class="text-lagoon-300" aria-hidden="true">·</span>
            </li>
        </ul>

        <div v-if="promise.privacy" class="mt-8 lg:mt-auto lg:pt-8">
            <div class="rounded-2xl bg-white p-3.5 shadow-sm ring-1 ring-black/5">
                <div class="flex items-start gap-3">
                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-lagoon-50 text-lagoon-800" aria-hidden="true">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none">
                            <rect x="5" y="10.5" width="14" height="9.5" rx="2" stroke="currentColor" stroke-width="1.6" />
                            <path d="M8 10.5V7.8a4 4 0 0 1 8 0v2.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm text-ink-soft">{{ promise.privacy.label }}</p>
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-lagoon-50 px-2.5 py-1.5 text-xs font-semibold text-ink ring-1 ring-lagoon-200">
                                {{ promise.privacy.level }}
                                <svg class="size-3 text-ink-soft" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M6 9.5 12 15.5 18 9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <p class="mt-2.5 flex items-center gap-1.5 text-sm font-semibold text-ink">
                            <svg class="size-3.5 text-lagoon-700" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 21s6-5.4 6-10a6 6 0 1 0-12 0c0 4.6 6 10 6 10Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                                <circle cx="12" cy="11" r="1.6" fill="currentColor" />
                            </svg>
                            {{ promise.privacy.place }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </article>
</template>

<script setup lang="ts">
import type { TrustPromise } from '@/types/landing';

defineProps<{
    promise: TrustPromise;
}>();
</script>
