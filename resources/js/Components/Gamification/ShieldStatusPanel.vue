<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    shieldCount: { type: Number, default: 0 },
    debtCount: { type: Number, default: 0 },
    streakCount: { type: Number, default: 0 },
});
</script>

<template>
    <div class="rounded-2xl border border-sky-200 bg-gradient-to-br from-sky-50/90 to-white p-4 sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 text-sm font-semibold text-sky-900">
                    <DynamicIcon name="Shield" size="18" />
                    Shield &amp; ứng trước
                </div>
                <p class="mt-2 text-sm text-sky-900/80">
                    Dùng khi <strong>chốt ngày &lt;75%</strong> để giữ streak.
                </p>
            </div>
            <Link :href="route('guide') + '#shield'" class="shrink-0 text-xs font-medium text-sky-700 underline underline-offset-2">
                Chi tiết
            </Link>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-sky-100 bg-white/80 px-3 py-2.5">
                <p class="text-[11px] uppercase tracking-wide text-muted-foreground">Shield</p>
                <p class="text-xl font-bold text-sky-700">{{ shieldCount }}</p>
                <p class="text-[11px] text-muted-foreground">Nhận khi 100% ngày</p>
            </div>
            <div class="rounded-xl border border-amber-100 bg-white/80 px-3 py-2.5">
                <p class="text-[11px] uppercase tracking-wide text-muted-foreground">Nợ shield</p>
                <p class="text-xl font-bold" :class="debtCount > 0 ? 'text-amber-700' : 'text-foreground'">{{ debtCount }}</p>
                <p class="text-[11px] text-muted-foreground">Ứng trước (streak ≥30)</p>
            </div>
            <div class="col-span-2 rounded-xl border border-orange-100 bg-white/80 px-3 py-2.5 sm:col-span-1">
                <p class="text-[11px] uppercase tracking-wide text-muted-foreground">Streak</p>
                <p class="text-xl font-bold text-orange-600">{{ streakCount }}</p>
                <p class="text-[11px] text-muted-foreground">Cần ≥75% để +1</p>
            </div>
        </div>

        <ul class="mt-4 space-y-1.5 text-xs text-sky-900/75">
            <li>• <strong>Shield:</strong> trừ 1 shield, giữ streak.</li>
            <li>• <strong>Ứng trước:</strong> giữ streak lần này, ghi 1 nợ (chỉ khi streak ≥30 và chưa nợ).</li>
            <li>• Lần chốt tiếp theo &lt;75% mà có nợ → mất streak.</li>
        </ul>
    </div>
</template>
