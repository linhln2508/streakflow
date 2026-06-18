<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    modelValue: { type: Number, default: 0 },
    class: { type: null, required: false },
});
</script>

<template>
    <div :class="cn('relative h-2 w-full overflow-hidden rounded-full bg-primary/20', props.class)">
        <div
            class="h-full bg-primary transition-all"
            :style="{ width: `${Math.min(100, Math.max(0, modelValue))}%` }"
        />
    </div>
</template>
