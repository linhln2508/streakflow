<script setup>
defineProps({
    modelValue: { type: [String, Number, Array, Boolean, Object, null], default: null },
    field: { type: Object, default: () => ({}) },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'focus']);
</script>

<template>
    <Select
        :id="field.id"
        :model-value="modelValue"
        :disabled="disabled || field.disabled"
        @update:model-value="emit('update:modelValue', $event)"
        @focus="emit('focus')"
    >
        <option v-if="field.placeholder" value="">{{ field.placeholder }}</option>
        <option
            v-for="option in field.options || []"
            :key="option.value ?? option"
            :value="option.value ?? option"
        >
            {{ option.label ?? option }}
        </option>
    </Select>
</template>
