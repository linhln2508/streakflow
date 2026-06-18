<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: { type: String, required: true },
});
</script>

<template>
    <Link
        :href="href"
        class="block w-full px-4 py-2 text-start text-sm text-foreground transition-colors hover:bg-muted focus:bg-muted focus:outline-none"
    >
        <slot />
    </Link>
</template>
