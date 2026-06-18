<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    class: { type: null, required: false },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <select
        :value="modelValue"
        :class="cn(
            'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50',
            props.class,
        )"
        @change="emit('update:modelValue', $event.target.value)"
    >
        <slot />
    </select>
</template>
