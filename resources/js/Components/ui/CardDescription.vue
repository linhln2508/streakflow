<script setup>
import { cn } from '@/lib/utils';

defineProps({
    class: { type: null, required: false },
});
</script>

<template>
    <p :class="cn('text-sm text-muted-foreground', $attrs.class)">
        <slot />
    </p>
</template>
