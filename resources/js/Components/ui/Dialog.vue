<script setup>
import { cn } from '@/lib/utils';
import {
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogOverlay,
    DialogPortal,
    DialogRoot,
    DialogTitle,
} from 'reka-ui';

const props = defineProps({
    open: { type: Boolean, default: false },
    class: { type: null, required: false },
});

const emit = defineEmits(['update:open']);
</script>

<template>
    <DialogRoot :open="open" @update:open="emit('update:open', $event)">
        <DialogPortal>
            <DialogOverlay class="fixed inset-0 z-50 bg-black/80 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0" />
            <DialogContent
                :class="cn(
                    'fixed left-1/2 top-1/2 z-50 grid w-full max-w-lg -translate-x-1/2 -translate-y-1/2 gap-4 rounded-2xl border bg-background p-6 shadow-xl duration-200 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[state=closed]:slide-out-to-left-1/2 data-[state=closed]:slide-out-to-top-[48%] data-[state=open]:slide-in-from-left-1/2 data-[state=open]:slide-in-from-top-[48%]',
                    props.class,
                )"
            >
                <DialogTitle v-if="$slots.title" class="text-lg font-semibold leading-none tracking-tight">
                    <slot name="title" />
                </DialogTitle>
                <DialogDescription v-if="$slots.description" class="text-sm text-muted-foreground">
                    <slot name="description" />
                </DialogDescription>
                <slot />
                <DialogClose class="absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                    <span class="sr-only">Close</span>
                    ✕
                </DialogClose>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
