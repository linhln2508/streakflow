<script setup>
import AppChart from '@/Components/charts/AppChart.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    year: Number, week: Number, start: String, end: String,
    summaries: Array, totalHpChange: Number, totalXpEarned: Number,
});

const heatColor = (pct) => {
    if (pct >= 100) return 'bg-green-500';
    if (pct >= 75) return 'bg-green-400';
    if (pct >= 50) return 'bg-yellow-400';
    if (pct > 0) return 'bg-orange-400';
    return 'bg-muted';
};

const days = computed(() => {
    const result = [];
    const start = new Date(props.start);
    for (let i = 0; i < 7; i++) {
        const d = new Date(start);
        d.setDate(d.getDate() + i);
        const dateStr = d.toISOString().substring(0, 10);
        const summary = props.summaries.find(s => s.date.substring(0, 10) === dateStr);
        result.push({ date: dateStr, label: ['T2','T3','T4','T5','T6','T7','CN'][i], pct: summary?.pct_completed ?? 0, summary });
    }
    return result;
});

const chartLabels = computed(() => days.value.map(d => d.label));
const chartDatasets = computed(() => [{
    label: '% Hoàn thành',
    data: days.value.map(d => d.pct),
    backgroundColor: days.value.map(d =>
        d.pct >= 100 ? 'rgba(34,197,94,0.8)' :
        d.pct >= 75 ? 'rgba(74,222,128,0.8)' :
        d.pct >= 50 ? 'rgba(250,204,21,0.8)' :
        d.pct > 0 ? 'rgba(251,146,60,0.8)' : 'rgba(203,213,225,0.8)'
    ),
}]);
</script>

<template>
    <Head :title="`Tuần ${week}/${year}`" />

    <PageHeader
        :title="`Tuần ${week}/${year}`"
        :description="`${start} → ${end}`"
        :breadcrumbs="[
            { label: 'Báo cáo', href: route('reports.overview') },
            { label: `Tuần ${week}` },
        ]"
    >
        <template #actions>
            <ReportsNav />
        </template>
    </PageHeader>

    <PageContainer class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <StatCard
                icon="Heart"
                label="Tổng HP"
                :value="`${totalHpChange >= 0 ? '+' : ''}${totalHpChange}`"
                :variant="totalHpChange >= 0 ? 'success' : 'danger'"
            />
            <StatCard icon="Zap" label="Tổng XP" :value="`+${totalXpEarned}`" variant="warning" />
        </div>

        <PageSection title="Heatmap tuần">
            <div class="grid grid-cols-7 gap-2">
                <div v-for="d in days" :key="d.date" class="text-center">
                    <div class="mb-1 text-xs text-muted-foreground">{{ d.label }}</div>
                    <Link
                        :href="route('reports.day', d.date)"
                        class="block rounded-lg p-4 text-sm font-bold text-white"
                        :class="heatColor(d.pct)"
                    >
                        {{ d.pct }}%
                    </Link>
                </div>
            </div>
        </PageSection>

        <PageSection title="Biểu đồ hoàn thành">
            <AppChart :labels="chartLabels" :datasets="chartDatasets" title="% hoàn thành theo ngày" />
        </PageSection>
    </PageContainer>
</template>
