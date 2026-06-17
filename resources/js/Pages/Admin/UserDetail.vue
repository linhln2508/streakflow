<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ profile: Object, summaries: Array });
</script>

<template>
    <Head :title="profile.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.users')" class="text-sm text-gray-500">← Users</Link>
                <h2 class="text-xl font-semibold text-gray-800">{{ profile.name }}</h2>
            </div>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-6 px-4">
                <div class="grid grid-cols-4 gap-4">
                    <div class="rounded-xl bg-white p-4 text-center shadow-sm"><div class="text-2xl font-bold text-amber-600">Lv.{{ profile.level }}</div></div>
                    <div class="rounded-xl bg-white p-4 text-center shadow-sm"><div class="text-2xl font-bold text-red-600">❤️ {{ profile.hp }}</div></div>
                    <div class="rounded-xl bg-white p-4 text-center shadow-sm"><div class="text-2xl font-bold text-orange-600">🔥 {{ profile.streak_count }}</div></div>
                    <div class="rounded-xl bg-white p-4 text-center shadow-sm"><div class="text-2xl font-bold">{{ profile.xp }} XP</div></div>
                </div>
                <div class="rounded-xl bg-white p-4 shadow-sm">
                    <h3 class="mb-3 font-medium">30 ngày gần nhất</h3>
                    <div v-for="s in summaries" :key="s.id" class="flex justify-between border-b py-2 text-sm">
                        <span>{{ s.date }}</span>
                        <span>{{ s.pct_completed }}% · +{{ s.xp_earned }} XP</span>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
