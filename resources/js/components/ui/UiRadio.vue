<template>
    <label
        class="group flex cursor-pointer transition duration-300 ease-out"
        :class="[
            card
                ? 'gap-3 rounded-2xl border bg-white px-4 py-3.5'
                : 'items-center gap-3',
            card && selected
                ? 'border-lagoon-700 ring-4 ring-lagoon-100'
                : card
                    ? 'border-line hover:border-lagoon-300'
                    : '',
            disabled ? 'cursor-not-allowed opacity-60' : '',
        ]"
    >
        <span class="relative inline-flex size-5 shrink-0 items-center justify-center" :class="card && description ? 'mt-0.5' : ''">
            <input
                :id="inputId"
                class="peer sr-only"
                type="radio"
                :name="name"
                :value="value"
                :checked="selected"
                :disabled="disabled"
                @change="emit('update:modelValue', value)"
            >
            <span
                class="grid size-5 place-items-center rounded-full border transition-[border-color,background-color,box-shadow] duration-300 ease-out peer-focus-visible:ring-4 peer-focus-visible:ring-lagoon-100"
                :class="selected
                    ? 'border-lagoon-700 bg-white'
                    : 'border-line bg-white group-hover:border-lagoon-400'"
            >
                <span
                    class="size-2.5 rounded-full bg-lagoon-700 transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
                    :class="selected ? 'scale-100' : 'scale-0'"
                />
            </span>
        </span>
        <span class="min-w-0">
            <span class="block text-sm font-semibold text-ink">
                <slot>{{ label }}</slot>
            </span>
            <span v-if="description" class="mt-1 block text-sm leading-relaxed text-ink-soft">{{ description }}</span>
        </span>
    </label>
</template>

<script setup lang="ts" generic="T extends string | number">
import { computed, useId } from 'vue';

const props = withDefaults(defineProps<{
    modelValue: T;
    value: T;
    label?: string;
    description?: string;
    name?: string;
    disabled?: boolean;
    card?: boolean;
    id?: string;
}>(), {
    disabled: false,
    card: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: T];
}>();

const uid = useId();
const inputId = computed(() => props.id ?? uid);
const selected = computed(() => props.modelValue === props.value);
</script>
