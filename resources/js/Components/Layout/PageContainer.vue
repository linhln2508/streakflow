<script setup>
import { cn } from '@/lib/utils';

defineProps({
    size: {
        type: String,
        default: 'default',
        validator: (v) => ['default', 'narrow', 'wide'].includes(v),
    },
    class: { type: String, default: '' },
});

const sizeClass = {
    default: 'max-w-6xl',
    narrow: 'max-w-2xl',
    wide: 'max-w-7xl',
};
</script>

<template>
    <div :class="cn('mx-auto w-full px-4 py-8 sm:px-8', sizeClass[size], $props.class)">
        <slot />
    </div>
</template>
