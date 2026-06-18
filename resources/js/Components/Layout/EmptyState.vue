<script setup>
defineProps({
    icon: { type: String, default: 'Briefcase' },
    title: { type: String, default: 'Chưa có dữ liệu' },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center py-14 text-center">
        <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 ring-1 ring-primary/10">
            <DynamicIcon :name="icon" size="28" class="text-primary" />
        </div>
        <p class="text-lg font-semibold text-foreground">{{ title }}</p>
        <p v-if="description" class="mt-2 max-w-md text-sm leading-relaxed text-muted-foreground">{{ description }}</p>
        <div v-if="$slots.action" class="mt-6">
            <slot name="action" />
        </div>
    </div>
</template>
