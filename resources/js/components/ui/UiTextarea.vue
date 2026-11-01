<template>
    <label class="block">
        <span v-if="label" class="text-sm font-semibold text-ink">
            {{ label }}
            <span v-if="optional" class="font-medium text-ink-soft">Optional</span>
        </span>
        <textarea
            :id="inputId"
            :value="modelValue"
            :name="name"
            :rows="rows"
            :placeholder="placeholder"
            :disabled="disabled"
            class="w-full resize-y rounded-2xl border bg-white px-4 py-3 text-base text-ink outline-none transition-[border-color,box-shadow,background-color] duration-300 ease-out placeholder:text-ink-soft/60 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:opacity-70"
            :class="[
                label ? 'mt-2' : '',
                compact ? 'text-sm' : '',
                error
                    ? 'border-clay-200 focus:border-clay-700 focus:ring-4 focus:ring-clay-100'
                    : 'border-line focus:border-lagoon-700 focus:ring-4 focus:ring-lagoon-100',
            ]"
            @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
        />
        <p v-if="hint && !error" class="mt-2 text-sm leading-relaxed text-ink-soft">{{ hint }}</p>
        <p v-if="error" class="mt-2 text-sm text-clay-800">{{ error }}</p>
    </label>
</template>

<script setup lang="ts">
import { computed, useId } from 'vue';

const props = withDefaults(defineProps<{
    label?: string;
    modelValue: string;
    name?: string;
    rows?: number;
    placeholder?: string;
    optional?: boolean;
    hint?: string;
    error?: string;
    disabled?: boolean;
    compact?: boolean;
    id?: string;
}>(), {
    rows: 4,
    optional: false,
    disabled: false,
    compact: false,
});

defineEmits<{
    'update:modelValue': [value: string];
}>();

const uid = useId();
const inputId = computed(() => props.id ?? uid);
</script>
