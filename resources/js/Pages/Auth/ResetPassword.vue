<script setup>
import { router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { useApi, unwrapApiData } from '@/composables/useApi';
import { useFormFields } from '@/composables/useFormFields';

const props = defineProps({
    email: { type: String, required: true },
    token: { type: String, required: true },
});

const emailField = ref(null);
const passwordField = ref(null);
const confirmField = ref(null);
const processing = ref(false);
const { validateAll } = useFormFields();

const form = reactive({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = async () => {
    if (!validateAll([emailField.value, passwordField.value, confirmField.value]).isValid) {
        return;
    }

    processing.value = true;

    try {
        const response = await useApi(route('web_api.auth.reset_password')).post({ ...form });
        const redirect = unwrapApiData(response)?.redirect ?? route('login');
        router.visit(redirect);
    } finally {
        processing.value = false;
        form.password = '';
        form.password_confirmation = '';
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Đặt lại mật khẩu" />

        <form @submit.prevent="submit" class="space-y-4">
            <Field ref="emailField" v-model="form.email" :field="{ label: 'Email', type: 'Email', validate: 'required|email' }" />
            <Field ref="passwordField" v-model="form.password" :field="{ label: 'Mật khẩu mới', type: 'Password', validate: 'required|min:8' }" />
            <Field ref="confirmField" v-model="form.password_confirmation" :field="{ label: 'Xác nhận mật khẩu', type: 'Password', validate: 'required' }" />
            <div class="flex justify-end">
                <Button type="submit" :disabled="processing">Đặt lại mật khẩu</Button>
            </div>
        </form>
    </GuestLayout>
</template>
