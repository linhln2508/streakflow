<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import Progress from '@/Components/ui/Progress.vue';
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
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold">Tổng quan</h2>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-6 px-4">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <Card>
                        <CardContent class="pt-6 text-center">
                            <div class="text-3xl font-bold text-amber-600">Lv.{{ user.level }}</div>
                            <div class="mt-1 text-xs text-muted-foreground">{{ user.xp }} XP</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="pt-6 text-center">
                            <div class="flex items-center justify-center gap-2 text-3xl font-bold text-orange-600">
                                <DynamicIcon name="Flame" size="28" />
                                {{ user.streak_count }}
                            </div>
                            <div class="mt-1 text-xs text-muted-foreground">Streak hiện tại</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="pt-6 text-center">
                            <div class="flex items-center justify-center gap-2 text-3xl font-bold text-red-600">
                                <DynamicIcon name="Heart" size="28" class="text-red-500" />
                                {{ user.hp }}
                            </div>
                            <div class="mt-1 text-xs text-muted-foreground">HP</div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardContent class="pt-6">
                        <div class="mb-2 flex justify-between text-sm">
                            <span>XP đến level {{ user.level + 1 }}</span>
                            <span>{{ xpToNextLevel }} XP còn lại</span>
                        </div>
                        <Progress :model-value="xpProgress" />
                        <p class="mt-2 text-xs text-muted-foreground">Streak dài nhất: {{ longestStreak }} ngày</p>
                    </CardContent>
                </Card>

                <div class="flex gap-3">
                    <Button
                        as="a"
                        variant="outline"
                        :href="route('reports.week', { year: now.getFullYear(), week: Math.ceil((now - new Date(now.getFullYear(), 0, 1)) / 86400000 / 7) })"
                    >
                        Tuần này
                    </Button>
                    <Button
                        as="a"
                        variant="outline"
                        :href="route('reports.month', { year: now.getFullYear(), month: now.getMonth() + 1 })"
                    >
                        Tháng này
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Badges ({{ badges.length }})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div v-for="b in badges" :key="b.id" class="rounded-lg bg-muted/50 p-3 text-center">
                                <div class="flex justify-center">
                                    <DynamicIcon v-if="b.icon" :name="b.icon" size="28" />
                                </div>
                                <div class="mt-1 text-xs font-medium">{{ b.name }}</div>
                            </div>
                        </div>
                        <p v-if="badges.length === 0" class="text-center text-sm text-muted-foreground">Chưa có badge nào.</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
