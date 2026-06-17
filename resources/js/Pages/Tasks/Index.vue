<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    templates: Array,
    categories: Array,
    filters: Object,
});

const recurrenceLabels = {
    daily: 'Hàng ngày',
    weekdays: 'Thứ 2–6',
    weekly: 'Theo tuần',
    monthly: 'Theo tháng',
    custom: 'Tùy chỉnh',
    one_time: 'Một lần',
};

const applyFilter = (key, value) => {
    router.get(route('tasks.index'), { ...props.filters, [key]: value || undefined }, { preserveState: true });
};

const toggleActive = (id) => router.patch(route('tasks.toggle', id));
const deleteTask = (id) => {
    if (confirm('Xóa task template này?')) router.delete(route('tasks.destroy', id));
};
</script>

<template>
    <Head title="Tasks" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Task Templates</h2>
                <Link :href="route('tasks.create')">
                    <PrimaryButton>+ Tạo task</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-4 px-4">
                <div class="flex flex-wrap gap-2">
                    <select @change="applyFilter('category_id', $event.target.value)" class="rounded-lg border-gray-300 text-sm">
                        <option value="">Tất cả danh mục</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id" :selected="filters.category_id == c.id">{{ c.name }}</option>
                    </select>
                    <select @change="applyFilter('recurrence_type', $event.target.value)" class="rounded-lg border-gray-300 text-sm">
                        <option value="">Tất cả loại</option>
                        <option v-for="(label, key) in recurrenceLabels" :key="key" :value="key" :selected="filters.recurrence_type === key">{{ label }}</option>
                    </select>
                    <select @change="applyFilter('is_active', $event.target.value)" class="rounded-lg border-gray-300 text-sm">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" :selected="filters.is_active === '1'">Active</option>
                        <option value="0" :selected="filters.is_active === '0'">Inactive</option>
                    </select>
                </div>

                <div v-for="t in templates" :key="t.id" class="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <button @click="toggleActive(t.id)" class="text-2xl" :title="t.is_active ? 'Tắt' : 'Bật'">
                            {{ t.is_active ? '✅' : '⏸️' }}
                        </button>
                        <div>
                            <div class="flex items-center gap-2">
                                <span v-if="t.category" class="h-3 w-3 rounded-full" :style="{ backgroundColor: t.category.color }" />
                                <span class="font-medium" :class="{ 'line-through text-gray-400': !t.is_active }">{{ t.title }}</span>
                            </div>
                            <div class="text-xs text-gray-400">{{ recurrenceLabels[t.recurrence_type] }} · {{ t.priority }}</div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="route('tasks.edit', t.id)" class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm hover:bg-gray-200">Sửa</Link>
                        <button @click="deleteTask(t.id)" class="rounded-lg bg-red-50 px-3 py-1.5 text-sm text-red-600 hover:bg-red-100">Xóa</button>
                    </div>
                </div>

                <div v-if="templates.length === 0" class="rounded-xl bg-white p-8 text-center text-gray-400">
                    Chưa có task nào.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
