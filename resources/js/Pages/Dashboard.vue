<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useApi, unwrapApiData } from '@/composables/useApi';

const props = defineProps({
    instances: Array,
    stats: Object,
    isDayClosed: Boolean,
    today: String,
    xpToNextLevel: Number,
});

const user = usePage().props.auth.user;
const showConfirmClose = ref(false);
const showResult = ref(false);
const closeResultData = ref(null);

const priorityVariant = {
    low: 'secondary',
    medium: 'default',
    high: 'destructive',
};

const markDone = async (id) => {
    await useApi(route('web_api.today.done', id)).patch();
    router.reload({ only: ['instances', 'stats'] });
};

const markSkip = async (id) => {
    await useApi(route('web_api.today.skip', id)).patch();
    router.reload({ only: ['instances', 'stats'] });
};

const markUndo = async (id) => {
    await useApi(route('web_api.today.undo', id)).patch();
    router.reload({ only: ['instances', 'stats'] });
};

const confirmClose = async () => {
    showConfirmClose.value = false;
    const response = await useApi(route('web_api.today.close')).post();
    closeResultData.value = unwrapApiData(response);
    showResult.value = true;
    router.reload();
};

const hpPredictSign = computed(() => props.stats.predicted_hp_change >= 0 ? '+' : '');
</script>

<template>
    <Head title="Hôm nay" />

    <PageHeader :title="`Hôm nay — ${today}`" description="Mark done/skip task rồi chốt ngày để nhận điểm" />

    <PageContainer class="space-y-8">
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <StatCard icon="Heart" label="HP" :value="`${user.hp}`" suffix="/100" variant="danger">
                <Progress
                    :model-value="user.hp"
                    indicator-class="bg-gradient-to-r from-rose-400 to-rose-600"
                    track-class="bg-rose-100"
                    class="h-1.5"
                />
            </StatCard>
            <StatCard icon="Zap" label="Level" :value="`Lv.${user.level}`" variant="warning">
                <p class="text-xs text-muted-foreground">{{ xpToNextLevel }} XP đến level tiếp</p>
            </StatCard>
            <StatCard icon="Flame" label="Streak" :value="user.streak_count" variant="warning" />
            <StatCard icon="Shield" label="Shield" :value="user.shield_count" variant="info" />
        </div>

        <DailySummaryBanner :stats="stats" :hp-predict-sign="hpPredictSign" />

        <div
            v-if="isDayClosed"
            class="flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/80 px-6 py-4 text-emerald-800"
        >
            <DynamicIcon name="CheckCircle2" size="20" />
            <span class="font-medium">Ngày hôm nay đã được chốt.</span>
            <Link :href="route('reports.day', today)" class="font-semibold underline underline-offset-2">Xem báo cáo</Link>
        </div>

        <PageSection title="Task hôm nay" :description="`${instances.length} task`" no-padding>
            <div v-if="instances.length" class="space-y-3 p-4 sm:p-6">
                <TaskInstanceCard
                    v-for="instance in instances"
                    :key="instance.id"
                    :instance="instance"
                    :is-day-closed="isDayClosed"
                    :priority-variant="priorityVariant"
                    @done="markDone"
                    @skip="markSkip"
                    @undo="markUndo"
                />
            </div>
            <div v-else class="rounded-b-2xl border-t border-dashed border-border/60 bg-muted/20">
                <EmptyState
                    icon="CalendarCheck"
                    title="Chưa có task hôm nay"
                    description="Tạo task template để hệ thống tự sinh instance mỗi ngày."
                >
                    <template #action>
                        <Button as="a" :href="route('tasks.create')" class="rounded-full px-6 shadow-md shadow-primary/20">
                            <DynamicIcon name="Plus" size="16" class="mr-1" />
                            Tạo task mới
                        </Button>
                    </template>
                </EmptyState>
            </div>
            <template v-if="!isDayClosed" #footer>
                <div class="flex justify-center">
                    <Button
                        size="lg"
                        class="h-12 w-full max-w-md gap-2 rounded-full bg-primary font-semibold text-primary-foreground shadow-lg shadow-primary/30 transition-all hover:bg-primary/90 hover:shadow-xl hover:shadow-primary/40 sm:w-auto sm:px-10"
                        @click="showConfirmClose = true"
                    >
                        <DynamicIcon name="CalendarCheck" size="18" />
                        Chốt Ngày
                    </Button>
                </div>
            </template>
        </PageSection>
    </PageContainer>

    <Dialog :open="showConfirmClose" @update:open="showConfirmClose = $event">
        <template #title>Xác nhận chốt ngày</template>
        <template #description>
            Sau khi chốt bạn không thể thay đổi task. Task pending sẽ tự động bị skip. Tiếp tục?
        </template>
        <div class="flex justify-end gap-3">
            <Button variant="outline" class="rounded-full" @click="showConfirmClose = false">Hủy</Button>
            <Button class="rounded-full" @click="confirmClose">Chốt ngày</Button>
        </div>
    </Dialog>

    <Dialog :open="showResult" @update:open="showResult = $event">
        <template v-if="closeResultData" #title>Kết quả ngày</template>
        <div v-if="closeResultData" class="grid grid-cols-2 gap-3">
            <StatCard
                label="HP"
                :value="`${closeResultData.hp_change >= 0 ? '+' : ''}${closeResultData.hp_change} → ${closeResultData.hp_after}`"
                :variant="closeResultData.hp_change >= 0 ? 'success' : 'danger'"
            />
            <StatCard label="XP nhận" :value="`+${closeResultData.xp_earned}`" variant="warning" />
            <StatCard
                label="Streak"
                :value="`${closeResultData.streak_before} → ${closeResultData.streak_after}`"
                variant="warning"
            />
            <StatCard label="Hoàn thành" :value="`${closeResultData.pct_completed}%`" variant="info" />
        </div>
        <div class="mt-6 text-center">
            <Button class="rounded-full px-8" @click="showResult = false">Đóng</Button>
        </div>
    </Dialog>
</template>
