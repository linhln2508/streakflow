<script setup>
import { cn } from '@/lib/utils';

defineProps({
    class: { type: null, required: false },
});
</script>

<template>
    <h3 :class="cn('font-semibold leading-none tracking-tight', $attrs.class)">
        <slot />
    </h3>
</template>
