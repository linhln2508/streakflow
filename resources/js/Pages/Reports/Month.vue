<script setup>
import AppChart from '@/Components/charts/AppChart.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    year: Number,
    month: Number,
    summaries: Object,
    topSkipped: Array,
    hpChartData: Array,
});

const daysInMonth = computed(() => new Date(props.year, props.month, 0).getDate());
const firstDayOffset = computed(() => {
    const day = new Date(props.year, props.month - 1, 1).getDay();
    return day === 0 ? 6 : day - 1;
});

const heatColor = (pct) => {
    if (pct === null) return 'bg-muted text-muted-foreground';
    if (pct >= 100) return 'bg-green-500 text-white';
    if (pct >= 75) return 'bg-green-400';
    if (pct >= 50) return 'bg-yellow-400';
    if (pct > 0) return 'bg-orange-400';
    return 'bg-muted text-muted-foreground';
};

const getPct = (day) => {
    const dateStr = `${props.year}-${String(props.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    return props.summaries[dateStr]?.pct_completed ?? null;
};

const dateLink = (day) => `${props.year}-${String(props.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

const hpLabels = computed(() => props.hpChartData.map(d => d.day));
const hpDatasets = computed(() => [{
    label: 'HP thay đổi',
    data: props.hpChartData.map(d => d.hp_change),
    borderColor: 'rgb(239, 68, 68)',
    backgroundColor: 'rgba(239, 68, 68, 0.2)',
    tension: 0.3,
}]);
</script>

<template>
    <Head :title="`Tháng ${month}/${year}`" />

    <PageHeader
        :title="`Tháng ${month}/${year}`"
        :breadcrumbs="[
            { label: 'Báo cáo', href: route('reports.overview') },
            { label: `Tháng ${month}` },
        ]"
    >
        <template #actions>
            <ReportsNav />
        </template>
    </PageHeader>

    <PageContainer class="space-y-6">
        <PageSection title="Calendar">
            <div class="mb-2 grid grid-cols-7 gap-1 text-center text-xs text-muted-foreground">
                <div v-for="label in ['T2','T3','T4','T5','T6','T7','CN']" :key="label">{{ label }}</div>
            </div>
            <div class="grid grid-cols-7 gap-1">
                <div v-for="n in firstDayOffset" :key="'empty-' + n" />
                <div v-for="day in daysInMonth" :key="day" class="aspect-square">
                    <Link
                        :href="route('reports.day', dateLink(day))"
                        class="flex h-full items-center justify-center rounded text-xs font-medium"
                        :class="heatColor(getPct(day))"
                    >{{ day }}</Link>
                </div>
            </div>
        </PageSection>

        <PageSection v-if="hpChartData.length" title="HP theo thời gian">
            <AppChart type="line" :labels="hpLabels" :datasets="hpDatasets" title="HP thay đổi trong tháng" />
        </PageSection>

        <PageSection v-if="topSkipped.length" title="Task hay bị skip">
            <div v-for="t in topSkipped" :key="t.title" class="flex justify-between border-b py-2 last:border-0">
                <span>{{ t.title }}</span>
                <span class="font-medium text-destructive">{{ t.count }} lần</span>
            </div>
        </PageSection>
    </PageContainer>
</template>
