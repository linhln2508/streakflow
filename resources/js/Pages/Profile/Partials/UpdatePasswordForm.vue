<script setup>
import { reactive, ref } from 'vue';
import { useApi } from '@/composables/useApi';
import { useFormFields } from '@/composables/useFormFields';

const currentField = ref(null);
const passwordField = ref(null);
const confirmField = ref(null);
const processing = ref(false);
const recentlySuccessful = ref(false);
const { validateAll } = useFormFields();

const form = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = async () => {
    if (!validateAll([currentField.value, passwordField.value, confirmField.value]).isValid) {
        return;
    }

    processing.value = true;
    recentlySuccessful.value = false;

    try {
        await useApi(route('web_api.profile.password')).put({ ...form });
        form.current_password = '';
        form.password = '';
        form.password_confirmation = '';
        [currentField, passwordField, confirmField].forEach((fieldRef) => fieldRef.value?.resetField?.());
        recentlySuccessful.value = true;
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <form @submit.prevent="updatePassword" class="space-y-4">
            <Field ref="currentField" v-model="form.current_password" :field="{ label: 'Mật khẩu hiện tại', type: 'Password', validate: 'required' }" />
            <Field ref="passwordField" v-model="form.password" :field="{ label: 'Mật khẩu mới', type: 'Password', validate: 'required|min:8' }" />
            <Field ref="confirmField" v-model="form.password_confirmation" :field="{ label: 'Xác nhận mật khẩu', type: 'Password', validate: 'required' }" />

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="processing">Lưu</Button>
                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="recentlySuccessful" class="text-sm text-muted-foreground">Đã lưu.</p>
                </Transition>
            </div>
    </form>
</template>
