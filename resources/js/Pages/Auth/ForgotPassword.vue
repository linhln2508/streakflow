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
    <GuestLayout>
        <Head title="Quên mật khẩu" />

        <p class="mb-4 text-sm text-muted-foreground">
            Nhập email của bạn, chúng tôi sẽ gửi link đặt lại mật khẩu.
        </p>

        <div v-if="status || statusMessage" class="mb-4 text-sm font-medium text-green-600">
            {{ status || statusMessage }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <Field ref="emailField" v-model="form.email" :field="{ label: 'Email', type: 'Email', validate: 'required|email' }" />
            <div class="flex justify-end">
                <Button type="submit" :disabled="processing">Gửi link reset</Button>
            </div>
        </form>
    </GuestLayout>
</template>
