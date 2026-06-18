<script setup>
import { router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { useApi, unwrapApiData } from '@/composables/useApi';
import { useFormFields } from '@/composables/useFormFields';

defineProps({
    status: { type: String },
});

const emailField = ref(null);
const passwordField = ref(null);
const processing = ref(false);
const { validateAll } = useFormFields();

const form = reactive({
    email: '',
    password: '',
    remember: false,
});

const submit = async () => {
    if (!validateAll([emailField.value, passwordField.value]).isValid) {
        return;
    }

    processing.value = true;

    try {
        const response = await useApi(route('web_api.auth.login')).post({ ...form });
        router.visit(unwrapApiData(response)?.redirect ?? route('dashboard'));
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <Head title="Đăng nhập" />

    <div class="mb-8">
        <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/15 ring-1 ring-primary/20">
            <DynamicIcon name="LogIn" size="26" class="text-primary" />
        </div>
        <h1 class="text-2xl font-bold tracking-tight">Đăng nhập</h1>
        <p class="mt-1.5 text-sm text-muted-foreground">Chào mừng trở lại Linh Tinh</p>
    </div>

    <div v-if="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        {{ status }}
    </div>

    <form @submit.prevent="submit" class="space-y-4">
        <Field ref="emailField" v-model="form.email" :field="{ label: 'Email', type: 'Email', validate: 'required|email' }" />
        <Field ref="passwordField" v-model="form.password" :field="{ label: 'Mật khẩu', type: 'Password', validate: 'required' }" />

        <div class="pt-1">
            <Field v-model="form.remember" :field="{ type: 'Checkbox', checkboxLabel: 'Ghi nhớ đăng nhập' }" />
        </div>

        <Button
            type="submit"
            :disabled="processing"
            class="mt-2 h-11 w-full gap-2 rounded-xl bg-primary font-semibold text-primary-foreground shadow-md shadow-primary/25 hover:bg-primary/90"
        >
            <DynamicIcon name="LogIn" size="16" />
            {{ processing ? 'Đang đăng nhập...' : 'Đăng nhập' }}
        </Button>
    </form>

    <p class="mt-8 text-center text-sm text-muted-foreground">
        Chưa có tài khoản?
        <Link :href="route('register')" class="font-semibold text-primary hover:underline">Đăng ký ngay</Link>
    </p>
</template>
