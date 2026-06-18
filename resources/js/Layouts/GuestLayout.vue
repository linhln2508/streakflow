<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-muted/40 pt-6 sm:justify-center sm:pt-0">
        <Link :href="route('login')" class="flex items-center gap-2">
            <ApplicationLogo class="h-10 w-auto fill-current text-primary" />
            <span class="text-xl font-bold text-primary">StreakFlow</span>
        </Link>

        <Card class="mt-6 w-full sm:max-w-md">
            <CardContent class="pt-6">
                <slot />
            </CardContent>
        </Card>
    </div>
</template>
