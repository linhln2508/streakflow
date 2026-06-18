<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
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

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold">Hôm nay — {{ today }}</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="flex items-center gap-1 text-xs font-normal text-muted-foreground">
                                <DynamicIcon name="Heart" size="14" class="text-red-500" /> HP
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <div class="text-2xl font-bold text-red-600">{{ user.hp }}/100</div>
                            <Progress :model-value="user.hp" />
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="flex items-center gap-1 text-xs font-normal text-muted-foreground">
                                <DynamicIcon name="Zap" size="14" class="text-amber-500" /> Level
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-amber-600">Lv.{{ user.level }}</div>
                            <div class="text-xs text-muted-foreground">{{ xpToNextLevel }} XP đến level tiếp</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="flex items-center gap-1 text-xs font-normal text-muted-foreground">
                                <DynamicIcon name="Flame" size="14" class="text-orange-500" /> Streak
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-orange-600">{{ user.streak_count }}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="flex items-center gap-1 text-xs font-normal text-muted-foreground">
                                <DynamicIcon name="Shield" size="14" class="text-blue-500" /> Shield
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-blue-600">{{ user.shield_count }}</div>
                        </CardContent>
                    </Card>
                </div>

                <Card class="border-indigo-200 bg-indigo-50/50">
                    <CardContent class="pt-6 text-sm text-indigo-900">
                        Hôm nay có <strong>{{ stats.total }}</strong> task
                        · Skip miễn phí: <strong>{{ stats.remaining_skips }}</strong> lượt
                        · HP dự kiến: <strong>{{ hpPredictSign }}{{ stats.predicted_hp_change }}</strong>
                        · Done: {{ stats.done }} / Skip: {{ stats.skipped }} / Pending: {{ stats.pending }}
                    </CardContent>
                </Card>

                <Card v-if="isDayClosed" class="border-green-200 bg-green-50">
                    <CardContent class="flex items-center justify-center gap-2 pt-6 text-green-700">
                        <DynamicIcon name="CheckCircle2" size="18" />
                        Ngày hôm nay đã được chốt.
                        <a :href="route('reports.day', today)" class="underline">Xem báo cáo</a>
                    </CardContent>
                </Card>

                <div class="space-y-3">
                    <Card
                        v-for="instance in instances"
                        :key="instance.id"
                        :class="{
                            'opacity-60': instance.status === 'skipped' || instance.status === 'skipped_auto',
                            'ring-2 ring-green-200': instance.status === 'done',
                        }"
                    >
                        <CardContent class="flex items-center justify-between pt-6">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        v-if="instance.template?.category"
                                        class="h-3 w-3 rounded-full"
                                        :style="{ backgroundColor: instance.template.category.color }"
                                    />
                                    <span class="font-medium">{{ instance.template?.title }}</span>
                                    <Badge :variant="priorityVariant[instance.template?.priority] ?? 'secondary'">
                                        {{ instance.template?.priority }}
                                    </Badge>
                                </div>
                                <div class="mt-1 text-xs capitalize text-muted-foreground">{{ instance.status }}</div>
                            </div>
                            <div v-if="!isDayClosed" class="flex gap-2">
                                <template v-if="instance.status === 'pending'">
                                    <Button size="sm" class="bg-green-600 hover:bg-green-700" @click="markDone(instance.id)">
                                        <DynamicIcon name="Check" size="14" class="mr-1" /> Done
                                    </Button>
                                    <Button size="sm" variant="secondary" @click="markSkip(instance.id)">
                                        <DynamicIcon name="SkipForward" size="14" class="mr-1" /> Skip
                                    </Button>
                                </template>
                                <Button v-else size="sm" variant="outline" @click="markUndo(instance.id)">
                                    <DynamicIcon name="Undo2" size="14" class="mr-1" /> Undo
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <Card v-if="instances.length === 0">
                        <CardContent class="py-8 text-center text-muted-foreground">
                            Chưa có task hôm nay.
                            <a :href="route('tasks.create')" class="text-primary underline">Tạo task mới</a>
                        </CardContent>
                    </Card>
                </div>

                <div v-if="!isDayClosed" class="text-center">
                    <Button size="lg" class="px-8" @click="showConfirmClose = true">
                        <DynamicIcon name="CalendarCheck" size="16" class="mr-2" />
                        Chốt Ngày
                    </Button>
                </div>
            </div>
        </div>

        <Dialog :open="showConfirmClose" @update:open="showConfirmClose = $event">
            <template #title>Xác nhận chốt ngày</template>
            <template #description>
                Sau khi chốt bạn không thể thay đổi task. Task pending sẽ tự động bị skip. Tiếp tục?
            </template>
            <div class="flex justify-end gap-3">
                <Button variant="outline" @click="showConfirmClose = false">Hủy</Button>
                <Button @click="confirmClose">Chốt ngày</Button>
            </div>
        </Dialog>

        <Dialog :open="showResult" @update:open="showResult = $event">
            <template v-if="closeResultData" #title>Kết quả ngày</template>
            <div v-if="closeResultData" class="grid grid-cols-2 gap-4 text-center">
                <Card class="bg-red-50">
                    <CardContent class="pt-4">
                        <div class="text-xs text-muted-foreground">HP</div>
                        <div class="text-xl font-bold" :class="closeResultData.hp_change >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ closeResultData.hp_change >= 0 ? '+' : '' }}{{ closeResultData.hp_change }} → {{ closeResultData.hp_after }}
                        </div>
                    </CardContent>
                </Card>
                <Card class="bg-amber-50">
                    <CardContent class="pt-4">
                        <div class="text-xs text-muted-foreground">XP nhận</div>
                        <div class="text-xl font-bold text-amber-600">+{{ closeResultData.xp_earned }}</div>
                    </CardContent>
                </Card>
                <Card class="bg-orange-50">
                    <CardContent class="pt-4">
                        <div class="text-xs text-muted-foreground">Streak</div>
                        <div class="text-xl font-bold text-orange-600">{{ closeResultData.streak_before }} → {{ closeResultData.streak_after }}</div>
                    </CardContent>
                </Card>
                <Card class="bg-blue-50">
                    <CardContent class="pt-4">
                        <div class="text-xs text-muted-foreground">Hoàn thành</div>
                        <div class="text-xl font-bold text-blue-600">{{ closeResultData.pct_completed }}%</div>
                    </CardContent>
                </Card>
            </div>
            <div class="mt-6 text-center">
                <Button @click="showResult = false">Đóng</Button>
            </div>
        </Dialog>
    </AuthenticatedLayout>
</template>
