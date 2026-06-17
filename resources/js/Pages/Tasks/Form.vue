<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    template: Object,
    categories: Array,
});

const isEdit = computed(() => !!props.template);

const form = useForm({
    title: props.template?.title ?? '',
    description: props.template?.description ?? '',
    category_id: props.template?.category_id ?? '',
    priority: props.template?.priority ?? 'medium',
    recurrence_type: props.template?.recurrence_type ?? 'daily',
    recurrence_config: props.template?.recurrence_config ?? {},
    start_date: props.template?.start_date?.substring(0, 10) ?? new Date().toISOString().substring(0, 10),
    end_date: props.template?.end_date?.substring(0, 10) ?? '',
    sort_order: props.template?.sort_order ?? 0,
});

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

const submit = () => {
    if (isEdit.value) {
        form.put(route('tasks.update', props.template.id));
    } else {
        form.post(route('tasks.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Sửa task' : 'Tạo task'" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">{{ isEdit ? 'Sửa task' : 'Tạo task mới' }}</h2>
        </template>

        <div class="py-8">
            <form @submit.prevent="submit" class="mx-auto max-w-2xl space-y-6 rounded-xl bg-white p-6 shadow-sm">
                <div>
                    <InputLabel value="Tiêu đề" />
                    <TextInput v-model="form.title" class="mt-1 block w-full" required />
                    <InputError :message="form.errors.title" />
                </div>

                <div>
                    <InputLabel value="Mô tả" />
                    <textarea v-model="form.description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" rows="2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="Danh mục" />
                        <select v-model="form.category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Không có</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Ưu tiên" />
                        <select v-model="form.priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="low">Thấp</option>
                            <option value="medium">Trung bình</option>
                            <option value="high">Cao</option>
                        </select>
                    </div>
                </div>

                <div>
                    <InputLabel value="Loại lặp lại" />
                    <select v-model="form.recurrence_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="daily">Hàng ngày</option>
                        <option value="weekdays">Thứ 2 – Thứ 6</option>
                        <option value="weekly">Theo tuần (chọn thứ)</option>
                        <option value="monthly">Theo tháng (chọn ngày)</option>
                        <option value="custom">Tùy chỉnh (mỗi N ngày)</option>
                        <option value="one_time">Một lần</option>
                    </select>
                </div>

                <div v-if="form.recurrence_type === 'weekly'" class="flex flex-wrap gap-2">
                    <button
                        v-for="d in weekDays" :key="d.value" type="button"
                        @click="toggleWeekDay(d.value)"
                        class="rounded-lg px-3 py-1.5 text-sm"
                        :class="(form.recurrence_config.days ?? []).includes(d.value) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600'"
                    >{{ d.label }}</button>
                </div>

                <div v-if="form.recurrence_type === 'monthly'">
                    <InputLabel value="Ngày trong tháng (VD: 1,15)" />
                    <TextInput
                        :model-value="(form.recurrence_config.days ?? []).join(',')"
                        @update:model-value="form.recurrence_config = { days: $event.split(',').map(Number).filter(Boolean) }"
                        class="mt-1 block w-full" placeholder="1,15"
                    />
                </div>

                <div v-if="form.recurrence_type === 'custom'">
                    <InputLabel value="Mỗi N ngày" />
                    <TextInput
                        type="number" min="1"
                        :model-value="form.recurrence_config.interval ?? 1"
                        @update:model-value="form.recurrence_config = { interval: parseInt($event) || 1, unit: 'day' }"
                        class="mt-1 block w-full"
                    />
                </div>

                <div v-if="form.recurrence_type === 'one_time'">
                    <InputLabel value="Ngày thực hiện" />
                    <TextInput type="date" :model-value="form.recurrence_config.date" @update:model-value="form.recurrence_config = { date: $event }" class="mt-1 block w-full" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="Ngày bắt đầu" />
                        <TextInput type="date" v-model="form.start_date" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <InputLabel value="Ngày kết thúc (tùy chọn)" />
                        <TextInput type="date" v-model="form.end_date" class="mt-1 block w-full" />
                    </div>
                </div>

                <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Cập nhật' : 'Tạo task' }}</PrimaryButton>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
