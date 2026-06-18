<script setup>
defineProps({
    modelValue: { type: [String, Number, Array, Boolean, Object, null], default: null },
    field: { type: Object, default: () => ({}) },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'focus']);
</script>

<template>
    <Textarea
        :id="field.id"
        :model-value="modelValue"
        :rows="field.rows || 3"
        :disabled="disabled || field.disabled"
        :placeholder="field.placeholder"
        @update:model-value="emit('update:modelValue', $event)"
        @focus="emit('focus')"
    />
</template>
