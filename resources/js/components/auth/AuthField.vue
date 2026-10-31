<template>
    <label class="block">
        <span class="text-sm font-semibold text-ink">
            {{ label }}
            <span v-if="optional" class="font-medium text-ink-soft">Optional</span>
        </span>
        <input
            :value="modelValue"
            :type="type"
            :name="name"
            :autocomplete="autocomplete"
            :inputmode="inputmode"
            class="mt-2 min-h-12 w-full rounded-2xl border border-line bg-white px-4 text-base text-ink outline-none transition-[border-color,box-shadow] duration-500 ease-out placeholder:text-ink-soft/60 focus:border-lagoon-700 focus:ring-4 focus:ring-lagoon-100"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        >
        <p v-if="hint" class="mt-2 text-sm leading-relaxed text-ink-soft">{{ hint }}</p>
        <p v-if="error" class="mt-2 text-sm text-clay-800">{{ error }}</p>
    </label>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
    label: string;
    modelValue: string;
    name?: string;
    type?: string;
    autocomplete?: string;
    inputmode?: 'text' | 'email' | 'tel' | 'numeric';
    optional?: boolean;
    hint?: string;
    error?: string;
}>(), {
    type: 'text',
    optional: false,
});

defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>
