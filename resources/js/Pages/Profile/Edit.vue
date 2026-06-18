<script setup>
import { Head } from '@inertiajs/vue3';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});
</script>

<template>
    <Head title="Hồ sơ" />

    <PageHeader size="narrow" title="Hồ sơ" description="Quản lý thông tin tài khoản và mật khẩu" />

    <PageContainer size="narrow" class="space-y-6">
        <PageSection title="Thông tin cá nhân">
            <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
        </PageSection>

        <PageSection title="Mật khẩu">
            <UpdatePasswordForm />
        </PageSection>

        <PageSection title="Xóa tài khoản">
            <DeleteUserForm />
        </PageSection>
    </PageContainer>
</template>
