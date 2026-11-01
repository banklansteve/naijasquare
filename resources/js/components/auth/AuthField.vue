<template>
    <UiTextField
        :label="label"
        :model-value="modelValue"
        :name="name"
        :type="type"
        :autocomplete="autocomplete"
        :inputmode="inputmode"
        :optional="optional"
        :hint="hint"
        :error="error"
        @update:model-value="$emit('update:modelValue', $event)"
    />
</template>

<script setup lang="ts">
import UiTextField from '@/components/ui/UiTextField.vue';

withDefaults(defineProps<{
    label: string;
    modelValue: string;
    name?: string;
    type?: string;
    autocomplete?: string;
    inputmode?: 'text' | 'email' | 'tel' | 'numeric' | 'search';
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
