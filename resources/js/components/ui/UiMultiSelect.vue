<template>
    <div ref="root" class="relative block w-full min-w-0" :class="open ? 'z-50' : 'z-0'">
        <span v-if="label" class="text-sm font-semibold text-ink">
            {{ label }}
            <span v-if="optional" class="font-medium text-ink-soft">Optional</span>
        </span>

        <div
            class="rounded-2xl border bg-white px-3 py-2 outline-none transition-[border-color,box-shadow] duration-300"
            :class="[
                label ? 'mt-2' : '',
                open || error
                    ? error
                        ? 'border-clay-200 ring-4 ring-clay-100'
                        : 'border-lagoon-700 ring-4 ring-lagoon-100'
                    : 'border-line hover:border-lagoon-300',
                disabled ? 'cursor-not-allowed bg-gray-50 opacity-70' : '',
            ]"
        >
            <div v-if="selectedOptions.length" class="mb-2 flex flex-wrap gap-1.5">
                <button
                    v-for="option in selectedOptions"
                    :key="String(option.value)"
                    type="button"
                    class="inline-flex max-w-full items-center gap-1.5 rounded-full bg-lagoon-100 px-2.5 py-1 text-xs font-semibold text-lagoon-950"
                    :disabled="disabled"
                    @click="remove(option.value)"
                >
                    <span class="truncate">{{ option.label }}</span>
                    <span aria-hidden="true" class="text-lagoon-800">×</span>
                    <span class="sr-only">Remove {{ option.label }}</span>
                </button>
            </div>

            <button
                :id="buttonId"
                type="button"
                class="flex w-full items-center justify-between gap-3 text-left disabled:cursor-not-allowed"
                :class="compact ? 'min-h-9 text-sm' : 'min-h-10 text-base'"
                :aria-expanded="open"
                aria-haspopup="listbox"
                :disabled="disabled || atMax"
                @click="toggle"
                @keydown="onTriggerKeydown"
            >
                <span class="min-w-0 flex-1 truncate" :class="atMax ? 'text-ink-soft' : 'text-ink-soft/70'">
                    {{ atMax ? `Up to ${max} selected` : placeholder }}
                </span>
                <svg
                    class="size-4 shrink-0 text-ink-soft transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
                    :class="open ? 'rotate-180' : ''"
                    viewBox="0 0 20 20"
                    fill="none"
                    aria-hidden="true"
                >
                    <path d="M5 7.5 10 12.5 15 7.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>

        <Transition
            enter-active-class="transition duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
            enter-from-class="translate-y-1 scale-[0.98] opacity-0"
            enter-to-class="translate-y-0 scale-100 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 scale-100 opacity-100"
            leave-to-class="translate-y-1 scale-[0.98] opacity-0"
        >
            <div
                v-if="open"
                class="absolute z-50 mt-2 w-full origin-top overflow-hidden rounded-2xl border border-line bg-white shadow-[0_18px_40px_-24px_rgb(12_44_49/0.45)]"
            >
                <div class="border-b border-line px-3 py-2">
                    <input
                        ref="searchInput"
                        v-model="query"
                        type="search"
                        class="w-full rounded-xl border border-line bg-paper px-3 py-2 text-sm text-ink outline-none placeholder:text-ink-soft/60 focus:border-lagoon-700 focus:ring-2 focus:ring-lagoon-100"
                        :placeholder="searchPlaceholder"
                        autocomplete="off"
                        @keydown="onSearchKeydown"
                    >
                </div>
                <ul
                    :id="listId"
                    class="max-h-60 overflow-auto py-2"
                    role="listbox"
                    :aria-labelledby="buttonId"
                    aria-multiselectable="true"
                >
                    <li
                        v-if="filteredOptions.length === 0"
                        class="px-4 py-2.5 text-sm text-ink-soft"
                    >
                        No matches
                    </li>
                    <li
                        v-for="(option, index) in filteredOptions"
                        :key="String(option.value)"
                        role="option"
                        :aria-selected="isSelected(option.value)"
                        class="cursor-pointer px-4 py-2.5 text-sm font-medium leading-snug transition-colors duration-200"
                        :class="[
                            isSelected(option.value) ? 'bg-lagoon-50 text-lagoon-800' : 'text-gray-700 hover:bg-gray-50',
                            index === activeIndex ? 'bg-lagoon-50' : '',
                        ]"
                        @click="toggleValue(option.value)"
                        @mouseenter="activeIndex = index"
                    >
                        {{ option.label }}
                    </li>
                </ul>
            </div>
        </Transition>

        <p v-if="hint && !error" class="mt-2 text-sm leading-relaxed text-ink-soft">{{ hint }}</p>
        <p v-if="error" class="mt-2 text-sm text-clay-800">{{ error }}</p>
    </div>
</template>

<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, useId, watch } from 'vue';

type OptionInput = string | { value: string | number; label: string };

const props = withDefaults(defineProps<{
    modelValue: Array<string | number>;
    options: OptionInput[] | Record<string, string>;
    label?: string;
    placeholder?: string;
    searchPlaceholder?: string;
    optional?: boolean;
    hint?: string;
    error?: string;
    disabled?: boolean;
    compact?: boolean;
    max?: number;
    allowCustom?: boolean;
    id?: string;
}>(), {
    placeholder: 'Add…',
    searchPlaceholder: 'Type to filter…',
    optional: false,
    disabled: false,
    compact: false,
    max: 3,
    allowCustom: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: Array<string | number>];
}>();

const uid = useId();
const buttonId = computed(() => props.id ?? `${uid}-button`);
const listId = computed(() => `${uid}-list`);
const root = ref<HTMLElement | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);
const open = ref(false);
const query = ref('');
const activeIndex = ref(0);

const normalizedOptions = computed(() => {
    if (Array.isArray(props.options)) {
        return props.options.map((option) => {
            if (typeof option === 'string') {
                return { value: option, label: option };
            }

            return option;
        });
    }

    return Object.entries(props.options).map(([value, label]) => ({ value, label }));
});

const selectedOptions = computed(() =>
    props.modelValue.map((value) => {
        const match = normalizedOptions.value.find((option) => String(option.value) === String(value));

        return match ?? { value, label: String(value) };
    }),
);

const atMax = computed(() => props.modelValue.length >= props.max);

const filteredOptions = computed(() => {
    const needle = query.value.trim().toLowerCase();
    const available = normalizedOptions.value.filter((option) => ! isSelected(option.value));

    if (needle === '') {
        return available;
    }

    return available.filter((option) => option.label.toLowerCase().includes(needle));
});

watch(open, async (isOpen) => {
    if (! isOpen) {
        query.value = '';

        return;
    }

    activeIndex.value = 0;
    await nextTick();
    searchInput.value?.focus();
});

function isSelected(value: string | number): boolean {
    return props.modelValue.some((item) => String(item) === String(value));
}

function toggle(): void {
    if (props.disabled || atMax.value) {
        return;
    }

    open.value = ! open.value;
}

function close(): void {
    open.value = false;
}

function remove(value: string | number): void {
    if (props.disabled) {
        return;
    }

    emit(
        'update:modelValue',
        props.modelValue.filter((item) => String(item) !== String(value)),
    );
}

function toggleValue(value: string | number): void {
    if (isSelected(value)) {
        remove(value);

        return;
    }

    if (atMax.value) {
        return;
    }

    emit('update:modelValue', [...props.modelValue, value]);
    query.value = '';

    if (props.modelValue.length + 1 >= props.max) {
        close();
    }
}

function move(delta: number): void {
    const total = filteredOptions.value.length;
    if (total === 0) {
        return;
    }

    activeIndex.value = (activeIndex.value + delta + total) % total;
}

function chooseActive(): void {
    const option = filteredOptions.value[activeIndex.value];
    if (option) {
        toggleValue(option.value);

        return;
    }

    if (props.allowCustom) {
        addCustom();
    }
}

function addCustom(): void {
    const value = query.value.trim();
    if (value === '' || atMax.value || isSelected(value)) {
        return;
    }

    emit('update:modelValue', [...props.modelValue, value]);
    query.value = '';

    if (props.modelValue.length + 1 >= props.max) {
        close();
    }
}

function onTriggerKeydown(event: KeyboardEvent): void {
    if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        if (! atMax.value) {
            open.value = true;
        }
    }
}

function onSearchKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        event.preventDefault();
        close();

        return;
    }

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        move(1);

        return;
    }

    if (event.key === 'ArrowUp') {
        event.preventDefault();
        move(-1);

        return;
    }

    if (event.key === 'Enter') {
        event.preventDefault();
        chooseActive();

        return;
    }

    if (event.key === ',' && props.allowCustom) {
        event.preventDefault();
        addCustom();
    }
}

function onDocumentClick(event: MouseEvent): void {
    if (! open.value || ! root.value) {
        return;
    }

    if (! root.value.contains(event.target as Node)) {
        close();
    }
}

function onWindowKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        close();
    }
}

onMounted(() => {
    document.addEventListener('click', onDocumentClick);
    window.addEventListener('keydown', onWindowKeydown);
});

onUnmounted(() => {
    document.removeEventListener('click', onDocumentClick);
    window.removeEventListener('keydown', onWindowKeydown);
});
</script>
