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

const priorityVariant = {
    low: 'secondary',
    medium: 'default',
    high: 'destructive',
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

    <PageHeader title="Task Templates" description="Thiết lập task một lần — hệ thống tự tạo instance mỗi ngày">
        <template #actions>
            <Button as="a" :href="route('tasks.create')" class="rounded-full shadow-md shadow-primary/20">
                <DynamicIcon name="Plus" size="14" />
                Tạo task
            </Button>
        </template>
    </PageHeader>

    <PageContainer class="space-y-6">
        <PageSection title="Bộ lọc">
            <div class="flex flex-wrap gap-2">
                <Select :model-value="filters.category_id ?? ''" class="w-auto min-w-[160px] rounded-xl" @update:model-value="applyFilter('category_id', $event)">
                    <option value="">Tất cả danh mục</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </Select>
                <Select :model-value="filters.recurrence_type ?? ''" class="w-auto min-w-[160px] rounded-xl" @update:model-value="applyFilter('recurrence_type', $event)">
                    <option value="">Tất cả loại</option>
                    <option v-for="(label, key) in recurrenceLabels" :key="key" :value="key">{{ label }}</option>
                </Select>
                <Select :model-value="filters.is_active ?? ''" class="w-auto min-w-[160px] rounded-xl" @update:model-value="applyFilter('is_active', $event)">
                    <option value="">Tất cả trạng thái</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </Select>
            </div>
        </PageSection>

        <div v-if="templates.length" class="space-y-3">
            <div
                v-for="t in templates"
                :key="t.id"
                class="flex items-center justify-between gap-4 rounded-2xl border border-border/60 bg-card p-4 shadow-sm transition-all hover:border-primary/20 hover:shadow-md"
            >
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        class="shrink-0 rounded-lg p-1 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                        :title="t.is_active ? 'Tắt' : 'Bật'"
                        @click="toggleActive(t.id)"
                    >
                        <DynamicIcon :name="t.is_active ? 'ToggleRight' : 'ToggleLeft'" size="22" :class="t.is_active ? 'text-primary' : ''" />
                    </button>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span v-if="t.category" class="h-2.5 w-2.5 rounded-full ring-2 ring-white" :style="{ backgroundColor: t.category.color }" />
                            <span class="font-medium" :class="{ 'line-through text-muted-foreground': !t.is_active }">{{ t.title }}</span>
                            <Badge :variant="priorityVariant[t.priority] ?? 'secondary'" class="text-[10px] uppercase">{{ t.priority }}</Badge>
                        </div>
                        <p class="mt-0.5 text-xs text-muted-foreground">{{ recurrenceLabels[t.recurrence_type] }}</p>
                    </div>
                </div>
                <div class="flex shrink-0 gap-2">
                    <Button as="a" :href="route('tasks.edit', t.id)" variant="outline" size="sm" class="rounded-full">
                        <DynamicIcon name="Pencil" size="14" />
                    </Button>
                    <Button variant="destructive" size="sm" class="rounded-full" @click="deleteTask(t.id)">
                        <DynamicIcon name="Trash2" size="14" />
                    </Button>
                </div>
            </div>
        </div>

        <PageSection v-else no-padding>
            <EmptyState icon="CheckCircle2" title="Chưa có task template" description="Tạo template để bắt đầu theo dõi thói quen.">
                <template #action>
                    <Button as="a" :href="route('tasks.create')" class="rounded-full px-6 shadow-md shadow-primary/20">
                        <DynamicIcon name="Plus" size="14" />
                        Tạo task
                    </Button>
                </template>
            </EmptyState>
        </PageSection>
    </PageContainer>
</template>
