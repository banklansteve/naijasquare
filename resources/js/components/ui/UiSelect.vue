<template>
    <div ref="root" class="relative block w-full min-w-0" :class="open ? 'z-50' : 'z-0'">
        <span v-if="label" class="text-sm font-semibold text-ink">
            {{ label }}
            <span v-if="optional" class="font-medium text-ink-soft">Optional</span>
        </span>

        <button
            :id="buttonId"
            type="button"
            class="flex w-full items-center justify-between gap-3 rounded-2xl border bg-white px-4 text-left outline-none transition-[border-color,box-shadow,background-color] duration-300 ease-out disabled:cursor-not-allowed disabled:bg-gray-50 disabled:opacity-70"
            :class="[
                label ? 'mt-2' : '',
                compact ? 'min-h-11 text-sm' : 'min-h-12 text-base',
                open || error
                    ? error
                        ? 'border-clay-200 ring-4 ring-clay-100'
                        : 'border-lagoon-700 ring-4 ring-lagoon-100'
                    : 'border-line hover:border-lagoon-300',
            ]"
            :aria-expanded="open"
            aria-haspopup="listbox"
            :disabled="disabled"
            @click="toggle"
            @keydown="onTriggerKeydown"
        >
            <span class="min-w-0 flex-1 truncate" :class="selectedLabel ? 'text-ink' : 'text-ink-soft/70'">
                {{ selectedLabel || placeholder }}
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
                class="absolute z-50 mt-2 min-w-full w-max max-w-[min(28rem,92vw)] origin-top overflow-hidden rounded-2xl border border-line bg-white shadow-[0_18px_40px_-24px_rgb(12_44_49/0.45)]"
            >
                <div v-if="searchable" class="border-b border-line px-3 py-2">
                    <input
                        ref="searchInput"
                        v-model="query"
                        type="search"
                        class="w-full rounded-xl border border-line bg-paper px-3 py-2 text-sm text-ink outline-none placeholder:text-ink-soft/60 focus:border-lagoon-700 focus:ring-2 focus:ring-lagoon-100"
                        :placeholder="searchPlaceholder"
                        autocomplete="off"
                        @keydown="onListKeydown"
                    >
                </div>
                <ul
                    :id="listId"
                    class="max-h-60 overflow-auto py-2"
                    role="listbox"
                    :aria-labelledby="buttonId"
                    @keydown="onListKeydown"
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
                        :aria-selected="String(option.value) === String(modelValue)"
                        class="cursor-pointer px-4 py-2.5 text-sm font-medium leading-snug break-words whitespace-normal transition-colors duration-200 ease-out"
                        :class="[
                            String(option.value) === String(modelValue)
                                ? 'bg-lagoon-50 text-lagoon-800'
                                : 'text-gray-700 hover:bg-gray-50',
                            index === activeIndex ? 'bg-lagoon-50' : '',
                        ]"
                        @click="select(option.value)"
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
    modelValue: string | number;
    options: OptionInput[] | Record<string, string>;
    label?: string;
    placeholder?: string;
    searchPlaceholder?: string;
    optional?: boolean;
    hint?: string;
    error?: string;
    disabled?: boolean;
    compact?: boolean;
    searchable?: boolean;
    id?: string;
}>(), {
    placeholder: 'Select…',
    searchPlaceholder: 'Type to filter…',
    optional: false,
    disabled: false,
    compact: false,
    searchable: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
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

const filteredOptions = computed(() => {
    if (! props.searchable) {
        return normalizedOptions.value;
    }

    const needle = query.value.trim().toLowerCase();
    if (needle === '') {
        return normalizedOptions.value;
    }

    return normalizedOptions.value.filter((option) => option.label.toLowerCase().includes(needle));
});

const selectedLabel = computed(() => {
    const match = normalizedOptions.value.find((option) => String(option.value) === String(props.modelValue));

    return match?.label ?? '';
});

watch(open, async (isOpen) => {
    if (! isOpen) {
        query.value = '';

        return;
    }

    const index = filteredOptions.value.findIndex((option) => String(option.value) === String(props.modelValue));
    activeIndex.value = index >= 0 ? index : 0;
    await nextTick();

    if (props.searchable) {
        searchInput.value?.focus();
    }
});

function toggle(): void {
    if (props.disabled) {
        return;
    }

    open.value = !open.value;
}

function close(): void {
    open.value = false;
}

function select(value: string | number): void {
    emit('update:modelValue', value);
    close();
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
        select(option.value);
    }
}

function onTriggerKeydown(event: KeyboardEvent): void {
    if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        open.value = true;
    }
}

function onListKeydown(event: KeyboardEvent): void {
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

    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        chooseActive();
    }
}

function onDocumentClick(event: MouseEvent): void {
    if (!open.value || !root.value) {
        return;
    }

    if (!root.value.contains(event.target as Node)) {
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
