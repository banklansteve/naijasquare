<template>
    <section
        class="w-full max-w-full min-w-0 rounded-[1.35rem] border border-line/80 bg-white p-5 shadow-card sm:p-6"
        aria-labelledby="help-with-title"
    >
        <h2 id="help-with-title" class="font-heading text-xl font-bold tracking-tight text-ink">
            What can you help with?
        </h2>
        <p class="mt-1.5 max-w-2xl text-sm leading-6 text-ink-soft">
            Optional — we’ll surface Needs you’re well placed to answer.
        </p>

        <form class="mt-5 space-y-4" @submit.prevent="submit">
            <fieldset class="min-w-0">
                <legend class="sr-only">Help categories</legend>
                <div class="flex flex-wrap gap-2">
                    <label
                        v-for="option in visibleOptions"
                        :key="option.key"
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-full border px-3.5 py-2 text-sm transition duration-300"
                        :class="selected.includes(option.key)
                            ? 'border-lagoon-700 bg-lagoon-100 font-semibold text-lagoon-900'
                            : 'border-line bg-paper text-ink-soft hover:border-lagoon-300 hover:bg-white hover:text-ink'"
                    >
                        <input
                            v-model="selected"
                            type="checkbox"
                            class="sr-only"
                            :value="option.key"
                        >
                        <BaseIcon
                            v-if="selected.includes(option.key)"
                            name="check"
                            bold
                            class="size-3"
                        />
                        {{ option.label }}
                    </label>
                    <button
                        v-if="hasMoreOptions"
                        type="button"
                        class="inline-flex items-center rounded-full border border-dashed border-lagoon-300 bg-white px-3.5 py-2 text-sm font-semibold text-lagoon-800 transition hover:border-lagoon-400"
                        @click="showAll = !showAll"
                    >
                        {{ showAll ? 'Show fewer' : `More (${hiddenCount})` }}
                    </button>
                </div>
                <p v-if="form.errors.help_category_keys" class="mt-2 text-sm text-clay-800">
                    {{ form.errors.help_category_keys }}
                </p>
            </fieldset>

            <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-stretch">
                <label class="block min-w-0 flex-1">
                    <span class="sr-only">A short note</span>
                    <input
                        v-model="form.help_note"
                        type="text"
                        name="help_note"
                        maxlength="240"
                        placeholder="e.g. CV reviews, nursing, housing in Leeds"
                        class="h-12 w-full min-w-0 rounded-full border border-line bg-paper px-5 text-sm text-ink outline-none transition placeholder:text-ink-soft/70 focus:border-lagoon-700 focus:bg-white focus:ring-4 focus:ring-lagoon-100"
                    >
                </label>
                <button
                    type="submit"
                    class="inline-flex h-12 w-full shrink-0 items-center justify-center rounded-full bg-lagoon-800 px-6 font-heading text-sm font-semibold text-white transition hover:bg-lagoon-900 disabled:opacity-60 sm:w-auto sm:min-w-28"
                    :disabled="form.processing || selected.length === 0"
                >
                    Save
                </button>
            </div>
            <p v-if="form.errors.help_note" class="text-sm text-clay-800">{{ form.errors.help_note }}</p>
        </form>
    </section>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

import BaseIcon from '@/components/BaseIcon.vue';
import { primaryHelpOptions } from '@/lib/helpCategories';

const props = defineProps<{
    options: Array<{ key: string; label: string }>;
    categoryKeys: string[];
    note: string | null;
}>();

const selected = ref<string[]>([...props.categoryKeys]);
const showAll = ref(false);

const curated = computed(() => primaryHelpOptions(props.options, selected.value));
const hiddenCount = computed(() => props.options.length - curated.value.length);
const hasMoreOptions = computed(() => hiddenCount.value > 0);
const visibleOptions = computed(() => (showAll.value ? props.options : curated.value));

const form = useForm({
    help_category_keys: [...props.categoryKeys] as string[],
    help_note: props.note ?? '',
});

watch(selected, (keys) => {
    form.help_category_keys = [...keys];
});

function submit(): void {
    form.help_category_keys = [...selected.value];
    form.put('/profile/help-with', { preserveScroll: true });
}
</script>
