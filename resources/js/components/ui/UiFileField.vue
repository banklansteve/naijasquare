<template>
    <label class="block">
        <span v-if="label" class="text-sm font-semibold text-ink">
            {{ label }}
            <span v-if="optional" class="font-medium text-ink-soft">Optional</span>
        </span>
        <input
            type="file"
            :name="name"
            :accept="accept"
            :disabled="disabled"
            class="mt-2 block w-full text-sm text-ink-soft file:mr-3 file:rounded-full file:border-0 file:bg-lagoon-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-lagoon-800 file:transition-colors file:duration-300 hover:file:bg-lagoon-200"
            @change="$emit('change', $event)"
        >
        <p v-if="hint && !error" class="mt-2 text-sm leading-relaxed text-ink-soft">{{ hint }}</p>
        <p v-if="error" class="mt-2 text-sm text-clay-800">{{ error }}</p>
        <slot />
    </label>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
    label?: string;
    name?: string;
    accept?: string;
    optional?: boolean;
    hint?: string;
    error?: string;
    disabled?: boolean;
}>(), {
    accept: 'image/jpeg,image/png,image/webp',
    optional: false,
    disabled: false,
});

defineEmits<{
    change: [event: Event];
}>();
</script>
