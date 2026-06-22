<script setup>
import { cva } from 'class-variance-authority';
import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });

const props = defineProps({
    variant: { type: String, default: 'default' },
    size: { type: String, default: 'default' },
    as: { type: String, default: 'button' },
    class: { type: null, required: false },
    type: { type: String, default: 'button' },
    disabled: { type: Boolean, default: false },
});

const buttonVariants = cva(
    'inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0',
    {
        variants: {
            variant: {
                default: 'bg-primary text-primary-foreground shadow hover:bg-primary/90',
                emphasis: 'bg-primary text-primary-foreground font-semibold shadow-md shadow-primary/25 hover:bg-primary/90',
                destructive: 'bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/90',
                outline: 'border border-input bg-background text-foreground shadow-sm hover:bg-accent hover:text-accent-foreground',
                secondary: 'bg-secondary text-secondary-foreground shadow-sm hover:bg-secondary/80',
                ghost: 'text-foreground hover:bg-accent hover:text-accent-foreground',
                link: 'text-primary underline-offset-4 hover:underline',
                success: 'bg-emerald-600 text-white shadow-sm hover:bg-emerald-700',
                warning: 'bg-amber-500 text-white shadow-sm hover:bg-amber-600',
            },
            size: {
                default: 'h-9 rounded-xl px-4 py-2',
                sm: 'h-8 rounded-lg px-3 text-xs',
                lg: 'h-11 rounded-xl px-8',
                block: 'h-11 w-full rounded-xl px-4',
                icon: 'h-9 w-9 rounded-xl',
                'icon-xs': 'h-6 w-6 rounded-md text-xs [&_svg]:!size-3',
                'icon-sm': 'h-7 w-7 rounded-full',
                'icon-pill': 'h-9 w-9 rounded-full',
                fab: 'h-14 w-14 rounded-full text-base shadow-lg shadow-primary/25',
                'fab-sm': 'h-11 w-11 rounded-full shadow-md shadow-primary/20 [&_svg]:!size-[18px]',
                'pill-sm': 'h-8 rounded-full px-3 text-xs',
                pill: 'h-9 rounded-full px-6',
                'pill-lg': 'h-12 rounded-full px-10 text-base',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
);
</script>

<template>
    <component
        :is="as"
        v-bind="$attrs"
        :type="as === 'button' ? type : undefined"
        :disabled="disabled"
        :class="cn(buttonVariants({ variant, size }), props.class)"
    >
        <slot />
    </component>
</template>
