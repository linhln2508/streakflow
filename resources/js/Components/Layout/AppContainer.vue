<script setup>
import { CONTAINER_CLASS } from '@/constants/layout';
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps({
    size: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'narrow', 'wide'].includes(value),
    },
    tag: {
        type: String,
        default: 'div',
    },
    class: {
        type: String,
        default: '',
    },
});

const containerClass = computed(() => cn(CONTAINER_CLASS[props.size], props.class));
</script>

<template>
    <component :is="tag" :class="containerClass">
        <slot />
    </component>
</template>
