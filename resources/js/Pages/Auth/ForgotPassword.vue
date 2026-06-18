<script setup>
import { reactive, ref } from 'vue';
import { useApi, unwrapApiData } from '@/composables/useApi';
import { useFormFields } from '@/composables/useFormFields';

defineProps({ status: { type: String } });

const emailField = ref(null);
const processing = ref(false);
const statusMessage = ref('');
const { validateAll } = useFormFields();

const form = reactive({ email: '' });

const submit = async () => {
    if (!validateAll([emailField.value]).isValid) {
        return;
    }

    processing.value = true;

    try {
        const response = await useApi(route('web_api.auth.forgot_password')).post({ ...form });
        statusMessage.value = unwrapApiData(response)?.status ?? response?.data?.message ?? '';
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <Head title="Quên mật khẩu" />

    <div class="mb-6">
        <h1 class="text-2xl font-bold">Quên mật khẩu</h1>
        <p class="mt-1 text-sm text-muted-foreground">Nhập email để nhận link đặt lại mật khẩu</p>
    </div>

    <div v-if="status || statusMessage" class="mb-4 text-sm font-medium text-green-600">
        {{ status || statusMessage }}
    </div>

    <form @submit.prevent="submit" class="space-y-4">
        <Field ref="emailField" v-model="form.email" :field="{ label: 'Email', type: 'Email', validate: 'required|email' }" />
        <Button type="submit" :disabled="processing" class="w-full">Gửi link reset</Button>
    </form>

    <p class="mt-4 text-center text-sm text-muted-foreground">
        <Link :href="route('login')" class="text-primary underline">Quay lại đăng nhập</Link>
    </p>
</template>
