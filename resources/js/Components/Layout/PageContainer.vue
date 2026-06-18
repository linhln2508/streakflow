<script setup>
import AppContainer from '@/Components/Layout/AppContainer.vue';
import { cn } from '@/lib/utils';

defineProps({
    size: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'narrow', 'wide'].includes(value),
    },
    class: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <AppContainer :size="size" :class="cn('py-8', $props.class)">
        <slot />
    </AppContainer>
</template>
