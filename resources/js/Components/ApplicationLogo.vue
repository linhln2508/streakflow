<script setup>
import { cn } from '@/lib/utils';
import { LOGO_MARK } from '@/constants/brand';

defineProps({
    class: { type: null, default: 'h-9 w-9' },
});
</script>

<template>
    <img
        :src="LOGO_MARK"
        alt=""
        aria-hidden="true"
        :class="cn('object-contain', $props.class)"
    />
</template>
