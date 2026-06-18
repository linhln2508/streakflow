<script setup>
defineProps({
    stats: { type: Object, required: true },
    hpPredictSign: { type: String, default: '' },
});

const summaryItems = [
    { key: 'total', label: 'Task', icon: 'CheckCircle2', bg: 'bg-amber-50', text: 'text-amber-600' },
    { key: 'remaining_skips', label: 'Skip free', icon: 'SkipForward', bg: 'bg-sky-50', text: 'text-sky-600' },
    { key: 'predicted_hp_change', label: 'HP dự kiến', icon: 'Heart', bg: 'bg-rose-50', text: 'text-rose-600', prefix: true },
];
</script>

<template>
    <div class="rounded-2xl border border-primary/15 bg-gradient-to-r from-primary/[0.06] via-amber-50/50 to-background p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div
                v-for="item in summaryItems"
                :key="item.key"
                class="flex items-center gap-3 rounded-xl border border-white/60 bg-white/70 p-3 shadow-sm backdrop-blur-sm"
            >
                <span :class="['flex h-9 w-9 shrink-0 items-center justify-center rounded-lg', item.bg]">
                    <DynamicIcon :name="item.icon" size="16" :class="item.text" />
                </span>
                <div>
                    <p class="text-xs text-muted-foreground">{{ item.label }}</p>
                    <p class="text-lg font-bold tabular-nums">
                        <template v-if="item.prefix">{{ hpPredictSign }}</template>{{ stats[item.key] }}
                    </p>
                </div>
            </div>
        </div>
        <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 rounded-xl bg-white/60 px-4 py-2.5 text-sm">
            <span class="font-medium text-muted-foreground">Tiến độ:</span>
            <span class="inline-flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-emerald-500" />
                Done <strong class="tabular-nums">{{ stats.done }}</strong>
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-amber-500" />
                Skip <strong class="tabular-nums">{{ stats.skipped }}</strong>
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-slate-400" />
                Pending <strong class="tabular-nums">{{ stats.pending }}</strong>
            </span>
        </div>
    </div>
</template>
