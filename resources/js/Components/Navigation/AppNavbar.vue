<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppContainer from '@/Components/Layout/AppContainer.vue';
import { mainNavItems } from '@/constants/navigation';
import { useApi, unwrapApiData } from '@/composables/useApi';
import UserStatsBar from '@/Components/Navigation/UserStatsBar.vue';
import { APP_NAME_SHORT } from '@/constants/brand';

const showingMobileMenu = ref(false);
const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.role === 'admin');
const navItems = computed(() => mainNavItems(isAdmin.value, page.url, page.props.unclosedDaysCount ?? 0));

const logout = async () => {
    const response = await useApi(route('web_api.auth.logout')).post();
    router.visit(unwrapApiData(response)?.redirect ?? '/');
};
</script>

<template>
    <nav class="glass-nav sticky top-0 z-40">
        <AppContainer>
            <div class="flex h-[3.75rem] items-center justify-between">
                <div class="flex min-w-0 items-center gap-6">
                    <Link :href="route('dashboard')" class="group flex shrink-0 items-center gap-2.5">
                        <ApplicationLogo class="h-9 w-9 rounded-xl shadow-md shadow-primary/20" />
                        <span class="hidden text-base font-bold text-foreground sm:inline">
                            {{ APP_NAME_SHORT }}
                        </span>
                    </Link>
                    <div class="hidden md:flex md:items-center md:gap-0.5">
                        <Link
                            v-for="item in navItems"
                            :key="item.label"
                            :href="item.href"
                            class="inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-sm font-medium transition-all"
                            :class="item.active
                                ? 'bg-primary text-primary-foreground shadow-md shadow-primary/20'
                                : 'text-muted-foreground hover:bg-muted/80 hover:text-foreground'"
                        >
                            <DynamicIcon :name="item.icon" size="15" />
                            {{ item.label }}
                            <Badge
                                v-if="item.badge"
                                variant="destructive"
                                class="h-5 min-w-5 justify-center rounded-full px-1.5 text-[10px]"
                            >
                                {{ item.badge }}
                            </Badge>
                        </Link>
                    </div>
                </div>

                <div class="hidden items-center gap-3 md:flex">
                    <UserStatsBar :user="user" />
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <Button variant="outline" size="pill-sm" class="gap-2 pl-2 pr-3">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">
                                    {{ user.name?.charAt(0)?.toUpperCase() }}
                                </span>
                                {{ user.name }}
                                <DynamicIcon name="ChevronDown" size="14" class="text-muted-foreground" />
                            </Button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('guide')">Hướng dẫn & Luật chơi</DropdownLink>
                            <DropdownLink :href="route('profile.edit')">Hồ sơ</DropdownLink>
                            <button
                                type="button"
                                class="block w-full px-4 py-2 text-start text-sm transition-colors hover:bg-muted"
                                @click="logout"
                            >
                                Đăng xuất
                            </button>
                        </template>
                    </Dropdown>
                </div>

                <Button variant="ghost" size="icon-pill" class="md:hidden" @click="showingMobileMenu = !showingMobileMenu">
                    <DynamicIcon :name="showingMobileMenu ? 'X' : 'Menu'" size="20" />
                </Button>
            </div>
        </AppContainer>

        <div v-show="showingMobileMenu" class="border-t border-border/60 bg-card/95 backdrop-blur-xl md:hidden">
            <div class="space-y-0.5 px-3 py-3">
                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium"
                    :class="item.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground'"
                    @click="showingMobileMenu = false"
                >
                    <DynamicIcon :name="item.icon" size="16" />
                    {{ item.label }}
                    <Badge
                        v-if="item.badge"
                        variant="destructive"
                        class="ml-auto h-5 min-w-5 justify-center rounded-full px-1.5 text-[10px]"
                    >
                        {{ item.badge }}
                    </Badge>
                </Link>
            </div>
            <div class="border-t border-border/60 px-4 py-4">
                <UserStatsBar :user="user" class="mb-3 !flex lg:!hidden" />
                <p class="text-sm font-semibold">{{ user.name }}</p>
                <div class="mt-2 flex gap-4">
                    <Link :href="route('profile.edit')" class="text-sm font-medium text-primary">Hồ sơ</Link>
                    <Link :href="route('guide')" class="text-sm font-medium text-primary">Luật chơi</Link>
                    <button type="button" class="text-sm text-muted-foreground" @click="logout">Đăng xuất</button>
                </div>
            </div>
        </div>
    </nav>
</template>
