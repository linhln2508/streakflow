<script setup>
import AppNavbar from '@/Components/Navigation/AppNavbar.vue';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background">
        <div class="pointer-events-none fixed inset-0 -z-10 page-gradient" />
        <AppNavbar />
        <main class="flex-1">
            <slot />
        </main>
    </div>
</template>
