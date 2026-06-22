<script setup>
import { computed } from 'vue';
import {
    AlertCircle,
    BookOpen,
    Briefcase,
    CalendarCheck,
    Check,
    CheckCircle2,
    ChevronDown,
    ChevronRight,
    ChevronUp,
    Clock,
    Crown,
    Dumbbell,
    Flame,
    Heart,
    LogIn,
    Menu,
    Pencil,
    Plus,
    Save,
    Shield,
    SkipForward,
    Sparkles,
    Star,
    Target,
    ToggleLeft,
    ToggleRight,
    Trophy,
    Trash2,
    Undo2,
    User,
    UserPlus,
    X,
    Zap,
} from '@lucide/vue';

const iconMap = {
    AlertCircle,
    BookOpen,
    Briefcase,
    CalendarCheck,
    Check,
    CheckCircle2,
    ChevronDown,
    ChevronRight,
    ChevronUp,
    Clock,
    Crown,
    Dumbbell,
    Flame,
    Heart,
    LogIn,
    Menu,
    Pencil,
    Plus,
    Save,
    Shield,
    SkipForward,
    Sparkles,
    Star,
    Target,
    ToggleLeft,
    ToggleRight,
    Trophy,
    Trash2,
    Undo2,
    User,
    UserPlus,
    X,
    Zap,
};

const props = defineProps({
    name: { type: String, required: true },
    size: { type: [String, Number], default: 16 },
    color: { type: String, default: undefined },
    strokeWidth: { type: [Number, String], default: 2 },
    class: { type: null, default: undefined },
});

const icon = computed(() => {
    const key = props.name?.trim();
    if (!key) {
        return null;
    }

    return iconMap[key] ?? iconMap[key.charAt(0).toUpperCase() + key.slice(1)];
});
</script>

<template>
    <component
        :is="icon"
        v-if="icon"
        :size="size"
        :color="color"
        :stroke-width="strokeWidth"
        :class="class"
    />
</template>
