<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { useApi, unwrapApiData } from '@/composables/useApi';
import { useFormFields } from '@/composables/useFormFields';

const props = defineProps({ profile: Object, summaries: Array });

const processing = ref(false);
const hpProcessing = ref(false);
const passwordProcessing = ref(false);
const displayHp = ref(props.profile.hp);
const { validateAll } = useFormFields();

const amountField = ref(null);
const noteField = ref(null);
const passwordField = ref(null);
const passwordConfirmField = ref(null);

const hpForm = reactive({
    amount: '',
    note: '',
});

const passwordForm = reactive({
    password: '',
    password_confirmation: '',
});

const approveUser = async () => {
    processing.value = true;
    try {
        await useApi(route('web_api.admin.users.approve', props.profile.id)).patch();
        router.reload();
    } finally {
        processing.value = false;
    }
};

const rejectUser = async () => {
    if (!confirm('Từ chối và xóa tài khoản này?')) {
        return;
    }

    processing.value = true;
    try {
        await useApi(route('web_api.admin.users.reject', props.profile.id)).delete();
        router.visit(route('admin.users'));
    } finally {
        processing.value = false;
    }
};

const adjustHp = async () => {
    if (!validateAll([amountField.value]).isValid) {
        return;
    }

    const amount = Number(hpForm.amount);

    if (!amount || Number.isNaN(amount)) {
        return;
    }

    hpProcessing.value = true;

    try {
        const response = await useApi(route('web_api.admin.users.adjust_hp', props.profile.id)).patch({
            amount,
            note: hpForm.note || null,
        });
        const data = unwrapApiData(response);
        displayHp.value = data?.hp_after ?? displayHp.value;
        amountField.value?.resetField();
        noteField.value?.resetField();
    } finally {
        hpProcessing.value = false;
    }
};

const resetPassword = async () => {
    if (!validateAll([passwordField.value, passwordConfirmField.value]).isValid) {
        return;
    }

    passwordProcessing.value = true;

    try {
        await useApi(route('web_api.admin.users.reset_password', props.profile.id)).put({
            password: passwordForm.password,
            password_confirmation: passwordForm.password_confirmation,
        });
        passwordField.value?.resetField();
        passwordConfirmField.value?.resetField();
    } finally {
        passwordProcessing.value = false;
    }
};
</script>

<template>
    <Head :title="profile.name" />

    <PageHeader
        :title="profile.name"
        :description="profile.email"
        :breadcrumbs="[
            { label: 'Admin', href: route('admin.users') },
            { label: 'Users', href: route('admin.users') },
            { label: profile.name },
        ]"
    >
        <template v-if="!profile.is_approved && profile.role !== 'admin'" #actions>
            <Button :disabled="processing" @click="approveUser">Duyệt tài khoản</Button>
            <Button variant="outline" :disabled="processing" @click="rejectUser">Từ chối</Button>
        </template>
    </PageHeader>

    <PageContainer class="space-y-6">
        <div class="flex flex-wrap items-center gap-2">
            <Badge :variant="profile.is_approved ? 'default' : 'outline'">
                {{ profile.is_approved ? 'Đã duyệt' : 'Chờ duyệt' }}
            </Badge>
            <Badge :variant="profile.role === 'admin' ? 'default' : 'secondary'">{{ profile.role }}</Badge>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <StatCard icon="Zap" label="Level" :value="`Lv.${profile.level}`" variant="warning" />
            <StatCard icon="Heart" label="HP" :value="displayHp" variant="danger" />
            <StatCard icon="Flame" label="Streak" :value="profile.streak_count" variant="warning" />
            <StatCard icon="Zap" label="XP" :value="profile.xp" variant="default" />
        </div>

        <PageSection title="Điều chỉnh HP">
            <p class="mb-4 text-sm text-muted-foreground">
                Nhập số dương để nạp HP, số âm để trừ. HP hiện tại: <strong>{{ displayHp }}</strong> (tối đa 100).
            </p>
            <form class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_minmax(0,2fr)_auto]" @submit.prevent="adjustHp">
                <Field
                    ref="amountField"
                    v-model="hpForm.amount"
                    :field="{ label: 'Số điểm (+/-)', type: 'Number', validate: 'required|integer' }"
                />
                <Field
                    ref="noteField"
                    v-model="hpForm.note"
                    :field="{ label: 'Ghi chú (tuỳ chọn)', type: 'Text' }"
                />
                <div class="flex items-end">
                    <Button type="submit" :disabled="hpProcessing" class="w-full sm:w-auto">
                        {{ hpProcessing ? 'Đang cập nhật...' : 'Cập nhật HP' }}
                    </Button>
                </div>
            </form>
        </PageSection>

        <PageSection v-if="profile.role !== 'admin'" title="Đặt mật khẩu mới">
            <p class="mb-4 text-sm text-muted-foreground">
                Admin có thể đặt mật khẩu mới cho tài khoản này. Người dùng sẽ đăng nhập bằng mật khẩu mới.
            </p>
            <form class="grid max-w-xl gap-4" @submit.prevent="resetPassword">
                <Field
                    ref="passwordField"
                    v-model="passwordForm.password"
                    :field="{ label: 'Mật khẩu mới', type: 'Password', validate: 'required|min:8' }"
                />
                <Field
                    ref="passwordConfirmField"
                    v-model="passwordForm.password_confirmation"
                    :field="{ label: 'Xác nhận mật khẩu', type: 'Password', validate: 'required' }"
                />
                <div>
                    <Button type="submit" :disabled="passwordProcessing">
                        {{ passwordProcessing ? 'Đang cập nhật...' : 'Cập nhật mật khẩu' }}
                    </Button>
                </div>
            </form>
        </PageSection>

        <PageSection title="30 ngày gần nhất">
            <div v-for="s in summaries" :key="s.id" class="flex justify-between border-b py-2 text-sm last:border-0">
                <span>{{ s.date }}</span>
                <span class="text-muted-foreground">{{ s.pct_completed }}% · +{{ s.xp_earned }} XP</span>
            </div>
            <EmptyState v-if="summaries.length === 0" icon="CalendarCheck" title="Chưa có dữ liệu" />
        </PageSection>

        <Link :href="route('admin.users')" class="text-sm text-primary hover:underline">← Quay lại danh sách</Link>
    </PageContainer>
</template>
