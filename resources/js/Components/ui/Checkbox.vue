<script setup>
import { CheckboxIndicator, CheckboxRoot } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({
    checked: { type: [Boolean, String], default: false },
    class: { type: null, required: false },
});

const emit = defineEmits(['update:checked']);
</script>

<template>
    <CheckboxRoot
        :checked="checked"
        :class="cn(
            'peer h-4 w-4 shrink-0 rounded-sm border border-primary shadow focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground',
            props.class,
        )"
        @update:checked="emit('update:checked', $event)"
    >
        <CheckboxIndicator class="flex items-center justify-center text-current">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="h-3 w-3">
                <path d="M20 6 9 17l-5-5" />
            </svg>
        </CheckboxIndicator>
    </CheckboxRoot>
</template>
