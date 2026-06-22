<script setup>
import { computed } from 'vue';

const props = defineProps({
    preview: Object,
    modelValue: { type: String, default: null },
});

const emit = defineEmits(['update:modelValue']);

const selected = computed({
    get: () => props.modelValue ?? props.preview?.default_strategy ?? 'reset',
    set: (value) => emit('update:modelValue', value),
});

const options = computed(() => {
    if (!props.preview?.needs_streak_choice) {
        return [];
    }

    const map = {
        shield: {
            key: 'shield',
            title: 'Dùng Shield',
            description: 'Giữ nguyên streak, trừ 1 Shield.',
            icon: 'Shield',
            variant: 'info',
        },
        debt: {
            key: 'debt',
            title: 'Ứng trước (Shield nợ)',
            description: 'Giữ streak lần này nhưng ghi 1 nợ. Lần sau <75% mà không có Shield sẽ mất streak.',
            icon: 'Clock',
            variant: 'warning',
        },
        reset: {
            key: 'reset',
            title: 'Chấp nhận mất streak',
            description: 'Streak về 0. Không dùng Shield hay ứng trước.',
            icon: 'Flame',
            variant: 'danger',
        },
    };

    return Object.keys(props.preview.outcomes ?? {})
        .map((key) => map[key])
        .filter(Boolean);
});

const successOutcome = computed(() => props.preview?.outcomes?.success ?? null);
</script>

<template>
    <div class="space-y-4">
        <div class="rounded-xl border border-border/60 bg-muted/30 px-4 py-3 text-sm">
            <p>
                Hoàn thành dự kiến:
                <strong>{{ preview?.pct_completed ?? 0 }}%</strong>
                · HP:
                <strong :class="(preview?.hp_change ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                    {{ (preview?.hp_change ?? 0) >= 0 ? '+' : '' }}{{ preview?.hp_change ?? 0 }}
                </strong>
            </p>
            <p class="mt-1 text-xs text-muted-foreground">
                Task pending sẽ tự chuyển thành skip khi chốt ngày.
            </p>
        </div>

        <div
            v-if="preview?.needs_streak_choice"
            class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950"
        >
            <div class="flex items-start gap-2">
                <DynamicIcon name="AlertCircle" size="18" class="mt-0.5 shrink-0" />
                <div>
                    <p class="font-semibold">Streak đang bị ảnh hưởng</p>
                    <p class="mt-1 text-amber-900/90">
                        Dưới 75% hoàn thành — streak hiện tại
                        <strong>{{ preview.streak_before }}</strong>
                        sẽ mất nếu không dùng Shield hoặc ứng trước.
                    </p>
                    <p class="mt-1 text-xs text-amber-800">
                        Shield: {{ preview.shield_count }} · Nợ shield: {{ preview.debt_count }}
                    </p>
                </div>
            </div>
        </div>

        <div v-else-if="successOutcome" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            <DynamicIcon name="CheckCircle2" size="16" class="mr-1 inline" />
            Streak sẽ tăng lên <strong>{{ successOutcome.streak_after }}</strong>
            <span v-if="successOutcome.shield_earned"> · Nhận thêm 1 Shield (100%)</span>
            <span v-if="successOutcome.debt_cleared"> · Xóa nợ shield</span>
        </div>

        <div v-if="options.length" class="space-y-2">
            <p class="text-sm font-medium">Chọn cách xử lý streak</p>
            <label
                v-for="option in options"
                :key="option.key"
                class="flex cursor-pointer gap-3 rounded-xl border p-3 transition-colors"
                :class="selected === option.key
                    ? 'border-primary bg-primary/5 ring-1 ring-primary/30'
                    : 'border-border hover:border-primary/30'"
            >
                <input v-model="selected" type="radio" :value="option.key" class="mt-1" />
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 font-medium">
                        <DynamicIcon :name="option.icon" size="16" />
                        {{ option.title }}
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">{{ option.description }}</p>
                    <p v-if="preview.outcomes[option.key]" class="mt-2 text-xs font-medium text-foreground">
                        → Streak {{ preview.outcomes[option.key].streak_before }}
                        → {{ preview.outcomes[option.key].streak_after }}
                    </p>
                </div>
            </label>
        </div>
    </div>
</template>
