<script setup>
import { router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
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

const submit = async () => {
    if (!validateAll([emailField.value, passwordField.value]).isValid) {
        return;
    }

    processing.value = true;

    try {
        const response = await useApi(route('web_api.auth.login')).post({ ...form });
        const redirect = unwrapApiData(response)?.redirect ?? route('dashboard');
        router.visit(redirect);
    } finally {
        processing.value = false;
        form.password = '';
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Đăng nhập" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">{{ status }}</div>

        <form @submit.prevent="submit" class="space-y-4">
            <Field ref="emailField" v-model="form.email" :field="{ label: 'Email', type: 'Email', validate: 'required|email' }" />
            <Field ref="passwordField" v-model="form.password" :field="{ label: 'Mật khẩu', type: 'Password', validate: 'required' }" />

            <Field v-model="form.remember" :field="{ type: 'Checkbox', checkboxLabel: 'Ghi nhớ đăng nhập' }" />

            <div class="flex items-center justify-end gap-3">
                <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-muted-foreground underline hover:text-foreground">
                    Quên mật khẩu?
                </Link>
                <Button type="submit" :disabled="processing">
                    <DynamicIcon name="LogIn" size="14" class="mr-1" />
                    Đăng nhập
                </Button>
            </div>
        </form>
    </GuestLayout>
</template>
