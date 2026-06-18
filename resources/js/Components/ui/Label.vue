<script setup>
import { cn } from '@/lib/utils';
import { Label } from 'reka-ui';

const props = defineProps({
    for: { type: String, required: false },
    class: { type: null, required: false },
});
</script>

<template>
    <Label :for="props.for" :class="cn('text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70', props.class)">
        <slot />
    </Label>
</template>
