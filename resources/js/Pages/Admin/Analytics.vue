<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ stats: Object, dailyActivity: Array });

const maxActivity = Math.max(...(dailyActivity?.map(d => d.count) ?? [1]), 1);
</script>

<template>
    <Head title="Admin - Analytics" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Analytics</h2>
                <Link :href="route('admin.users')" class="text-sm text-indigo-600">← Users</Link>
            </div>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-4xl space-y-6 px-4">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div v-for="(val, key) in stats" :key="key" class="rounded-xl bg-white p-4 text-center shadow-sm">
                        <div class="text-2xl font-bold text-indigo-600">{{ val }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ key.replace(/_/g, ' ') }}</div>
                    </div>
                </div>
                <div class="rounded-xl bg-white p-4 shadow-sm">
                    <h3 class="mb-4 font-medium">Hoạt động 30 ngày</h3>
                    <div class="flex items-end gap-1 h-32">
                        <div v-for="d in dailyActivity" :key="d.date" class="flex-1 bg-indigo-500 rounded-t" :style="{ height: (d.count / maxActivity * 100) + '%' }" :title="`${d.date}: ${d.count}`" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
