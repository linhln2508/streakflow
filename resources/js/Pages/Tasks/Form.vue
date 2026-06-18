<script setup>
import { router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { useApi } from '@/composables/useApi';
import { useFormFields } from '@/composables/useFormFields';

const props = defineProps({
    template: Object,
    categories: Array,
});

const isEdit = computed(() => !!props.template);
const titleField = ref(null);
const startDateField = ref(null);
const { validateAll } = useFormFields();

const form = reactive({
    title: props.template?.title ?? '',
    description: props.template?.description ?? '',
    category_id: props.template?.category_id ?? '',
    priority: props.template?.priority ?? 'medium',
    recurrence_type: props.template?.recurrence_type ?? 'daily',
    recurrence_config: { ...(props.template?.recurrence_config ?? {}) },
    start_date: props.template?.start_date?.substring(0, 10) ?? new Date().toISOString().substring(0, 10),
    end_date: props.template?.end_date?.substring(0, 10) ?? '',
    due_time: props.template?.due_time ? String(props.template.due_time).substring(0, 5) : '',
    sort_order: props.template?.sort_order ?? 0,
});

const monthlyDaysInput = computed({
    get: () => (form.recurrence_config.days ?? []).join(','),
    set: (value) => {
        form.recurrence_config = {
            ...form.recurrence_config,
            days: String(value).split(',').map(Number).filter(Boolean),
        };
    },
});

const customInterval = computed({
    get: () => form.recurrence_config.interval ?? 1,
    set: (value) => {
        form.recurrence_config = {
            ...form.recurrence_config,
            interval: parseInt(value) || 1,
            unit: 'day',
        };
    },
});

const oneTimeDate = computed({
    get: () => form.recurrence_config.date ?? '',
    set: (value) => {
        form.recurrence_config = { date: value };
    },
});

const categoryOptions = computed(() => [
    { value: '', label: 'Không có' },
    ...props.categories.map((c) => ({ value: c.id, label: c.name })),
]);

const weekDays = [
    { value: 1, label: 'T2' }, { value: 2, label: 'T3' }, { value: 3, label: 'T4' },
    { value: 4, label: 'T5' }, { value: 5, label: 'T6' }, { value: 6, label: 'T7' },
    { value: 7, label: 'CN' },
];

const toggleWeekDay = (day) => {
    const days = form.recurrence_config.days ?? [];
    const idx = days.indexOf(day);
    if (idx >= 0) days.splice(idx, 1);
    else days.push(day);
    form.recurrence_config = { ...form.recurrence_config, days: [...days].sort() };
};

const submit = async () => {
    if (!validateAll([titleField.value, startDateField.value]).isValid) {
        return;
    }

    const payload = { ...form, category_id: form.category_id || null, end_date: form.end_date || null, due_time: form.due_time || null };

    if (isEdit.value) {
        await useApi(route('web_api.tasks.update', props.template.id)).put(payload);
    } else {
        await useApi(route('web_api.tasks.store')).post(payload);
    }

    router.visit(route('tasks.index'));
};
</script>

<template>
    <Head :title="isEdit ? 'Sửa task' : 'Tạo task'" />

    <PageHeader
        size="narrow"
        :title="isEdit ? 'Sửa task' : 'Tạo task mới'"
        :breadcrumbs="[
            { label: 'Tasks', href: route('tasks.index') },
            { label: isEdit ? 'Sửa' : 'Tạo mới' },
        ]"
    />

    <PageContainer size="narrow">
        <PageSection>
            <form @submit.prevent="submit" class="space-y-4">
                        <Field ref="titleField" v-model="form.title" :field="{ label: 'Tiêu đề', type: 'Text', validate: 'required|string|max:255' }" />
                        <Field v-model="form.description" :field="{ label: 'Mô tả', type: 'Textarea' }" />

                        <div class="grid grid-cols-2 gap-4">
                            <Field v-model="form.category_id" :field="{ label: 'Danh mục', type: 'Select', placeholder: 'Không có', options: categoryOptions }" />
                            <Field v-model="form.priority" :field="{ label: 'Ưu tiên', type: 'Select', options: [{ value: 'low', label: 'Thấp' }, { value: 'medium', label: 'Trung bình' }, { value: 'high', label: 'Cao' }] }" />
                        </div>

                        <Field v-model="form.recurrence_type" :field="{ label: 'Loại lặp lại', type: 'Select', options: [{ value: 'daily', label: 'Hàng ngày' }, { value: 'weekdays', label: 'Thứ 2 – Thứ 6' }, { value: 'weekly', label: 'Theo tuần' }, { value: 'monthly', label: 'Theo tháng' }, { value: 'custom', label: 'Tùy chỉnh' }, { value: 'one_time', label: 'Một lần' }] }" />

                        <div v-if="form.recurrence_type === 'weekly'" class="flex flex-wrap gap-2">
                            <Button v-for="d in weekDays" :key="d.value" type="button" size="sm" :variant="(form.recurrence_config.days ?? []).includes(d.value) ? 'default' : 'outline'" @click="toggleWeekDay(d.value)">
                                {{ d.label }}
                            </Button>
                        </div>

                        <Field v-if="form.recurrence_type === 'monthly'" v-model="monthlyDaysInput" :field="{ label: 'Ngày trong tháng (VD: 1,15)', type: 'Text', validate: 'required' }" />
                        <Field v-if="form.recurrence_type === 'custom'" v-model="customInterval" :field="{ label: 'Mỗi N ngày', type: 'Number', validate: 'required|integer|min:1' }" />
                        <Field v-if="form.recurrence_type === 'one_time'" v-model="oneTimeDate" :field="{ label: 'Ngày thực hiện', type: 'Date', validate: 'required|date' }" />

                        <div class="grid grid-cols-2 gap-4">
                            <Field ref="startDateField" v-model="form.start_date" :field="{ label: 'Ngày bắt đầu', type: 'Date', validate: 'required|date' }" />
                            <Field v-model="form.end_date" :field="{ label: 'Ngày kết thúc (tùy chọn)', type: 'Date' }" />
                        </div>

                        <div class="space-y-2">
                            <Label>Giờ hạn (tùy chọn)</Label>
                            <Input v-model="form.due_time" type="time" class="rounded-xl" />
                            <p class="text-xs text-muted-foreground">Nếu quá giờ hạn mà chưa hoàn thành, task sẽ hiển thị cảnh báo đỏ trên trang Hôm nay.</p>
                        </div>

                        <Button type="submit">
                            <DynamicIcon name="Save" size="14" class="mr-1" />
                            {{ isEdit ? 'Cập nhật' : 'Tạo task' }}
                        </Button>
                    </form>
        </PageSection>
    </PageContainer>
</template>
