<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

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
const closeResult = computed(() => usePage().props.flash?.closeResult);

watch(closeResult, (val) => {
    if (val) showResult.value = true;
}, { immediate: true });

const priorityColor = {
    low: 'bg-slate-100 text-slate-600',
    medium: 'bg-blue-100 text-blue-700',
    high: 'bg-red-100 text-red-700',
};

const markDone = (id) => router.patch(route('today.done', id));
const markSkip = (id) => router.patch(route('today.skip', id));
const markUndo = (id) => router.patch(route('today.undo', id));

const confirmClose = () => {
    showConfirmClose.value = false;
    router.post(route('today.close'));
};

const hpPredictSign = computed(() => props.stats.predicted_hp_change >= 0 ? '+' : '');
</script>

<template>
    <Head title="Hôm nay" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Hôm nay — {{ today }}</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="text-xs text-gray-500">HP</div>
                        <div class="mt-1 text-2xl font-bold text-red-600">{{ user.hp }}/100</div>
                        <div class="mt-2 h-2 rounded-full bg-gray-200">
                            <div class="h-2 rounded-full bg-red-500 transition-all" :style="{ width: user.hp + '%' }" />
                        </div>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="text-xs text-gray-500">XP / Level</div>
                        <div class="mt-1 text-2xl font-bold text-amber-600">Lv.{{ user.level }}</div>
                        <div class="text-xs text-gray-400">{{ xpToNextLevel }} XP đến level tiếp</div>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="text-xs text-gray-500">Streak</div>
                        <div class="mt-1 text-2xl font-bold text-orange-600">🔥 {{ user.streak_count }}</div>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="text-xs text-gray-500">Shield</div>
                        <div class="mt-1 text-2xl font-bold text-blue-600">🛡️ {{ user.shield_count }}</div>
                    </div>
                </div>

                <div class="rounded-xl bg-indigo-50 p-4 text-sm text-indigo-800">
                    Hôm nay có <strong>{{ stats.total }}</strong> task
                    · Skip miễn phí: <strong>{{ stats.remaining_skips }}</strong> lượt
                    · HP dự kiến: <strong>{{ hpPredictSign }}{{ stats.predicted_hp_change }}</strong>
                    · Done: {{ stats.done }} / Skip: {{ stats.skipped }} / Pending: {{ stats.pending }}
                </div>

                <div v-if="isDayClosed" class="rounded-xl border border-green-200 bg-green-50 p-4 text-center text-green-700">
                    ✅ Ngày hôm nay đã được chốt. Xem <a :href="route('reports.day', today)" class="underline">báo cáo</a>.
                </div>

                <div class="space-y-3">
                    <div
                        v-for="instance in instances"
                        :key="instance.id"
                        class="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm"
                        :class="{
                            'opacity-60': instance.status === 'skipped' || instance.status === 'skipped_auto',
                            'ring-2 ring-green-200': instance.status === 'done',
                        }"
                    >
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span
                                    v-if="instance.template?.category"
                                    class="h-3 w-3 rounded-full"
                                    :style="{ backgroundColor: instance.template.category.color }"
                                />
                                <span class="font-medium">{{ instance.template?.title }}</span>
                                <span :class="priorityColor[instance.template?.priority]" class="rounded px-1.5 py-0.5 text-xs">
                                    {{ instance.template?.priority }}
                                </span>
                            </div>
                            <div class="mt-1 text-xs text-gray-400 capitalize">{{ instance.status }}</div>
                        </div>
                        <div v-if="!isDayClosed" class="flex gap-2">
                            <template v-if="instance.status === 'pending'">
                                <button @click="markDone(instance.id)" class="rounded-lg bg-green-500 px-3 py-1.5 text-sm text-white hover:bg-green-600">Done</button>
                                <button @click="markSkip(instance.id)" class="rounded-lg bg-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-300">Skip</button>
                            </template>
                            <button v-else @click="markUndo(instance.id)" class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-200">Undo</button>
                        </div>
                    </div>

                    <div v-if="instances.length === 0" class="rounded-xl bg-white p-8 text-center text-gray-400 shadow-sm">
                        Chưa có task hôm nay. <a :href="route('tasks.create')" class="text-indigo-600 underline">Tạo task mới</a>
                    </div>
                </div>

                <div v-if="!isDayClosed" class="text-center">
                    <PrimaryButton @click="showConfirmClose = true" class="px-8 py-3 text-base">
                        Chốt Ngày
                    </PrimaryButton>
                </div>
            </div>
        </div>

        <Modal :show="showConfirmClose" @close="showConfirmClose = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold">Xác nhận chốt ngày</h3>
                <p class="mt-2 text-sm text-gray-600">Sau khi chốt bạn không thể thay đổi task. Task pending sẽ tự động bị skip. Tiếp tục?</p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showConfirmClose = false">Hủy</SecondaryButton>
                    <PrimaryButton @click="confirmClose">Chốt ngày</PrimaryButton>
                </div>
            </div>
        </Modal>

        <Modal :show="showResult" @close="showResult = false">
            <div class="p-6" v-if="closeResult">
                <h3 class="text-lg font-semibold text-center">🎉 Kết quả ngày</h3>
                <div class="mt-4 grid grid-cols-2 gap-4 text-center">
                    <div class="rounded-lg bg-red-50 p-3">
                        <div class="text-xs text-gray-500">HP</div>
                        <div class="text-xl font-bold" :class="closeResult.hp_change >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ closeResult.hp_change >= 0 ? '+' : '' }}{{ closeResult.hp_change }} → {{ closeResult.hp_after }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-amber-50 p-3">
                        <div class="text-xs text-gray-500">XP nhận</div>
                        <div class="text-xl font-bold text-amber-600">+{{ closeResult.xp_earned }}</div>
                    </div>
                    <div class="rounded-lg bg-orange-50 p-3">
                        <div class="text-xs text-gray-500">Streak</div>
                        <div class="text-xl font-bold text-orange-600">{{ closeResult.streak_before }} → {{ closeResult.streak_after }}</div>
                    </div>
                    <div class="rounded-lg bg-blue-50 p-3">
                        <div class="text-xs text-gray-500">Hoàn thành</div>
                        <div class="text-xl font-bold text-blue-600">{{ closeResult.pct_completed }}%</div>
                    </div>
                </div>
                <p v-if="closeResult.shield_used" class="mt-3 text-center text-sm text-blue-600">🛡️ Đã dùng 1 Shield để bảo vệ streak</p>
                <p v-if="closeResult.debt_added" class="mt-3 text-center text-sm text-yellow-600">⚠️ Đã vay nợ — ngày mai cần ≥75% để trả</p>
                <p v-if="closeResult.streak_reset" class="mt-3 text-center text-sm text-red-600">💔 Streak đã reset</p>
                <div class="mt-6 text-center">
                    <PrimaryButton @click="showResult = false">Đóng</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
