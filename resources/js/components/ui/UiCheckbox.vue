<template>
    <label
        class="group flex cursor-pointer items-start gap-3 transition duration-300 ease-out"
        :class="[
            card
                ? 'rounded-2xl border bg-white px-4 py-3'
                : '',
            card && checked
                ? 'border-lagoon-700 ring-4 ring-lagoon-100'
                : card
                    ? 'border-line hover:border-lagoon-300'
                    : '',
            disabled ? 'cursor-not-allowed opacity-60' : '',
        ]"
    >
        <span class="relative mt-0.5 inline-flex size-5 shrink-0 items-center justify-center">
            <input
                :id="inputId"
                class="peer sr-only"
                type="checkbox"
                :name="name"
                :value="value"
                :checked="checked"
                :disabled="disabled"
                @change="onChange"
            >
            <span
                class="grid size-5 place-items-center rounded-[0.4rem] border transition-[border-color,background-color,box-shadow,transform] duration-300 ease-out peer-focus-visible:ring-4 peer-focus-visible:ring-lagoon-100"
                :class="checked
                    ? 'scale-100 border-lagoon-700 bg-lagoon-700 text-white'
                    : 'border-line bg-white text-transparent group-hover:border-lagoon-400'"
            >
                <svg class="size-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M4.5 10.5 8 14l7.5-8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
        </span>
        <span v-if="label || $slots.default" class="min-w-0 text-sm leading-relaxed text-ink">
            <slot>{{ label }}</slot>
        </span>
    </label>
    <p v-if="error" class="mt-2 text-sm text-clay-800">{{ error }}</p>
</template>

<script setup lang="ts">
import { computed, useId } from 'vue';

const props = withDefaults(defineProps<{
    modelValue: boolean | Array<string | number>;
    value?: string | number;
    label?: string;
    name?: string;
    error?: string;
    disabled?: boolean;
    card?: boolean;
    id?: string;
}>(), {
    disabled: false,
    card: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean | Array<string | number>];
}>();

const uid = useId();
const inputId = computed(() => props.id ?? uid);

const checked = computed(() => {
    if (Array.isArray(props.modelValue)) {
        return props.value !== undefined && props.modelValue.includes(props.value);
    }

    return Boolean(props.modelValue);
});

function onChange(event: Event): void {
    const input = event.target as HTMLInputElement;

    if (Array.isArray(props.modelValue)) {
        const next = [...props.modelValue];
        const option = props.value;

        if (option === undefined) {
            return;
        }

        if (input.checked) {
            if (!next.includes(option)) {
                next.push(option);
            }
        } else {
            const index = next.indexOf(option);
            if (index >= 0) {
                next.splice(index, 1);
            }
        }

        emit('update:modelValue', next);

        return;
    }

    emit('update:modelValue', input.checked);
}
</script>
