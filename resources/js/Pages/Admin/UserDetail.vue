<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({ profile: Object, summaries: Array });
</script>

<template>
    <Head :title="profile.name" />

    <PageHeader
        :title="profile.name"
        :description="profile.email"
        :breadcrumbs="[
            { label: 'Admin', href: route('admin.users') },
            { label: 'Users', href: route('admin.users') },
            { label: profile.name },
        ]"
    />

    <PageContainer class="space-y-6">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <StatCard icon="Zap" label="Level" :value="`Lv.${profile.level}`" variant="warning" />
            <StatCard icon="Heart" label="HP" :value="profile.hp" variant="danger" />
            <StatCard icon="Flame" label="Streak" :value="profile.streak_count" variant="warning" />
            <StatCard icon="Zap" label="XP" :value="profile.xp" variant="default" />
        </div>

        <PageSection title="30 ngày gần nhất">
            <div v-for="s in summaries" :key="s.id" class="flex justify-between border-b py-2 text-sm last:border-0">
                <span>{{ s.date }}</span>
                <span class="text-muted-foreground">{{ s.pct_completed }}% · +{{ s.xp_earned }} XP</span>
            </div>
            <EmptyState v-if="summaries.length === 0" icon="CalendarCheck" title="Chưa có dữ liệu" />
        </PageSection>
    </PageContainer>
</template>
