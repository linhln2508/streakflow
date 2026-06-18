<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { APP_NAME, APP_NAME_SHORT } from '@/constants/brand';
import { authHighlights } from '@/constants/navigation';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="grid min-h-screen lg:grid-cols-[1fr_480px] xl:grid-cols-[1fr_520px]">
        <div class="auth-panel auth-grid relative hidden flex-col justify-between overflow-hidden p-12 text-white lg:flex">
            <div class="relative">
                <div class="flex items-center gap-3">
                    <ApplicationLogo class="h-12 w-12 rounded-2xl shadow-lg shadow-primary/30" />
                    <div>
                        <span class="block text-2xl font-bold tracking-tight">{{ APP_NAME_SHORT }}</span>
                        <span class="text-xs text-white/50">{{ APP_NAME }}</span>
                    </div>
                </div>
                <p class="mt-10 max-w-md text-[1.65rem] font-semibold leading-snug tracking-tight text-white">
                    Linh ta linh tinh —
                    <span class="text-primary">một nơi</span>
                    cho mọi thứ.
                </p>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/60">
                    Task, thói quen, gamification và nhiều tính năng khác — tất cả gom về một app của bạn.
                </p>
            </div>

            <ul class="relative space-y-3">
                <li
                    v-for="item in authHighlights"
                    :key="item.text"
                    class="flex items-center gap-3 rounded-xl border border-white/8 bg-white/5 px-4 py-3 text-sm text-white/85 backdrop-blur-sm"
                >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/20 text-primary">
                        <DynamicIcon :name="item.icon" size="17" />
                    </span>
                    {{ item.text }}
                </li>
            </ul>

            <p class="relative text-xs text-white/35">Laravel · Vue 3 · Inertia</p>
        </div>

        <div class="flex min-h-screen flex-col bg-background">
            <div class="flex flex-1 flex-col justify-center px-6 py-10 sm:px-10 lg:px-12">
                <Link :href="route('login')" class="mb-10 flex items-center gap-2.5 lg:hidden">
                    <ApplicationLogo class="h-10 w-10 rounded-xl shadow-md shadow-primary/25" />
                    <span class="text-xl font-bold text-foreground">{{ APP_NAME_SHORT }}</span>
                </Link>

                <div class="mx-auto w-full max-w-[400px]">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
