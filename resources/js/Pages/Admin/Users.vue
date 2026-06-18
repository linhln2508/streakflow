<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import Input from '@/Components/ui/Input.vue';
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
                <h2 class="text-xl font-semibold">Quản lý Users</h2>
                <Link :href="route('admin.analytics')" class="text-sm text-primary hover:underline">Analytics →</Link>
            </div>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4">
                <form @submit.prevent="applySearch" class="mb-4 flex gap-2">
                    <Input v-model="search" placeholder="Tìm kiếm..." class="flex-1" />
                    <Button type="submit">Tìm</Button>
                </form>
                <Card>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b bg-muted/50 text-xs uppercase text-muted-foreground">
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
                                <tr v-for="u in users.data" :key="u.id" class="border-b last:border-0">
                                    <td class="px-4 py-3 font-medium">{{ u.name }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ u.email }}</td>
                                    <td class="px-4 py-3">
                                        <Badge :variant="u.role === 'admin' ? 'default' : 'secondary'">{{ u.role }}</Badge>
                                    </td>
                                    <td class="px-4 py-3">Lv.{{ u.level }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1">
                                            <DynamicIcon name="Flame" size="14" class="text-orange-500" />
                                            {{ u.streak_count }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <Link :href="route('admin.users.show', u.id)" class="text-primary hover:underline">Chi tiết</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
