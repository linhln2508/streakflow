<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    icon: { type: String, default: null },
    label: { type: String, default: '' },
    value: { type: [String, Number], required: true },
    suffix: { type: String, default: '' },
    variant: {
        type: String,
        default: 'default',
        validator: (v) => ['default', 'success', 'warning', 'danger', 'info'].includes(v),
    },
    class: { type: String, default: '' },
});

const styles = {
    default: {
        card: 'border-border/60 bg-card',
        icon: 'bg-primary/10 text-primary',
        value: 'text-foreground',
    },
    success: {
        card: 'border-emerald-200/60 bg-gradient-to-br from-emerald-50/80 to-white',
        icon: 'bg-emerald-100 text-emerald-600',
        value: 'text-emerald-700',
    },
    warning: {
        card: 'border-amber-200/60 bg-gradient-to-br from-amber-50/80 to-white',
        icon: 'bg-amber-100 text-amber-600',
        value: 'text-amber-700',
    },
    danger: {
        card: 'border-rose-200/60 bg-gradient-to-br from-rose-50/80 to-white',
        icon: 'bg-rose-100 text-rose-600',
        value: 'text-rose-700',
    },
    info: {
        card: 'border-sky-200/60 bg-gradient-to-br from-sky-50/80 to-white',
        icon: 'bg-sky-100 text-sky-600',
        value: 'text-sky-700',
    },
};

const current = styles[props.variant] ?? styles.default;
</script>

<template>
    <div
        :class="cn(
            'relative overflow-hidden rounded-2xl border p-5 shadow-sm transition-shadow hover:shadow-md',
            current.card,
            $props.class,
        )"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p v-if="label" class="text-xs font-medium uppercase tracking-wide text-muted-foreground">{{ label }}</p>
                <p :class="cn('mt-1 text-2xl font-bold tracking-tight', current.value)">
                    {{ value }}<span v-if="suffix" class="ml-0.5 text-base font-medium text-muted-foreground">{{ suffix }}</span>
                </p>
                <div class="mt-2">
                    <slot />
                </div>
            </div>
            <div
                v-if="icon"
                :class="cn('flex h-11 w-11 shrink-0 items-center justify-center rounded-xl', current.icon)"
            >
                <DynamicIcon :name="icon" size="22" />
            </div>
        </div>
    </div>
</template>
