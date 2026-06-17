<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    year: Number, week: Number, start: String, end: String,
    summaries: Array, totalHpChange: Number, totalXpEarned: Number,
});

const heatColor = (pct) => {
    if (pct >= 100) return 'bg-green-500';
    if (pct >= 75) return 'bg-green-300';
    if (pct >= 50) return 'bg-yellow-300';
    if (pct > 0) return 'bg-orange-300';
    return 'bg-gray-200';
};

const days = computed(() => {
    const result = [];
    const start = new Date(props.start);
    for (let i = 0; i < 7; i++) {
        const d = new Date(start);
        d.setDate(d.getDate() + i);
        const dateStr = d.toISOString().substring(0, 10);
        const summary = props.summaries.find(s => s.date.substring(0, 10) === dateStr);
        result.push({ date: dateStr, label: ['T2','T3','T4','T5','T6','T7','CN'][i], pct: summary?.pct_completed ?? 0, summary });
    }
    return result;
});
</script>

<template>
    <Head :title="`Tuần ${week}/${year}`" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Báo cáo tuần {{ week }}/{{ year }}</h2>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-6 px-4">
                <div class="flex justify-between rounded-xl bg-white p-4 shadow-sm">
                    <div><span class="text-gray-500">Tổng HP:</span> <strong :class="totalHpChange >= 0 ? 'text-green-600' : 'text-red-600'">{{ totalHpChange >= 0 ? '+' : '' }}{{ totalHpChange }}</strong></div>
                    <div><span class="text-gray-500">Tổng XP:</span> <strong class="text-amber-600">+{{ totalXpEarned }}</strong></div>
                </div>
                <div class="grid grid-cols-7 gap-2">
                    <div v-for="d in days" :key="d.date" class="text-center">
                        <div class="text-xs text-gray-500 mb-1">{{ d.label }}</div>
                        <a :href="route('reports.day', d.date)" class="block rounded-lg p-4 text-white text-sm font-bold" :class="heatColor(d.pct)">
                            {{ d.pct }}%
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
