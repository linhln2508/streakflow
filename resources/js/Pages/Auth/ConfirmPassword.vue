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
        const redirect = unwrapApiData(response)?.redirect ?? route('dashboard');
        router.visit(redirect);
    } finally {
        processing.value = false;
        form.password = '';
        passwordField.value?.resetField?.();
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Xác nhận mật khẩu" />

        <p class="mb-4 text-sm text-muted-foreground">
            Đây là khu vực bảo mật. Vui lòng xác nhận mật khẩu trước khi tiếp tục.
        </p>

        <form @submit.prevent="submit" class="space-y-4">
            <Field ref="passwordField" v-model="form.password" :field="{ label: 'Mật khẩu', type: 'Password', validate: 'required' }" />
            <div class="flex justify-end">
                <Button type="submit" :disabled="processing">Xác nhận</Button>
            </div>
        </form>
    </GuestLayout>
</template>
