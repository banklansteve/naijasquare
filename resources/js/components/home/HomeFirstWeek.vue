<template>
    <section
        v-if="firstWeek"
        class="relative w-full max-w-full overflow-hidden rounded-[1.35rem] border border-lagoon-200/80 bg-lagoon-50"
        aria-labelledby="first-week-focus"
    >
        <div class="p-5 sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 id="first-week-focus" class="font-heading text-xl font-bold tracking-tight text-lagoon-900">
                        Your first week
                    </h2>
                    <p class="mt-1 text-sm text-lagoon-800/80">
                        {{ firstWeek.done_count }} of {{ firstWeek.total }} done
                    </p>
                </div>
                <button
                    type="button"
                    class="inline-flex size-9 shrink-0 items-center justify-center rounded-full text-lagoon-700 transition hover:bg-white/80"
                    aria-label="Dismiss for now"
                    :disabled="dismissing"
                    @click="dismiss"
                >
                    <BaseIcon name="close-sm" class="size-4" />
                </button>
            </div>

            <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/80 ring-1 ring-lagoon-100">
                <div
                    class="h-full rounded-full bg-lagoon-700 transition-[width] duration-500 ease-out"
                    :style="{ width: `${Math.round(firstWeek.progress * 100)}%` }"
                />
            </div>

            <ul class="mt-5 space-y-3">
                <li
                    v-for="step in firstWeek.steps"
                    :key="step.key"
                    class="rounded-2xl bg-white/70 p-3.5 ring-1 ring-lagoon-100/80"
                    :class="step.key === firstWeek.active_key ? 'ring-lagoon-300' : ''"
                >
                    <div class="flex items-start gap-3">
                        <BaseIcon
                            name="verified"
                            bold
                            class="mt-0.5 size-5 shrink-0"
                            :class="step.done ? 'text-lagoon-700' : 'text-lagoon-300'"
                        />
                        <div class="min-w-0 flex-1">
                            <p
                                class="text-sm leading-6"
                                :class="step.done ? 'text-ink-soft line-through' : 'font-semibold text-ink'"
                            >
                                {{ step.label }}
                            </p>

                            <div
                                v-if="!step.done && step.key === firstWeek.active_key"
                                class="mt-3"
                            >
                                <template v-if="step.kind === 'help_with'">
                                    <div class="flex flex-wrap gap-2">
                                        <label
                                            v-for="option in helpWith.options"
                                            :key="option.key"
                                            class="inline-flex cursor-pointer items-center rounded-full border px-3 py-1.5 text-xs transition"
                                            :class="selected.includes(option.key)
                                                ? 'border-lagoon-700 bg-lagoon-100 font-semibold text-lagoon-900'
                                                : 'border-line bg-white text-ink-soft hover:border-lagoon-300'"
                                        >
                                            <input
                                                v-model="selected"
                                                type="checkbox"
                                                class="sr-only"
                                                :value="option.key"
                                            >
                                            {{ option.label }}
                                        </label>
                                    </div>
                                    <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                                        <input
                                            v-model="form.help_note"
                                            type="text"
                                            maxlength="240"
                                            placeholder="Optional note — e.g. CV reviews"
                                            class="h-10 min-w-0 flex-1 rounded-full border border-line bg-white px-4 text-sm text-ink outline-none focus:border-lagoon-700 focus:ring-4 focus:ring-lagoon-100"
                                        >
                                        <button
                                            type="button"
                                            class="inline-flex h-10 items-center justify-center rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white disabled:opacity-60"
                                            :disabled="form.processing || selected.length === 0"
                                            @click="saveHelpWith"
                                        >
                                            {{ step.cta_label }}
                                        </button>
                                    </div>
                                </template>
                                <Link
                                    v-else-if="step.cta_href"
                                    :href="step.cta_href"
                                    class="inline-flex h-10 items-center justify-center rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white"
                                >
                                    {{ step.cta_label }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>

            <p class="mt-4 text-xs text-lagoon-800/70">
                Not now is fine — you can come back any time.
            </p>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import BaseIcon from '@/components/BaseIcon.vue';

const props = defineProps<{
    firstWeek: {
        done_count: number;
        total: number;
        progress: number;
        active_key: string | null;
        steps: Array<{
            key: string;
            label: string;
            done: boolean;
            cta_label: string;
            cta_href: string | null;
            kind: string;
        }>;
    } | null;
    helpWith: {
        category_keys: string[];
        note: string | null;
        options: Array<{ key: string; label: string }>;
    };
}>();

const selected = ref<string[]>([...props.helpWith.category_keys]);
const dismissing = ref(false);

const form = useForm({
    help_category_keys: [...props.helpWith.category_keys] as string[],
    help_note: props.helpWith.note ?? '',
});

watch(selected, (keys) => {
    form.help_category_keys = [...keys];
});

function saveHelpWith(): void {
    form.help_category_keys = [...selected.value];
    form.put('/profile/help-with', { preserveScroll: true });
}

function dismiss(): void {
    dismissing.value = true;
    router.post('/home/checklist/dismiss', {}, {
        preserveScroll: true,
        onFinish: () => {
            dismissing.value = false;
        },
    });
}
</script>
