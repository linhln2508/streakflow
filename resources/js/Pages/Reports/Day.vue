<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ date: String, summary: Object, instances: Array });

const statusVariant = {
    done: 'default',
    skipped: 'secondary',
    skipped_auto: 'destructive',
    pending: 'outline',
};
</script>

<template>
    <Head :title="`Báo cáo ${date}`" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold">Báo cáo ngày {{ date }}</h2>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-6 px-4">
                <div v-if="summary" class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <Card>
                        <CardContent class="pt-6 text-center">
                            <div class="text-xs text-muted-foreground">HP</div>
                            <div class="text-xl font-bold" :class="summary.hp_change >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ summary.hp_change >= 0 ? '+' : '' }}{{ summary.hp_change }}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="pt-6 text-center">
                            <div class="text-xs text-muted-foreground">XP</div>
                            <div class="text-xl font-bold text-amber-600">+{{ summary.xp_earned }}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="pt-6 text-center">
                            <div class="text-xs text-muted-foreground">Streak</div>
                            <div class="text-xl font-bold text-orange-600">{{ summary.streak_after }}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="pt-6 text-center">
                            <div class="text-xs text-muted-foreground">Hoàn thành</div>
                            <div class="text-xl font-bold text-blue-600">{{ summary.pct_completed }}%</div>
                        </CardContent>
                    </Card>
                </div>
                <Card v-else class="border-yellow-200 bg-yellow-50">
                    <CardContent class="pt-6 text-center text-yellow-800">
                        Ngày này chưa được chốt.
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Chi tiết task</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-for="i in instances" :key="i.id" class="flex items-center justify-between border-b py-2 last:border-0">
                            <span>{{ i.template?.title }}</span>
                            <Badge :variant="statusVariant[i.status] ?? 'outline'" class="capitalize">{{ i.status }}</Badge>
                        </div>
                        <p v-if="instances.length === 0" class="text-center text-muted-foreground">Không có task.</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
