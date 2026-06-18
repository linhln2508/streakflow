<script setup>
import { cn } from '@/lib/utils';

defineProps({
    user: { type: Object, required: true },
    class: { type: null, required: false },
});

const stats = [
    { key: 'hp', icon: 'Heart', label: 'HP', bg: 'bg-rose-50', text: 'text-rose-600', get: (u) => u.hp },
    { key: 'level', icon: 'Zap', label: 'Lv', bg: 'bg-amber-50', text: 'text-amber-600', get: (u) => u.level },
    { key: 'streak', icon: 'Flame', label: '', bg: 'bg-orange-50', text: 'text-orange-600', get: (u) => u.streak_count },
    { key: 'shield', icon: 'Shield', label: '', bg: 'bg-sky-50', text: 'text-sky-600', get: (u) => u.shield_count },
];
</script>

<template>
    <div :class="cn('hidden items-center gap-1 rounded-full border border-border/60 bg-background/80 p-1 shadow-sm lg:flex', $props.class)">
        <div
            v-for="s in stats"
            :key="s.key"
            :class="['flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold', s.bg, s.text]"
        >
            <DynamicIcon :name="s.icon" size="13" />
            <span v-if="s.label" class="opacity-70">{{ s.label }}</span>
            {{ s.get(user) }}
        </div>
    </div>
</template>
