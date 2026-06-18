<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    user: Object,
    xpToNextLevel: Number,
    xpForNextLevel: Number,
    longestStreak: Number,
    badges: Array,
});

const now = new Date();
const xpProgress = computed(() => Math.min(100, (props.user.xp / props.xpForNextLevel) * 100));
</script>

<template>
    <Head title="Tổng quan" />

    <PageHeader title="Tổng quan" description="Thống kê gamification và badges">
        <template #actions>
            <ReportsNav />
        </template>
    </PageHeader>

    <PageContainer class="space-y-6">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
            <StatCard icon="Zap" label="Level" :value="`Lv.${user.level}`" variant="warning">
                <p class="mt-1 text-xs text-muted-foreground">{{ user.xp }} XP</p>
            </StatCard>
            <StatCard icon="Flame" label="Streak hiện tại" :value="user.streak_count" variant="warning" />
            <StatCard icon="Heart" label="HP" :value="user.hp" variant="danger" />
        </div>

        <PageSection title="Tiến độ level">
            <div class="mb-2 flex justify-between text-sm">
                <span>XP đến level {{ user.level + 1 }}</span>
                <span>{{ xpToNextLevel }} XP còn lại</span>
            </div>
            <Progress :model-value="xpProgress" />
            <p class="mt-2 text-xs text-muted-foreground">Streak dài nhất: {{ longestStreak }} ngày</p>
        </PageSection>

        <PageSection :title="`Badges (${badges.length})`">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div v-for="b in badges" :key="b.id" class="rounded-lg bg-muted/50 p-3 text-center">
                    <div class="flex justify-center">
                        <DynamicIcon v-if="b.icon" :name="b.icon" size="28" />
                    </div>
                    <div class="mt-1 text-xs font-medium">{{ b.name }}</div>
                </div>
            </div>
            <EmptyState v-if="badges.length === 0" icon="Trophy" title="Chưa có badge" description="Hoàn thành streak và ngày hoàn hảo để nhận badge." />
        </PageSection>
    </PageContainer>
</template>
