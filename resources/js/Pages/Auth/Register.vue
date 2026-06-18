<script setup>
import { router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { useApi, unwrapApiData } from '@/composables/useApi';
import { useFormFields } from '@/composables/useFormFields';

const nameField = ref(null);
const emailField = ref(null);
const passwordField = ref(null);
const confirmField = ref(null);
const processing = ref(false);
const { validateAll } = useFormFields();

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = async () => {
    if (!validateAll([nameField.value, emailField.value, passwordField.value, confirmField.value]).isValid) {
        return;
    }

    processing.value = true;

    try {
        const response = await useApi(route('web_api.auth.register')).post({ ...form });
        const redirect = unwrapApiData(response)?.redirect ?? route('dashboard');
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
        <Head title="Đăng ký" />

        <form @submit.prevent="submit" class="space-y-4">
            <Field ref="nameField" v-model="form.name" :field="{ label: 'Tên', type: 'Text', validate: 'required|string|max:255' }" />
            <Field ref="emailField" v-model="form.email" :field="{ label: 'Email', type: 'Email', validate: 'required|email' }" />
            <Field ref="passwordField" v-model="form.password" :field="{ label: 'Mật khẩu', type: 'Password', validate: 'required|min:8' }" />
            <Field ref="confirmField" v-model="form.password_confirmation" :field="{ label: 'Xác nhận mật khẩu', type: 'Password', validate: 'required' }" />

            <div class="flex items-center justify-end gap-3">
                <Link :href="route('login')" class="text-sm text-muted-foreground underline hover:text-foreground">
                    Đã có tài khoản?
                </Link>
                <Button type="submit" :disabled="processing">Đăng ký</Button>
            </div>
        </form>
    </GuestLayout>
</template>
