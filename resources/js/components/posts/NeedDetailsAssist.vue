<template>
    <div class="space-y-3">
        <Transition
            enter-active-class="transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-250 ease-out motion-reduce:transition-none"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="-translate-y-1 opacity-0"
            mode="out-in"
        >
            <div
                v-if="loading"
                key="loading"
                class="overflow-hidden rounded-[1.35rem] border border-lagoon-200/70 bg-gradient-to-br from-lagoon-50 via-white to-moss-50/40 p-4 shadow-card sm:p-5"
                role="status"
                aria-live="polite"
            >
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex size-9 shrink-0 items-center justify-center rounded-2xl bg-lagoon-800 text-white shadow-sm">
                        <BaseIcon name="bulb" class="size-4" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="font-heading text-sm font-bold tracking-tight text-lagoon-950">
                            Drafting starter details…
                        </p>
                        <p class="mt-1 text-sm leading-6 text-ink-soft">
                            Based on your title and category — you can edit everything.
                        </p>
                        <div class="mt-4 space-y-2" aria-hidden="true">
                            <div class="h-2.5 w-[92%] animate-pulse rounded-full bg-lagoon-100/90" />
                            <div class="h-2.5 w-[78%] animate-pulse rounded-full bg-lagoon-100/70" />
                            <div class="h-2.5 w-[64%] animate-pulse rounded-full bg-lagoon-100/50" />
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else-if="visible && text"
                key="ready"
                class="animate-settle overflow-hidden rounded-[1.35rem] border border-lagoon-200/80 bg-gradient-to-br from-white via-lagoon-50/50 to-moss-50/30 shadow-lift"
                role="region"
                aria-labelledby="need-details-assist-heading"
            >
                <div class="border-b border-lagoon-100/80 bg-white/70 px-4 py-3.5 sm:px-5">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex size-9 shrink-0 items-center justify-center rounded-2xl bg-lagoon-800 text-white shadow-sm">
                            <BaseIcon name="bulb" class="size-4" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3
                                    id="need-details-assist-heading"
                                    class="font-heading text-sm font-bold tracking-tight text-lagoon-950 sm:text-base"
                                >
                                    Suggested details
                                </h3>
                                <span class="rounded-full bg-moss-100 px-2.5 py-0.5 text-[11px] font-semibold tracking-wide text-moss-800 uppercase">
                                    Optional
                                </span>
                            </div>
                            <p class="mt-1 text-sm leading-6 text-ink-soft">
                                A calm starter from your title — use it, edit it, or skip.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-4 sm:px-5 sm:py-5">
                    <div class="rounded-2xl border border-line/80 bg-white/90 px-4 py-3.5">
                        <p class="whitespace-pre-wrap text-sm leading-7 text-ink">
                            {{ text }}
                        </p>
                    </div>

                    <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                        <button
                            type="button"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white shadow-sm transition hover:bg-lagoon-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lagoon-700"
                            @click="$emit('use')"
                        >
                            <BaseIcon name="check" class="size-4" />
                            Use suggestion
                        </button>
                        <button
                            type="button"
                            class="inline-flex h-11 items-center justify-center rounded-full border border-line bg-white px-5 font-heading text-sm font-semibold text-ink transition hover:border-lagoon-300 hover:bg-lagoon-50/60 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lagoon-700"
                            @click="$emit('dismiss')"
                        >
                            Write my own
                        </button>
                    </div>
                </div>
            </div>

        </Transition>
    </div>
</template>

<script setup lang="ts">
import BaseIcon from '@/components/BaseIcon.vue';

defineProps<{
    loading: boolean;
    visible: boolean;
    text: string;
}>();

defineEmits<{
    use: [];
    dismiss: [];
}>();
</script>
