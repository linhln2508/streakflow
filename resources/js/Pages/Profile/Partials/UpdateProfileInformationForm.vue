<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { useApi, unwrapApiData } from '@/composables/useApi';
import { useFormFields } from '@/composables/useFormFields';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const user = usePage().props.auth.user;
const nameField = ref(null);
const emailField = ref(null);
const processing = ref(false);
const recentlySuccessful = ref(false);
const verificationStatus = ref('');
const { validateAll } = useFormFields();

const form = reactive({
    name: user.name,
    email: user.email,
});

const submit = async () => {
    if (!validateAll([nameField.value, emailField.value]).isValid) {
        return;
    }

    processing.value = true;
    recentlySuccessful.value = false;

    try {
        await useApi(route('web_api.profile.update')).patch({ ...form });
        recentlySuccessful.value = true;
        router.reload({ only: ['auth'] });
    } finally {
        processing.value = false;
    }
};

const resendVerification = async () => {
    const response = await useApi(route('web_api.auth.verification_send')).post();
    verificationStatus.value = unwrapApiData(response)?.status ?? 'verification-link-sent';
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-semibold">Thông tin hồ sơ</h2>
            <p class="mt-1 text-sm text-muted-foreground">Cập nhật tên và email của bạn.</p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-4">
            <Field ref="nameField" v-model="form.name" :field="{ label: 'Tên', type: 'Text', validate: 'required|string|max:255' }" />
            <Field ref="emailField" v-model="form.email" :field="{ label: 'Email', type: 'Email', validate: 'required|email' }" />

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm">
                    Email chưa được xác minh.
                    <button type="button" class="text-primary underline" @click="resendVerification">
                        Gửi lại email xác minh
                    </button>
                </p>
                <p v-show="status === 'verification-link-sent' || verificationStatus === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                    Link xác minh mới đã được gửi.
                </p>
            </div>

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="processing">Lưu</Button>
                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="recentlySuccessful" class="text-sm text-muted-foreground">Đã lưu.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
