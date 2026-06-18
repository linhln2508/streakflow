<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    modelValue: { type: Number, default: 0 },
    indicatorClass: { type: String, default: 'bg-primary' },
    trackClass: { type: String, default: 'bg-primary/15' },
    class: { type: null, required: false },
});
</script>

<template>
    <div :class="cn('relative h-2 w-full overflow-hidden rounded-full', trackClass, props.class)">
        <div
            :class="cn('h-full rounded-full transition-all duration-500', indicatorClass)"
            :style="{ width: `${Math.min(100, Math.max(0, modelValue))}%` }"
        />
    </div>
</template>
