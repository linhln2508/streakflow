<script setup>
import { router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { useApi } from '@/composables/useApi';
import { useFormFields } from '@/composables/useFormFields';

defineProps({ categories: Array });

const showForm = ref(false);
const editingId = ref(null);
const nameField = ref(null);
const colorField = ref(null);
const iconField = ref(null);
const { validateAll } = useFormFields();

const form = reactive({
    name: '',
    color: '#6366f1',
    icon: '',
});

const fields = {
    name: { label: 'Tên', type: 'Text', validate: 'required|string|max:255' },
    color: { label: 'Mã màu', type: 'Text', validate: 'required|regex:^#[0-9A-Fa-f]{6}$' },
    icon: { label: 'Icon (Lucide)', type: 'Text', validate: 'nullable|max:50', config: { help_text: 'Tên icon Lucide, ví dụ: Heart, Briefcase' } },
};

const resetForm = () => {
    form.name = '';
    form.color = '#6366f1';
    form.icon = '';
    editingId.value = null;
    showForm.value = false;
    [nameField, colorField, iconField].forEach((fieldRef) => fieldRef.value?.resetField?.());
};

const submit = async () => {
    if (!validateAll([nameField.value, colorField.value, iconField.value]).isValid) {
        return;
    }

    const payload = { ...form };

    if (editingId.value) {
        await useApi(route('web_api.categories.update', editingId.value)).put(payload);
    } else {
        await useApi(route('web_api.categories.store')).post(payload);
    }

    resetForm();
    router.reload({ only: ['categories'] });
};

const editCategory = (cat) => {
    editingId.value = cat.id;
    form.name = cat.name;
    form.color = cat.color;
    form.icon = cat.icon ?? '';
    showForm.value = true;
};

const deleteCategory = async (id) => {
    if (!confirm('Xóa danh mục này?')) {
        return;
    }

    await useApi(route('web_api.categories.destroy', id)).delete();
    router.reload({ only: ['categories'] });
};

const openCreate = () => {
    editingId.value = null;
    form.name = '';
    form.color = '#6366f1';
    form.icon = '';
    showForm.value = true;
};
</script>

<template>
    <Head title="Danh mục" />

    <PageHeader title="Danh mục" description="Nhóm task theo chủ đề với màu và icon">
        <template #actions>
            <Button @click="openCreate">
                <DynamicIcon name="Plus" class="mr-1" />
                Thêm
            </Button>
        </template>
    </PageHeader>

    <PageContainer size="narrow" class="space-y-4">
        <PageSection v-if="showForm" :title="editingId ? 'Cập nhật danh mục' : 'Tạo danh mục'">
            <form @submit.prevent="submit" class="space-y-4">
                <Field ref="nameField" v-model="form.name" :field="fields.name" />
                <Field ref="colorField" v-model="form.color" :field="fields.color">
                    <template #label-right>
                        <input v-model="form.color" type="color" class="h-8 w-10 rounded border border-input" />
                    </template>
                </Field>
                <Field ref="iconField" v-model="form.icon" :field="fields.icon" />
                <div class="flex gap-2">
                    <Button type="submit">{{ editingId ? 'Cập nhật' : 'Tạo' }}</Button>
                    <Button type="button" variant="ghost" @click="resetForm">Hủy</Button>
                </div>
            </form>
        </PageSection>

        <Card v-for="cat in categories" :key="cat.id">
            <CardContent class="flex items-center justify-between pt-6">
                <div class="flex items-center gap-3">
                    <span class="h-6 w-6 rounded-full" :style="{ backgroundColor: cat.color }" />
                    <DynamicIcon v-if="cat.icon" :name="cat.icon" size="18" />
                    <span class="font-medium">{{ cat.name }}</span>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" @click="editCategory(cat)">
                        <DynamicIcon name="Pencil" size="14" />
                    </Button>
                    <Button variant="destructive" size="sm" @click="deleteCategory(cat.id)">
                        <DynamicIcon name="Trash2" size="14" />
                    </Button>
                </div>
            </CardContent>
        </Card>

        <PageSection v-if="categories.length === 0 && !showForm">
            <EmptyState icon="Briefcase" title="Chưa có danh mục nào" description="Tạo danh mục để phân loại task template.">
                <template #action>
                    <Button @click="openCreate">
                        <DynamicIcon name="Plus" size="14" class="mr-1" />
                        Thêm danh mục
                    </Button>
                </template>
            </EmptyState>
        </PageSection>
    </PageContainer>
</template>
