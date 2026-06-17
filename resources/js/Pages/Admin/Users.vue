<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ users: Object, filters: Object });

const search = ref('');
const applySearch = () => router.get(route('admin.users'), { search: search.value }, { preserveState: true });
</script>

<template>
    <Head title="Admin - Users" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Quản lý Users</h2>
                <Link :href="route('admin.analytics')" class="text-sm text-indigo-600">Analytics →</Link>
            </div>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4">
                <form @submit.prevent="applySearch" class="mb-4 flex gap-2">
                    <input v-model="search" placeholder="Tìm kiếm..." class="rounded-lg border-gray-300 text-sm flex-1" />
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white">Tìm</button>
                </form>
                <table class="w-full rounded-xl bg-white shadow-sm overflow-hidden">
                    <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3">Tên</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Level</th>
                            <th class="px-4 py-3">Streak</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in users.data" :key="u.id" class="border-t">
                            <td class="px-4 py-3">{{ u.name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ u.email }}</td>
                            <td class="px-4 py-3"><span class="rounded px-2 py-0.5 text-xs" :class="u.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100'">{{ u.role }}</span></td>
                            <td class="px-4 py-3">Lv.{{ u.level }}</td>
                            <td class="px-4 py-3">🔥 {{ u.streak_count }}</td>
                            <td class="px-4 py-3"><Link :href="route('admin.users.show', u.id)" class="text-sm text-indigo-600">Chi tiết</Link></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
