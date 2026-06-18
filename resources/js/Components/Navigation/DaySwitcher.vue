<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    days: Array,
    selectedDate: String,
    today: String,
});
</script>

<template>
    <div v-if="days.length > 1 || (days.length === 1 && days[0].date !== today)" class="space-y-2">
        <div class="flex items-center justify-between gap-2">
            <p class="text-sm font-medium text-foreground">Chọn ngày cần chốt</p>
            <span v-if="days.length > 1" class="text-xs text-muted-foreground">Nên chốt từ cũ → mới</span>
        </div>
        <div class="flex gap-2 overflow-x-auto pb-1">
            <Link
                v-for="day in days"
                :key="day.date"
                :href="route('dashboard', { date: day.date })"
                preserve-scroll
                class="inline-flex shrink-0 items-center gap-2 rounded-full border px-3.5 py-2 text-sm font-medium transition-all"
                :class="selectedDate === day.date
                    ? 'border-primary bg-primary text-primary-foreground shadow-md shadow-primary/20'
                    : day.is_today
                        ? 'border-border bg-card text-foreground hover:border-primary/40'
                        : 'border-amber-200 bg-amber-50 text-amber-950 hover:border-amber-300'"
            >
                <span>{{ day.label }}</span>
                <Badge
                    v-if="day.pending > 0"
                    :variant="selectedDate === day.date ? 'secondary' : 'outline'"
                    class="h-5 min-w-5 justify-center rounded-full px-1.5 text-[10px]"
                >
                    {{ day.pending }}
                </Badge>
            </Link>
            <Link
                v-if="!days.some((day) => day.is_today) && selectedDate !== today"
                :href="route('dashboard', { date: today })"
                preserve-scroll
                class="inline-flex shrink-0 items-center gap-2 rounded-full border border-border bg-card px-3.5 py-2 text-sm font-medium text-foreground transition-all hover:border-primary/40"
            >
                Hôm nay
            </Link>
        </div>
    </div>
</template>
