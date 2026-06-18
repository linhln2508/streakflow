<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { useApi, unwrapApiData } from '@/composables/useApi';

const props = defineProps({
    instances: Array,
    stats: Object,
    isDayClosed: Boolean,
    selectedDate: String,
    today: String,
    isToday: Boolean,
    unclosedDays: Array,
    xpToNextLevel: Number,
});

const page = usePage();
const user = page.props.auth.user;
const unclosedDaysCount = computed(() => page.props.unclosedDaysCount ?? 0);
const showConfirmClose = ref(false);
const showResult = ref(false);
const showCompleted = ref(true);
const quickProcessing = ref(false);
const closeResultData = ref(null);

const quickForm = reactive({
    title: '',
    due_time: '',
    priority: 'medium',
});

const priorityVariant = {
    low: 'secondary',
    medium: 'default',
    high: 'destructive',
};

const pageTitle = computed(() =>
    props.isToday ? `Hôm nay — ${props.selectedDate}` : `Ngày ${props.selectedDate}`,
);

const sectionTitle = computed(() =>
    props.isToday ? 'Task hôm nay' : `Task ngày ${props.selectedDate}`,
);

const pendingInstances = computed(() =>
    props.instances.filter((instance) => instance.status === 'pending'),
);

const completedInstances = computed(() =>
    props.instances.filter((instance) => ['done', 'skipped', 'skipped_auto'].includes(instance.status)),
);

const overdueCount = computed(() =>
    pendingInstances.value.filter((instance) => Boolean(instance.is_overdue)).length,
);

const hpPredictSign = computed(() => props.stats.predicted_hp_change >= 0 ? '+' : '');

const reloadDay = () => {
    router.reload({
        only: ['instances', 'stats', 'unclosedDays', 'selectedDate', 'isToday'],
    });
};

const markDone = async (id) => {
    await useApi(route('web_api.today.done', id)).patch();
    reloadDay();
};

const markSkip = async (id) => {
    await useApi(route('web_api.today.skip', id)).patch();
    reloadDay();
};

const markUndo = async (id) => {
    await useApi(route('web_api.today.undo', id)).patch();
    reloadDay();
};

const submitQuickTask = async () => {
    if (!quickForm.title.trim()) {
        return;
    }

    quickProcessing.value = true;

    try {
        await useApi(route('web_api.today.quick_task')).post({
            title: quickForm.title.trim(),
            priority: quickForm.priority,
            due_time: quickForm.due_time || undefined,
        });

        quickForm.title = '';
        quickForm.due_time = '';
        reloadDay();
    } finally {
        quickProcessing.value = false;
    }
};

const confirmClose = async () => {
    showConfirmClose.value = false;
    const response = await useApi(route('web_api.today.close')).post({
        date: props.selectedDate,
    });
    closeResultData.value = unwrapApiData(response);
    showResult.value = true;
    router.reload();
};

const afterResultClose = () => {
    showResult.value = false;
    router.visit(route('dashboard'));
};
</script>

<template>
    <Head title="Hôm nay" />

    <PageHeader
        :title="pageTitle"
        description="Xử lý task rồi chốt ngày để nhận HP, XP và streak"
    />

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

        <DaySwitcher
            v-if="unclosedDays.length"
            :days="unclosedDays"
            :selected-date="selectedDate"
            :today="today"
        />

        <div
            v-else-if="unclosedDaysCount === 0"
            class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-800"
        >
            <DynamicIcon name="CheckCircle2" size="16" />
            Tất cả ngày có task đã được chốt.
        </div>

        <DailySummaryBanner :stats="stats" :hp-predict-sign="hpPredictSign" />

        <div
            v-if="!isToday"
            class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950"
        >
            <DynamicIcon name="Clock" size="16" class="mr-1 inline" />
            Đang xử lý ngày cũ chưa chốt. Sau khi chốt xong, chuyển sang ngày tiếp theo trong danh sách phía trên.
        </div>

        <PageSection :title="sectionTitle" :description="`${instances.length} task`" no-padding>
            <div v-if="isToday && !isDayClosed" class="border-b border-border/60 bg-muted/20 p-4 sm:p-6">
                <form class="space-y-3" @submit.prevent="submitQuickTask">
                    <p class="text-sm font-medium text-foreground">Tạo task nhanh (chỉ hôm nay)</p>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <Input
                            v-model="quickForm.title"
                            placeholder="VD: Gọi điện cho khách hàng..."
                            class="flex-1 rounded-xl"
                        />
                        <Input
                            v-model="quickForm.due_time"
                            type="time"
                            class="w-full rounded-xl sm:w-36"
                        />
                        <Select v-model="quickForm.priority" class="w-full rounded-xl sm:w-36">
                            <option value="low">Thấp</option>
                            <option value="medium">Trung bình</option>
                            <option value="high">Cao</option>
                        </Select>
                        <Button type="submit" :disabled="quickProcessing" class="rounded-full px-6">
                            <DynamicIcon name="Plus" size="14" />
                            {{ quickProcessing ? 'Đang thêm...' : 'Thêm' }}
                        </Button>
                    </div>
                </form>
            </div>

            <div v-if="instances.length" class="space-y-6 p-4 sm:p-6">
                <div v-if="overdueCount > 0" class="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <DynamicIcon name="AlertCircle" size="16" />
                    {{ overdueCount }} task đã quá hạn
                </div>

                <div v-if="pendingInstances.length" class="space-y-3">
                    <h3 class="text-sm font-semibold text-foreground">Cần xử lý ({{ pendingInstances.length }})</h3>
                    <TaskInstanceCard
                        v-for="instance in pendingInstances"
                        :key="instance.id"
                        :instance="instance"
                        :is-day-closed="isDayClosed"
                        :priority-variant="priorityVariant"
                        @done="markDone"
                        @skip="markSkip"
                        @undo="markUndo"
                    />
                </div>

                <div v-if="completedInstances.length" class="space-y-3">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded-xl border border-border/60 bg-muted/30 px-4 py-3 text-left text-sm font-semibold text-muted-foreground transition-colors hover:bg-muted/50"
                        @click="showCompleted = !showCompleted"
                    >
                        <span>Đã xong / Bỏ qua ({{ completedInstances.length }})</span>
                        <DynamicIcon :name="showCompleted ? 'ChevronUp' : 'ChevronDown'" size="16" />
                    </button>
                    <div v-show="showCompleted" class="space-y-3">
                        <TaskInstanceCard
                            v-for="instance in completedInstances"
                            :key="instance.id"
                            :instance="instance"
                            :is-day-closed="isDayClosed"
                            :priority-variant="priorityVariant"
                            @done="markDone"
                            @skip="markSkip"
                            @undo="markUndo"
                        />
                    </div>
                </div>
            </div>

            <div v-else class="rounded-b-2xl border-t border-dashed border-border/60 bg-muted/20">
                <EmptyState
                    icon="CalendarCheck"
                    :title="isToday ? 'Chưa có task hôm nay' : 'Không có task trong ngày này'"
                    :description="isToday ? 'Dùng form phía trên để thêm task nhanh, hoặc tạo task template lặp lại.' : undefined"
                >
                    <template v-if="isToday" #action>
                        <Button as="a" :href="route('tasks.create')" class="rounded-full px-6 shadow-md shadow-primary/20">
                            <DynamicIcon name="Plus" size="16" class="mr-1" />
                            Tạo task template
                        </Button>
                    </template>
                </EmptyState>
            </div>

            <template v-if="!isDayClosed && instances.length" #footer>
                <div class="flex justify-center">
                    <Button
                        size="lg"
                        class="h-12 w-full max-w-md gap-2 rounded-full bg-primary font-semibold text-primary-foreground shadow-lg shadow-primary/30 sm:w-auto sm:px-10"
                        @click="showConfirmClose = true"
                    >
                        <DynamicIcon name="CalendarCheck" size="18" />
                        Chốt ngày {{ isToday ? 'hôm nay' : selectedDate }}
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
        <template v-if="closeResultData" #title>Kết quả ngày {{ selectedDate }}</template>
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
            <Button class="rounded-full px-8" @click="afterResultClose">Tiếp tục</Button>
        </div>
    </Dialog>
</template>
