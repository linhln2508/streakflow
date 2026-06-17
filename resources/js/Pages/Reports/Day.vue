<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ date: String, summary: Object, instances: Array });

const statusColor = { done: 'text-green-600', skipped: 'text-yellow-600', skipped_auto: 'text-red-400', pending: 'text-gray-400' };
</script>

<template>
    <Head :title="`Báo cáo ${date}`" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Báo cáo ngày {{ date }}</h2>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-6 px-4">
                <div v-if="summary" class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-xl bg-white p-4 text-center shadow-sm">
                        <div class="text-xs text-gray-500">HP</div>
                        <div class="text-xl font-bold" :class="summary.hp_change >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ summary.hp_change >= 0 ? '+' : '' }}{{ summary.hp_change }}
                        </div>
                    </div>
                    <div class="rounded-xl bg-white p-4 text-center shadow-sm">
                        <div class="text-xs text-gray-500">XP</div>
                        <div class="text-xl font-bold text-amber-600">+{{ summary.xp_earned }}</div>
                    </div>
                    <div class="rounded-xl bg-white p-4 text-center shadow-sm">
                        <div class="text-xs text-gray-500">Streak</div>
                        <div class="text-xl font-bold text-orange-600">{{ summary.streak_after }}</div>
                    </div>
                    <div class="rounded-xl bg-white p-4 text-center shadow-sm">
                        <div class="text-xs text-gray-500">Hoàn thành</div>
                        <div class="text-xl font-bold text-blue-600">{{ summary.pct_completed }}%</div>
                    </div>
                </div>
                <div v-else class="rounded-xl bg-yellow-50 p-4 text-center text-yellow-700">Ngày này chưa được chốt.</div>

                <div class="rounded-xl bg-white p-4 shadow-sm">
                    <h3 class="mb-3 font-medium">Chi tiết task</h3>
                    <div v-for="i in instances" :key="i.id" class="flex justify-between border-b py-2 last:border-0">
                        <span>{{ i.template?.title }}</span>
                        <span :class="statusColor[i.status]" class="text-sm capitalize">{{ i.status }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
