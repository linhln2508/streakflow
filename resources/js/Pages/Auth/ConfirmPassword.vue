<script setup>
import { router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { useApi, unwrapApiData } from '@/composables/useApi';
import { useFormFields } from '@/composables/useFormFields';

const passwordField = ref(null);
const processing = ref(false);
const { validateAll } = useFormFields();

const form = reactive({ password: '' });

const submit = async () => {
    if (!validateAll([passwordField.value]).isValid) {
        return;
    }

    processing.value = true;

    try {
        const response = await useApi(route('web_api.auth.confirm_password')).post({ ...form });
        router.visit(unwrapApiData(response)?.redirect ?? route('dashboard'));
    } finally {
        processing.value = false;
        form.password = '';
        passwordField.value?.resetField?.();
    }
};
</script>

<template>
    <Head title="Xác nhận mật khẩu" />

    <div class="mb-6">
        <h1 class="text-2xl font-bold">Xác nhận mật khẩu</h1>
        <p class="mt-1 text-sm text-muted-foreground">Khu vực bảo mật — xác nhận trước khi tiếp tục</p>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
        <Field ref="passwordField" v-model="form.password" :field="{ label: 'Mật khẩu', type: 'Password', validate: 'required' }" />
        <Button type="submit" :disabled="processing" class="w-full">Xác nhận</Button>
    </form>
</template>
