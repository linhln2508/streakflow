<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useApi, unwrapApiData } from '@/composables/useApi';

const showingNavigationDropdown = ref(false);
const user = usePage().props.auth.user;
const isAdmin = user?.role === 'admin';

const logout = async () => {
    const response = await useApi(route('web_api.auth.logout')).post();
    router.visit(unwrapApiData(response)?.redirect ?? '/');
};
</script>

<template>
    <div class="min-h-screen bg-background">
        <nav class="border-b bg-card">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex shrink-0 items-center">
                            <Link :href="route('dashboard')" class="flex items-center gap-2">
                                <ApplicationLogo class="block h-8 w-auto fill-current text-primary" />
                                <span class="text-lg font-bold text-primary">StreakFlow</span>
                            </Link>
                        </div>
                        <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">Hôm nay</NavLink>
                            <NavLink :href="route('tasks.index')" :active="route().current('tasks.*')">Tasks</NavLink>
                            <NavLink :href="route('categories.index')" :active="route().current('categories.*')">Danh mục</NavLink>
                            <NavLink :href="route('reports.overview')" :active="route().current('reports.*')">Báo cáo</NavLink>
                            <NavLink v-if="isAdmin" :href="route('admin.users')" :active="route().current('admin.*')">Admin</NavLink>
                        </div>
                    </div>
                    <div class="hidden sm:ms-6 sm:flex sm:items-center gap-4">
                        <div class="flex items-center gap-2 text-sm">
                            <Badge variant="secondary" class="gap-1">
                                <DynamicIcon name="Heart" size="12" class="text-red-500" /> {{ user.hp }}
                            </Badge>
                            <Badge variant="secondary" class="gap-1">
                                <DynamicIcon name="Zap" size="12" class="text-amber-500" /> Lv.{{ user.level }}
                            </Badge>
                            <Badge variant="secondary" class="gap-1">
                                <DynamicIcon name="Flame" size="12" class="text-orange-500" /> {{ user.streak_count }}
                            </Badge>
                            <Badge variant="secondary" class="gap-1">
                                <DynamicIcon name="Shield" size="12" class="text-blue-500" /> {{ user.shield_count }}
                            </Badge>
                        </div>
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button type="button" class="inline-flex items-center rounded-md border border-input bg-background px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground">
                                    {{ user.name }}
                                    <DynamicIcon name="ChevronDown" size="14" class="ml-2" />
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Hồ sơ</DropdownLink>
                                <button
                                    type="button"
                                    class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                                    @click="logout"
                                >
                                    Đăng xuất
                                </button>
                            </template>
                        </Dropdown>
                    </div>
                    <div class="-me-2 flex items-center sm:hidden">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center rounded-md p-2 text-muted-foreground hover:bg-muted">
                            <DynamicIcon :name="showingNavigationDropdown ? 'X' : 'Menu'" size="20" />
                        </button>
                    </div>
                </div>
            </div>
            <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
                <div class="space-y-1 pb-3 pt-2">
                    <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">Hôm nay</ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('tasks.index')" :active="route().current('tasks.*')">Tasks</ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('categories.index')" :active="route().current('categories.*')">Danh mục</ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('reports.overview')" :active="route().current('reports.*')">Báo cáo</ResponsiveNavLink>
                    <ResponsiveNavLink v-if="isAdmin" :href="route('admin.users')" :active="route().current('admin.*')">Admin</ResponsiveNavLink>
                </div>
            </div>
        </nav>
        <header v-if="$slots.header" class="border-b bg-card">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>
        <main>
            <slot />
        </main>
    </div>
</template>
