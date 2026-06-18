<script setup>
import { computed } from 'vue';
import InputCheckbox from './InputCheckbox.vue';
import InputDate from './InputDate.vue';
import InputEmail from './InputEmail.vue';
import InputNumber from './InputNumber.vue';
import InputPassword from './InputPassword.vue';
import InputSelect from './InputSelect.vue';
import InputText from './InputText.vue';
import InputTextarea from './InputTextarea.vue';

const props = defineProps({
    field: { type: Object, required: true },
    modelValue: {
        type: [String, Number, Array, Boolean, Object, null],
        required: true,
    },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'focus', 'enter']);

const componentType = computed(() => {
    const typeToComponent = {
        Checkbox: InputCheckbox,
        Email: InputEmail,
        Number: InputNumber,
        Password: InputPassword,
        Select: InputSelect,
        Switch: InputCheckbox,
        Text: InputText,
        Textarea: InputTextarea,
        Date: InputDate,
    };

    return typeToComponent[props.field.type] || InputText;
});
</script>

<template>
    <component
        :is="componentType"
        :model-value="modelValue"
        :field="field"
        :disabled="disabled"
        @update:model-value="emit('update:modelValue', $event)"
        @focus="emit('focus')"
        @enter="emit('enter')"
    />
</template>
