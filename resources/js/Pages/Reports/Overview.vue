<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    user: Object,
    xpToNextLevel: Number,
    xpForNextLevel: Number,
    longestStreak: Number,
    badges: Array,
});

const now = new Date();
</script>

<template>
    <Head title="Tổng quan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Tổng quan</h2>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-6 px-4">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div class="rounded-xl bg-white p-4 shadow-sm text-center">
                        <div class="text-3xl font-bold text-amber-600">Lv.{{ user.level }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ user.xp }} XP</div>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm text-center">
                        <div class="text-3xl font-bold text-orange-600">🔥 {{ user.streak_count }}</div>
                        <div class="text-xs text-gray-500 mt-1">Streak hiện tại</div>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm text-center">
                        <div class="text-3xl font-bold text-red-600">❤️ {{ user.hp }}</div>
                        <div class="text-xs text-gray-500 mt-1">HP</div>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-4 shadow-sm">
                    <div class="flex justify-between text-sm mb-2">
                        <span>XP đến level {{ user.level + 1 }}</span>
                        <span>{{ xpToNextLevel }} XP còn lại</span>
                    </div>
                    <div class="h-3 rounded-full bg-gray-200">
                        <div class="h-3 rounded-full bg-amber-500" :style="{ width: Math.min(100, (user.xp / xpForNextLevel) * 100) + '%' }" />
                    </div>
                    <p class="mt-2 text-xs text-gray-400">Streak dài nhất: {{ longestStreak }} ngày</p>
                </div>

                <div class="flex gap-3">
                    <Link :href="route('reports.week', { year: now.getFullYear(), week: Math.ceil((now - new Date(now.getFullYear(), 0, 1)) / 86400000 / 7) })" class="rounded-lg bg-indigo-50 px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-100">Tuần này</Link>
                    <Link :href="route('reports.month', { year: now.getFullYear(), month: now.getMonth() + 1 })" class="rounded-lg bg-indigo-50 px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-100">Tháng này</Link>
                </div>

                <div class="rounded-xl bg-white p-4 shadow-sm">
                    <h3 class="mb-3 font-medium">Badges ({{ badges.length }})</h3>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div v-for="b in badges" :key="b.id" class="rounded-lg bg-gray-50 p-3 text-center">
                            <div class="text-2xl">{{ b.icon }}</div>
                            <div class="mt-1 text-xs font-medium">{{ b.name }}</div>
                        </div>
                    </div>
                    <p v-if="badges.length === 0" class="text-center text-gray-400 text-sm">Chưa có badge nào.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
