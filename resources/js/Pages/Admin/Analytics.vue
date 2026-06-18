<script setup>
import AppChart from '@/Components/charts/AppChart.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ stats: Object, dailyActivity: Array });

const statLabels = {
    total_users: 'Tổng users',
    total_instances: 'Task instances',
    total_summaries: 'Daily summaries',
    active_users_7d: 'Active 7 ngày',
};

const chartLabels = computed(() => props.dailyActivity?.map(d => d.date.substring(5)) ?? []);
const chartDatasets = computed(() => [{
    label: 'Summaries',
    data: props.dailyActivity?.map(d => d.count) ?? [],
    backgroundColor: 'rgba(99, 102, 241, 0.7)',
}]);
</script>

<template>
    <Head title="Admin - Analytics" />

    <PageHeader
        size="wide"
        title="Analytics"
        :breadcrumbs="[
            { label: 'Admin', href: route('admin.users') },
            { label: 'Analytics' },
        ]"
    />

    <PageContainer size="wide" class="space-y-6">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <StatCard
                v-for="(val, key) in stats"
                :key="key"
                icon="Shield"
                :label="statLabels[key] ?? key"
                :value="val"
                variant="info"
            />
        </div>

        <PageSection title="Hoạt động 30 ngày">
            <AppChart v-if="dailyActivity?.length" :labels="chartLabels" :datasets="chartDatasets" title="Daily summaries" :y-max="null" />
            <EmptyState v-else icon="CalendarCheck" title="Chưa có dữ liệu" />
        </PageSection>
    </PageContainer>
</template>
