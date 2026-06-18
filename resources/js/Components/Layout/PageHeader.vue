<script setup>
import AppContainer from '@/Components/Layout/AppContainer.vue';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/vue3';

defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    breadcrumbs: { type: Array, default: () => [] },
    size: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'narrow', 'wide'].includes(value),
    },
    class: { type: String, default: '' },
});
</script>

<template>
    <header :class="cn('page-gradient border-b border-border/60 bg-card/40 py-6', $props.class)">
        <AppContainer :size="size" class="flex items-center justify-between gap-4">
            <div class="min-w-0 flex-1">
                <nav v-if="breadcrumbs.length" class="mb-2 flex flex-wrap items-center gap-1 text-sm text-muted-foreground">
                    <template v-for="(item, index) in breadcrumbs" :key="index">
                        <Link
                            v-if="item.href"
                            :href="item.href"
                            class="transition-colors hover:text-primary"
                        >
                            {{ item.label }}
                        </Link>
                        <span v-else :class="index === breadcrumbs.length - 1 ? 'font-medium text-foreground' : ''">
                            {{ item.label }}
                        </span>
                        <DynamicIcon
                            v-if="index < breadcrumbs.length - 1"
                            name="ChevronRight"
                            size="14"
                            class="text-muted-foreground/50"
                        />
                    </template>
                </nav>
                <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">{{ title }}</h1>
                <p v-if="description" class="mt-1.5 max-w-2xl text-sm text-muted-foreground">{{ description }}</p>
            </div>
            <div v-if="$slots.actions" class="flex shrink-0 flex-wrap items-center gap-2">
                <slot name="actions" />
            </div>
        </AppContainer>
    </header>
</template>
