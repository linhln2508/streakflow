<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useApi } from '@/composables/useApi';

const props = defineProps({
    users: Object,
    filters: Object,
    pendingCount: { type: Number, default: 0 },
});

const search = ref(props.filters?.search ?? '');
const approval = ref(props.filters?.approval ?? '');
const processingId = ref(null);

const applyFilters = () => router.get(
    route('admin.users'),
    { search: search.value || undefined, approval: approval.value || undefined },
    { preserveState: true },
);

const setApprovalFilter = (value) => {
    approval.value = value;
    applyFilters();
};

const approveUser = async (userId) => {
    processingId.value = userId;
    try {
        await useApi(route('web_api.admin.users.approve', userId)).patch();
        router.reload({ only: ['users', 'pendingCount'] });
    } finally {
        processingId.value = null;
    }
};

const rejectUser = async (userId) => {
    if (!confirm('Từ chối và xóa tài khoản này?')) {
        return;
    }

    processingId.value = userId;
    try {
        await useApi(route('web_api.admin.users.reject', userId)).delete();
        router.reload({ only: ['users', 'pendingCount'] });
    } finally {
        processingId.value = null;
    }
};
</script>

<template>
    <Head title="Admin - Users" />

    <PageHeader size="wide" title="Quản lý Users" description="Duyệt tài khoản mới và quản lý người dùng">
        <template #actions>
            <Button as="a" variant="outline" :href="route('admin.analytics')">
                Analytics
            </Button>
        </template>
    </PageHeader>

    <PageContainer size="wide" class="space-y-4">
        <div class="flex flex-wrap gap-2">
            <Button
                size="sm"
                :variant="approval === '' ? 'default' : 'outline'"
                @click="setApprovalFilter('')"
            >
                Tất cả
            </Button>
            <Button
                size="sm"
                :variant="approval === 'pending' ? 'default' : 'outline'"
                @click="setApprovalFilter('pending')"
            >
                Chờ duyệt
                <Badge v-if="pendingCount > 0" variant="secondary" class="ml-1.5">{{ pendingCount }}</Badge>
            </Button>
            <Button
                size="sm"
                :variant="approval === 'approved' ? 'default' : 'outline'"
                @click="setApprovalFilter('approved')"
            >
                Đã duyệt
            </Button>
        </div>

        <form @submit.prevent="applyFilters" class="flex gap-2">
            <Input v-model="search" placeholder="Tìm kiếm..." class="flex-1" />
            <Button type="submit">Tìm</Button>
        </form>

        <PageSection>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/50 text-xs uppercase text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3">Tên</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Trạng thái</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Level</th>
                            <th class="px-4 py-3">Streak</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in users.data" :key="u.id" class="border-b last:border-0">
                            <td class="px-4 py-3 font-medium">{{ u.name }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ u.email }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="u.is_approved ? 'default' : 'outline'">
                                    {{ u.is_approved ? 'Đã duyệt' : 'Chờ duyệt' }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3">
                                <Badge :variant="u.role === 'admin' ? 'default' : 'secondary'">{{ u.role }}</Badge>
                            </td>
                            <td class="px-4 py-3">Lv.{{ u.level }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1">
                                    <DynamicIcon name="Flame" size="14" class="text-orange-500" />
                                    {{ u.streak_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <template v-if="!u.is_approved && u.role !== 'admin'">
                                        <Button
                                            size="sm"
                                            :disabled="processingId === u.id"
                                            @click="approveUser(u.id)"
                                        >
                                            Duyệt
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            :disabled="processingId === u.id"
                                            @click="rejectUser(u.id)"
                                        >
                                            Từ chối
                                        </Button>
                                    </template>
                                    <Link :href="route('admin.users.show', u.id)" class="text-primary hover:underline">Chi tiết</Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </PageSection>
    </PageContainer>
</template>
