<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    modelValue: { type: String, default: '' },
    rows: { type: Number, default: 3 },
    class: { type: null, required: false },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <textarea
        :rows="rows"
        :value="modelValue"
        :class="cn(
            'flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50',
            props.class,
        )"
        @input="emit('update:modelValue', $event.target.value)"
    />
</template>
