<script setup>
defineProps({
    modelValue: { type: [String, Number, Array, Boolean, Object, null], default: null },
    field: { type: Object, default: () => ({}) },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'focus']);
</script>

<template>
    <label class="flex items-center gap-2">
        <Checkbox
            :checked="!!modelValue"
            :disabled="disabled || field.disabled"
            @update:checked="emit('update:modelValue', $event)"
        />
        <span v-if="field.checkboxLabel" class="text-sm text-muted-foreground">{{ field.checkboxLabel }}</span>
    </label>
</template>
