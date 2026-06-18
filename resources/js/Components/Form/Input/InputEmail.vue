<script setup>
defineProps({
    modelValue: { type: [String, Number, Array, Boolean, Object, null], default: null },
    field: { type: Object, default: () => ({}) },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'focus', 'enter']);
</script>

<template>
    <Input
        :id="field.id"
        type="email"
        :model-value="modelValue"
        :disabled="disabled || field.disabled"
        :placeholder="field.placeholder"
        autocomplete="email"
        @update:model-value="emit('update:modelValue', $event)"
        @focus="emit('focus')"
        @keydown.enter="emit('enter')"
    />
</template>
