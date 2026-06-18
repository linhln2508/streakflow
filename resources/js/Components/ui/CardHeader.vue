<script setup>
import { cn } from '@/lib/utils';

defineProps({
    class: { type: null, required: false },
});
</script>

<template>
    <div :class="cn('flex flex-col space-y-1.5 p-6', $attrs.class)">
        <slot />
    </div>
</template>
