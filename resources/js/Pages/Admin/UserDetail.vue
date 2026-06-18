<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ profile: Object, summaries: Array });
</script>

<template>
    <Head :title="profile.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.users')" class="text-sm text-muted-foreground hover:text-foreground">← Users</Link>
                <h2 class="text-xl font-semibold">{{ profile.name }}</h2>
            </div>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-6 px-4">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <Card>
                        <CardContent class="pt-6 text-center">
                            <div class="text-2xl font-bold text-amber-600">Lv.{{ profile.level }}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="pt-6 text-center">
                            <div class="flex items-center justify-center gap-2 text-2xl font-bold text-red-600">
                                <DynamicIcon name="Heart" size="22" class="text-red-500" />
                                {{ profile.hp }}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="pt-6 text-center">
                            <div class="flex items-center justify-center gap-2 text-2xl font-bold text-orange-600">
                                <DynamicIcon name="Flame" size="22" />
                                {{ profile.streak_count }}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="pt-6 text-center">
                            <div class="text-2xl font-bold">{{ profile.xp }} XP</div>
                        </CardContent>
                    </Card>
                </div>
                <Card>
                    <CardHeader>
                        <CardTitle>30 ngày gần nhất</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-for="s in summaries" :key="s.id" class="flex justify-between border-b py-2 text-sm last:border-0">
                            <span>{{ s.date }}</span>
                            <span class="text-muted-foreground">{{ s.pct_completed }}% · +{{ s.xp_earned }} XP</span>
                        </div>
                        <p v-if="summaries.length === 0" class="text-center text-muted-foreground">Chưa có dữ liệu.</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
