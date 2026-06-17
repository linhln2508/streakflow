<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ categories: Array });

const showForm = ref(false);
const editingId = ref(null);

const form = useForm({ name: '', color: '#6366f1', icon: '' });

const resetForm = () => {
    form.reset();
    form.color = '#6366f1';
    editingId.value = null;
    showForm.value = false;
};

const submit = () => {
    if (editingId.value) {
        form.put(route('categories.update', editingId.value), { onSuccess: resetForm });
    } else {
        form.post(route('categories.store'), { onSuccess: resetForm });
    }
};

const editCategory = (cat) => {
    editingId.value = cat.id;
    form.name = cat.name;
    form.color = cat.color;
    form.icon = cat.icon ?? '';
    showForm.value = true;
};

const deleteCategory = (id) => {
    if (confirm('Xóa danh mục này?')) form.delete(route('categories.destroy', id));
};
</script>

<template>
    <Head title="Danh mục" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Danh mục</h2>
                <PrimaryButton @click="showForm = true; editingId = null; form.reset()">+ Thêm</PrimaryButton>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl space-y-4 px-4">
                <form v-if="showForm" @submit.prevent="submit" class="rounded-xl bg-white p-4 shadow-sm space-y-3">
                    <InputLabel value="Tên" />
                    <TextInput v-model="form.name" class="w-full" required />
                    <InputLabel value="Màu" />
                    <div class="flex gap-2">
                        <input type="color" v-model="form.color" class="h-10 w-14 rounded" />
                        <TextInput v-model="form.color" class="flex-1" />
                    </div>
                    <InputLabel value="Icon (emoji)" />
                    <TextInput v-model="form.icon" class="w-full" placeholder="🏃" />
                    <div class="flex gap-2">
                        <PrimaryButton :disabled="form.processing">{{ editingId ? 'Cập nhật' : 'Tạo' }}</PrimaryButton>
                        <button type="button" @click="resetForm" class="text-sm text-gray-500">Hủy</button>
                    </div>
                </form>

                <div v-for="cat in categories" :key="cat.id" class="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="h-6 w-6 rounded-full" :style="{ backgroundColor: cat.color }" />
                        <span class="text-lg">{{ cat.icon }}</span>
                        <span class="font-medium">{{ cat.name }}</span>
                    </div>
                    <div class="flex gap-2">
                        <button @click="editCategory(cat)" class="text-sm text-indigo-600">Sửa</button>
                        <button @click="deleteCategory(cat.id)" class="text-sm text-red-600">Xóa</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
