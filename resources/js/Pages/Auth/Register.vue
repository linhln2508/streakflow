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
        router.visit(unwrapApiData(response)?.redirect ?? route('dashboard'));
    } finally {
        processing.value = false;
        form.password = '';
        form.password_confirmation = '';
    }
};
</script>

<template>
    <Head title="Đăng ký" />

    <div class="mb-8">
        <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/15 ring-1 ring-primary/20">
            <DynamicIcon name="UserPlus" size="26" class="text-primary" />
        </div>
        <h1 class="text-2xl font-bold tracking-tight">Tạo tài khoản</h1>
        <p class="mt-1.5 text-sm text-muted-foreground">Bắt đầu xây dựng thói quen hôm nay</p>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
        <Field ref="nameField" v-model="form.name" :field="{ label: 'Tên', type: 'Text', validate: 'required|string|max:255' }" />
        <Field ref="emailField" v-model="form.email" :field="{ label: 'Email', type: 'Email', validate: 'required|email' }" />
        <Field ref="passwordField" v-model="form.password" :field="{ label: 'Mật khẩu', type: 'Password', validate: 'required|min:8' }" />
        <Field ref="confirmField" v-model="form.password_confirmation" :field="{ label: 'Xác nhận mật khẩu', type: 'Password', validate: 'required' }" />
        <Button
            type="submit"
            :disabled="processing"
            class="mt-2 h-11 w-full rounded-xl bg-primary font-semibold text-primary-foreground shadow-md shadow-primary/25 hover:bg-primary/90"
        >
            {{ processing ? 'Đang tạo...' : 'Đăng ký' }}
        </Button>
    </form>

    <p class="mt-8 text-center text-sm text-muted-foreground">
        Đã có tài khoản?
        <Link :href="route('login')" class="font-semibold text-primary hover:underline">Đăng nhập</Link>
    </p>
</template>
