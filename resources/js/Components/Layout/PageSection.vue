<script setup>
defineProps({
    title: { type: String, default: '' },
    description: { type: String, default: '' },
    noPadding: { type: Boolean, default: false },
});
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
        <div v-if="title || $slots.header" class="border-b border-border/60 bg-muted/30 px-6 py-4">
            <slot name="header">
                <h3 class="font-semibold text-foreground">{{ title }}</h3>
                <p v-if="description" class="mt-0.5 text-sm text-muted-foreground">{{ description }}</p>
            </slot>
        </div>
        <div :class="noPadding ? '' : 'p-6'">
            <slot />
        </div>
        <div v-if="$slots.footer" class="border-t border-border/60 bg-muted/20 px-6 py-4">
            <slot name="footer" />
        </div>
    </div>
</template>
