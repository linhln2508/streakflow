<script setup>
import { router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { demoAccounts } from '@/constants/navigation';
import { useApi, unwrapApiData } from '@/composables/useApi';
import { useFormFields } from '@/composables/useFormFields';

defineProps({
    canResetPassword: { type: Boolean },
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

const fillDemo = (email) => {
    form.email = email;
    form.password = 'password';
};

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
        form.password = '';
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

        <div class="flex items-center justify-between pt-1">
            <Field v-model="form.remember" :field="{ type: 'Checkbox', checkboxLabel: 'Ghi nhớ đăng nhập' }" />
            <Link
                v-if="canResetPassword"
                :href="route('password.request')"
                class="text-xs font-medium text-muted-foreground hover:text-primary"
            >
                Quên mật khẩu?
            </Link>
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

    <div class="relative my-8">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-border" />
        </div>
        <div class="relative flex justify-center text-xs uppercase">
            <span class="bg-background px-3 text-muted-foreground">Hoặc dùng demo</span>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <button
            v-for="acc in demoAccounts"
            :key="acc.email"
            type="button"
            class="group flex flex-col items-start gap-1 rounded-xl border border-border bg-card p-3.5 text-left transition-all hover:border-primary/40 hover:bg-accent hover:shadow-sm"
            @click="fillDemo(acc.email)"
        >
            <span class="flex items-center gap-1.5 text-sm font-semibold text-foreground">
                <DynamicIcon
                    :name="acc.email.includes('admin') ? 'Shield' : 'User'"
                    size="14"
                    class="text-primary"
                />
                {{ acc.label }}
            </span>
            <span class="truncate text-[11px] text-muted-foreground">{{ acc.email }}</span>
        </button>
    </div>
    <p class="mt-2 text-center text-[11px] text-muted-foreground">Mật khẩu: <code class="rounded bg-muted px-1 py-0.5 font-mono">password</code></p>

    <p class="mt-8 text-center text-sm text-muted-foreground">
        Chưa có tài khoản?
        <Link :href="route('register')" class="font-semibold text-primary hover:underline">Đăng ký ngay</Link>
    </p>
</template>
