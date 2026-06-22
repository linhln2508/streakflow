<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useApi, unwrapApiData } from '@/composables/useApi';

defineOptions({ layout: AuthLayout });

const props = defineProps({ status: { type: String } });

const processing = ref(false);
const localStatus = ref('');

const submit = async () => {
    processing.value = true;

    try {
        const response = await useApi(route('web_api.auth.verification_send')).post();
        localStatus.value = unwrapApiData(response)?.status ?? 'verification-link-sent';
    } finally {
        processing.value = false;
    }
};

const logout = async () => {
    const response = await useApi(route('web_api.auth.logout')).post();
    router.visit(unwrapApiData(response)?.redirect ?? '/');
};

const verificationLinkSent = computed(() =>
    props.status === 'verification-link-sent' || localStatus.value === 'verification-link-sent',
);
</script>

<template>
    <Head title="Xác minh email" />

    <div class="mb-6">
        <h1 class="text-2xl font-bold">Xác minh email</h1>
        <p class="mt-1 text-sm text-muted-foreground">
            Kiểm tra hộp thư hoặc yêu cầu gửi lại link xác minh.
        </p>
    </div>

    <div v-if="verificationLinkSent" class="mb-4 text-sm font-medium text-green-600">
        Link xác minh mới đã được gửi đến email của bạn.
    </div>

    <form @submit.prevent="submit" class="space-y-4">
        <Button type="submit" variant="emphasis" size="block" :disabled="processing">Gửi lại email xác minh</Button>
        <Button type="button" variant="outline" size="block" @click="logout">Đăng xuất</Button>
    </form>
</template>
