<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const now = new Date();
const year = now.getFullYear();
const month = now.getMonth() + 1;
const week = computed(() => Math.ceil((now - new Date(year, 0, 1)) / 86400000 / 7));

const tabs = computed(() => [
    { label: 'Tổng quan', href: route('reports.overview'), active: route().current('reports.overview') },
    { label: 'Tuần', href: route('reports.week', { year, week: week.value }), active: route().current('reports.week') },
    { label: 'Tháng', href: route('reports.month', { year, month }), active: route().current('reports.month') },
]);
</script>

<template>
    <div class="inline-flex rounded-full border border-border/60 bg-muted/50 p-1">
        <Link
            v-for="tab in tabs"
            :key="tab.label"
            :href="tab.href"
            class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition-all sm:text-sm"
            :class="tab.active
                ? 'bg-card text-primary shadow-sm'
                : 'text-muted-foreground hover:text-foreground'"
        >
            {{ tab.label }}
        </Link>
    </div>
</template>
