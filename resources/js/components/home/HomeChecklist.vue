<template>
    <section
        class="w-full max-w-full overflow-hidden rounded-[1.35rem] border border-lagoon-200/80 bg-lagoon-50"
        aria-labelledby="first-week"
    >
        <!-- Mobile: title + verified mark, checklist, tip with info -->
        <div class="p-5 md:hidden">
            <div class="flex items-start justify-between gap-3">
                <h2 id="first-week" class="font-heading text-xl font-bold tracking-tight text-lagoon-900">
                    Your first week
                </h2>
                <span
                    class="grid size-9 shrink-0 place-items-center rounded-full bg-white text-lagoon-700 ring-1 ring-lagoon-100"
                    aria-hidden="true"
                >
                    <BaseIcon name="verified" class="size-5" bold />
                </span>
            </div>

            <ul class="mt-5 space-y-3.5">
                <li
                    v-for="item in items"
                    :key="item.key"
                    class="flex items-center gap-3.5"
                >
                    <BaseIcon
                        name="verified"
                        bold
                        class="size-6 shrink-0"
                        :class="item.done ? 'text-lagoon-700' : 'text-lagoon-300'"
                    />
                    <span
                        class="text-[15px] leading-6"
                        :class="item.done ? 'text-ink-soft line-through' : 'font-medium text-ink'"
                    >
                        {{ item.label }}
                    </span>
                </li>
            </ul>

            <div
                v-if="showTip"
                class="mt-5 flex items-start gap-2.5 border-t border-lagoon-200/80 pt-4"
            >
                <BaseIcon name="info" class="mt-0.5 size-4 shrink-0 text-lagoon-700" />
                <p class="text-sm leading-6 text-ink-soft">
                    Most people start by joining 1 or 2 communities, then posting or answering a Need.
                </p>
            </div>
        </div>

        <!-- Desktop: verified mark + checklist | tip with bulb -->
        <div class="hidden md:flex md:items-stretch">
            <div class="flex min-w-0 flex-1 items-start gap-5 p-7">
                <span class="grid size-14 shrink-0 place-items-center rounded-full bg-white text-lagoon-700 shadow-[0_1px_2px_rgb(12_44_49/0.06)] ring-1 ring-lagoon-100">
                    <BaseIcon name="verified" class="size-8" bold />
                </span>

                <div class="min-w-0 flex-1">
                    <h2 class="font-heading text-xl font-bold tracking-tight text-ink">
                        Your first week
                    </h2>
                    <ul class="mt-4 space-y-2.5">
                        <li
                            v-for="item in items"
                            :key="`desk-${item.key}`"
                            class="flex items-start gap-3"
                        >
                            <BaseIcon
                                name="verified"
                                bold
                                class="mt-0.5 size-5 shrink-0"
                                :class="item.done ? 'text-lagoon-700' : 'text-lagoon-300'"
                            />
                            <span
                                class="text-sm leading-6"
                                :class="item.done ? 'text-ink-soft line-through' : 'font-medium text-ink'"
                            >
                                {{ item.label }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                v-if="showTip"
                class="flex w-72 shrink-0 items-center border-l border-lagoon-200/90 px-7 py-7"
            >
                <div class="flex items-start gap-3">
                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-white text-lagoon-700 ring-1 ring-lagoon-100">
                        <BaseIcon name="bulb" class="size-5" />
                    </span>
                    <p class="text-sm leading-6 text-ink-soft">
                        <span class="font-semibold text-lagoon-800">Tip:</span>
                        Start small. One post, one connection — that’s enough for week one.
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import BaseIcon from '@/components/BaseIcon.vue';

withDefaults(defineProps<{
    items: Array<{
        key: string;
        label: string;
        done: boolean;
    }>;
    showTip?: boolean;
}>(), {
    showTip: true,
});
</script>
