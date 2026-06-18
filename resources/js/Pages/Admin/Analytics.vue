<script setup>
import AppChart from '@/Components/charts/AppChart.vue';
import Badge from '@/Components/ui/Badge.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Analytics</h2>
                <Link :href="route('admin.users')" class="text-sm text-primary hover:underline">← Users</Link>
            </div>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-4xl space-y-6 px-4">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <Card v-for="(val, key) in stats" :key="key">
                        <CardContent class="pt-6 text-center">
                            <div class="text-2xl font-bold text-primary">{{ val }}</div>
                            <div class="mt-1 text-xs text-muted-foreground">{{ statLabels[key] ?? key }}</div>
                        </CardContent>
                    </Card>
                </div>
                <Card>
                    <CardHeader>
                        <CardTitle>Hoạt động 30 ngày</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <AppChart v-if="dailyActivity?.length" :labels="chartLabels" :datasets="chartDatasets" title="Daily summaries" :y-max="null" />
                        <p v-else class="text-center text-muted-foreground">Chưa có dữ liệu.</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
