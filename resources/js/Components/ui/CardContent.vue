<script setup>
import { cn } from '@/lib/utils';

defineProps({
    class: { type: null, required: false },
});
</script>

<template>
    <div :class="cn('p-6 pt-0', $attrs.class)">
        <slot />
    </div>
</template>
