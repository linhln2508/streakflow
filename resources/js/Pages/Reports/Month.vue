<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ year: Number, month: Number, summaries: Object, topSkipped: Array });

const daysInMonth = computed(() => new Date(props.year, props.month, 0).getDate());

const heatColor = (pct) => {
    if (pct >= 100) return 'bg-green-500 text-white';
    if (pct >= 75) return 'bg-green-300';
    if (pct >= 50) return 'bg-yellow-300';
    if (pct > 0) return 'bg-orange-300';
    return 'bg-gray-100 text-gray-400';
};

const getPct = (day) => {
    const dateStr = `${props.year}-${String(props.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    return props.summaries[dateStr]?.pct_completed ?? null;
};
</script>

<template>
    <Head :title="`Tháng ${month}/${year}`" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Báo cáo tháng {{ month }}/{{ year }}</h2>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-6 px-4">
                <div class="grid grid-cols-7 gap-1">
                    <div v-for="day in daysInMonth" :key="day" class="aspect-square">
                        <a
                            :href="route('reports.day', `${year}-${String(month).padStart(2,'0')}-${String(day).padStart(2,'0')}`)"
                            class="flex h-full items-center justify-center rounded text-xs font-medium"
                            :class="heatColor(getPct(day) ?? 0)"
                        >{{ day }}</a>
                    </div>
                </div>
                <div v-if="topSkipped.length" class="rounded-xl bg-white p-4 shadow-sm">
                    <h3 class="mb-3 font-medium">Task hay bị skip</h3>
                    <div v-for="t in topSkipped" :key="t.title" class="flex justify-between py-1">
                        <span>{{ t.title }}</span>
                        <span class="text-red-500">{{ t.count }} lần</span>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
