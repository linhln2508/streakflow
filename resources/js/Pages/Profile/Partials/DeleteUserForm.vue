<script setup>
import { router } from '@inertiajs/vue3';
import { nextTick, reactive, ref } from 'vue';
import { useApi, unwrapApiData } from '@/composables/useApi';
import { useFormFields } from '@/composables/useFormFields';

const confirmingUserDeletion = ref(false);
const passwordField = ref(null);
const processing = ref(false);
const { validateAll } = useFormFields();

const form = reactive({ password: '' });

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordField.value?.validate?.());
};

const deleteUser = async () => {
    if (!validateAll([passwordField.value]).isValid) {
        return;
    }

    processing.value = true;

    try {
        const response = await useApi(route('web_api.profile.destroy')).delete({ password: form.password });
        const redirect = unwrapApiData(response)?.redirect ?? '/';
        router.visit(redirect);
    } finally {
        processing.value = false;
        closeModal();
    }
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.password = '';
    passwordField.value?.resetField?.();
};
</script>

<template>
    <div class="space-y-4">
        <p class="text-sm text-muted-foreground">
            Sau khi xóa, toàn bộ dữ liệu sẽ bị xóa vĩnh viễn. Hãy chắc chắn trước khi thực hiện.
        </p>

        <Button variant="destructive" @click="confirmUserDeletion">Xóa tài khoản</Button>

        <Dialog :open="confirmingUserDeletion" @update:open="confirmingUserDeletion = $event">
            <template #title>Xác nhận xóa tài khoản?</template>
            <template #description>
                Nhập mật khẩu để xác nhận xóa vĩnh viễn tài khoản của bạn.
            </template>

            <Field ref="passwordField" v-model="form.password" :field="{ label: 'Mật khẩu', type: 'Password', validate: 'required' }" />

            <div class="mt-6 flex justify-end gap-3">
                <Button variant="outline" @click="closeModal">Hủy</Button>
                <Button variant="destructive" :disabled="processing" @click="deleteUser">
                    Xóa tài khoản
                </Button>
            </div>
        </Dialog>
    </div>
</template>
