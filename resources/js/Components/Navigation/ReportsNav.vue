<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { normalizePath } from '@/constants/layout';

const page = usePage();
const now = new Date();
const year = now.getFullYear();
const month = now.getMonth() + 1;
const week = computed(() => Math.ceil((now - new Date(year, 0, 1)) / 86400000 / 7));

const tabs = computed(() => {
    const current = normalizePath(page.url);

    return [
        {
            label: 'Tổng quan',
            href: route('reports.overview'),
            active: current === normalizePath(route('reports.overview')),
        },
        {
            label: 'Tuần',
            href: route('reports.week', { year, week: week.value }),
            active: current.startsWith('/reports/week'),
        },
        {
            label: 'Tháng',
            href: route('reports.month', { year, month }),
            active: current.startsWith('/reports/month'),
        },
    ];
});
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
