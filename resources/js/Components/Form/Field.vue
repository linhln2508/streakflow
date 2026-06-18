<script setup>
import { ref, watch, useId } from 'vue';
import { validate } from '@/utils/fieldValidation';

const props = defineProps({
    field: { type: Object, default: () => ({}) },
    modelValue: {
        type: [String, Number, Array, Boolean, Object, null],
        default: null,
        required: true,
    },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'focus', 'enter']);

const localField = ref({
    id: useId(),
    ...props.field,
});
const errors = ref({});
const isResetForm = ref(false);

const validateField = () => {
    if (props.field.customValidator) {
        const customResult = props.field.customValidator(props.modelValue);
        if (customResult !== true) {
            errors.value = isResetForm.value ? {} : { custom: customResult };
            isResetForm.value = false;
            return false;
        }
    }

    const { isValid, errors: validationErrors } = validate(props.modelValue, props.field);
    errors.value = isResetForm.value ? {} : validationErrors;
    isResetForm.value = false;
    return isValid;
};

watch(() => props.field, (newVal) => {
    localField.value = { ...localField.value, ...newVal };
}, { deep: true });

watch(() => props.disabled, (newVal) => {
    localField.value = { ...localField.value, disabled: newVal };
}, { immediate: true });

watch(() => props.modelValue, () => {
    validateField();
});

const resetField = () => {
    isResetForm.value = true;
    const type = localField.value.type;
    if (type === 'Checkbox' || type === 'Switch') {
        emit('update:modelValue', false);
    } else if (type === 'Number') {
        emit('update:modelValue', null);
    } else {
        emit('update:modelValue', '');
    }
};

defineExpose({
    validate: validateField,
    resetField,
    errors,
});
</script>

<template>
    <div
        :class="[
            'flex w-full flex-col gap-1.5',
            localField.type?.toLowerCase() || 'text',
        ]"
    >
        <div
            v-if="localField.label && localField.type !== 'Switch'"
            class="flex items-end justify-between leading-none"
        >
            <Label :for="localField.id">
                {{ localField.label }}
                <span v-if="localField.validate?.includes('required')" class="text-destructive">*</span>
            </Label>
            <slot name="label-right" />
        </div>

        <InputDynamic
            :field="localField"
            :model-value="modelValue"
            :disabled="disabled"
            @update:model-value="emit('update:modelValue', $event)"
            @focus="emit('focus')"
            @enter="emit('enter')"
        />

        <div v-if="Object.keys(errors).length" class="space-y-1 text-xs text-destructive">
            <p v-for="(error, key) in errors" :key="key">{{ error }}</p>
        </div>

        <span v-if="localField.config?.help_text" class="text-xs text-muted-foreground">
            {{ localField.config.help_text }}
        </span>
    </div>
</template>
