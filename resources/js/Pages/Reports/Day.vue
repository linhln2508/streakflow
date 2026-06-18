<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({ date: String, summary: Object, instances: Array });

const statusVariant = {
    done: 'default',
    skipped: 'secondary',
    skipped_auto: 'destructive',
    pending: 'outline',
};
</script>

<template>
    <Head :title="`Báo cáo ${date}`" />

    <PageHeader
        :title="`Ngày ${date}`"
        :breadcrumbs="[
            { label: 'Báo cáo', href: route('reports.overview') },
            { label: date },
        ]"
    >
        <template #actions>
            <ReportsNav />
        </template>
    </PageHeader>

    <PageContainer class="space-y-6">
        <div v-if="summary" class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <StatCard
                icon="Heart"
                label="HP"
                :value="`${summary.hp_change >= 0 ? '+' : ''}${summary.hp_change}`"
                :variant="summary.hp_change >= 0 ? 'success' : 'danger'"
            />
            <StatCard icon="Zap" label="XP" :value="`+${summary.xp_earned}`" variant="warning" />
            <StatCard icon="Flame" label="Streak" :value="summary.streak_after" variant="warning" />
            <StatCard icon="CheckCircle2" label="Hoàn thành" :value="`${summary.pct_completed}%`" variant="info" />
        </div>

        <Card v-else class="border-yellow-200 bg-yellow-50">
            <CardContent class="flex items-center justify-center gap-2 pt-6 text-yellow-800">
                <DynamicIcon name="CalendarCheck" size="18" />
                Ngày này chưa được chốt.
            </CardContent>
        </Card>

        <PageSection title="Chi tiết task">
            <div v-for="i in instances" :key="i.id" class="flex items-center justify-between border-b py-2 last:border-0">
                <span>{{ i.template?.title }}</span>
                <Badge :variant="statusVariant[i.status] ?? 'outline'" class="capitalize">{{ i.status }}</Badge>
            </div>
            <EmptyState v-if="instances.length === 0" icon="CheckCircle2" title="Không có task" />
        </PageSection>
    </PageContainer>
</template>
