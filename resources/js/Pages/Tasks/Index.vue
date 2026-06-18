<script setup>
import { router } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';

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

const toggleActive = async (id) => {
    await useApi(route('web_api.tasks.toggle', id)).patch();
    router.reload({ only: ['templates'] });
};

const deleteTask = async (id) => {
    if (!confirm('Xóa task template này?')) {
        return;
    }

    await useApi(route('web_api.tasks.destroy', id)).delete();
    router.reload({ only: ['templates'] });
};
</script>

<template>
    <Head title="Tasks" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Task Templates</h2>
                <Button as="a" :href="route('tasks.create')">
                    <DynamicIcon name="Plus" size="14" class="mr-1" />
                    Tạo task
                </Button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-4 px-4">
                <Card>
                    <CardContent class="flex flex-wrap gap-2 pt-6">
                        <Select :model-value="filters.category_id ?? ''" class="w-auto min-w-[160px]" @update:model-value="applyFilter('category_id', $event)">
                            <option value="">Tất cả danh mục</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </Select>
                        <Select :model-value="filters.recurrence_type ?? ''" class="w-auto min-w-[160px]" @update:model-value="applyFilter('recurrence_type', $event)">
                            <option value="">Tất cả loại</option>
                            <option v-for="(label, key) in recurrenceLabels" :key="key" :value="key">{{ label }}</option>
                        </Select>
                        <Select :model-value="filters.is_active ?? ''" class="w-auto min-w-[160px]" @update:model-value="applyFilter('is_active', $event)">
                            <option value="">Tất cả trạng thái</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </Select>
                    </CardContent>
                </Card>

                <Card v-for="t in templates" :key="t.id">
                    <CardContent class="flex items-center justify-between pt-6">
                        <div class="flex items-center gap-3">
                            <button @click="toggleActive(t.id)" class="text-muted-foreground hover:text-foreground" :title="t.is_active ? 'Tắt' : 'Bật'">
                                <DynamicIcon :name="t.is_active ? 'ToggleRight' : 'ToggleLeft'" size="22" />
                            </button>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span v-if="t.category" class="h-3 w-3 rounded-full" :style="{ backgroundColor: t.category.color }" />
                                    <span class="font-medium" :class="{ 'line-through text-muted-foreground': !t.is_active }">{{ t.title }}</span>
                                </div>
                                <div class="text-xs text-muted-foreground">{{ recurrenceLabels[t.recurrence_type] }} · {{ t.priority }}</div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Button as="a" :href="route('tasks.edit', t.id)" variant="outline" size="sm">
                                <DynamicIcon name="Pencil" size="14" />
                            </Button>
                            <Button variant="destructive" size="sm" @click="deleteTask(t.id)">
                                <DynamicIcon name="Trash2" size="14" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="templates.length === 0">
                    <CardContent class="py-8 text-center text-muted-foreground">Chưa có task nào.</CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
