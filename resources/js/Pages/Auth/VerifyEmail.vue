<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useApi, unwrapApiData } from '@/composables/useApi';

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
    <GuestLayout>
        <Head title="Xác minh email" />

        <p class="mb-4 text-sm text-muted-foreground">
            Cảm ơn bạn đã đăng ký! Vui lòng xác minh email bằng link chúng tôi đã gửi. Nếu chưa nhận được, bạn có thể yêu cầu gửi lại.
        </p>

        <div v-if="verificationLinkSent" class="mb-4 text-sm font-medium text-green-600">
            Link xác minh mới đã được gửi đến email của bạn.
        </div>

        <form @submit.prevent="submit">
            <div class="flex items-center justify-between gap-3">
                <Button type="submit" :disabled="processing">Gửi lại email xác minh</Button>
                <button
                    type="button"
                    class="text-sm text-muted-foreground underline hover:text-foreground"
                    @click="logout"
                >
                    Đăng xuất
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
