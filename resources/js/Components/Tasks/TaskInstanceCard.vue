<script setup>
import { computed } from 'vue';

const props = defineProps({
    instance: { type: Object, required: true },
    isDayClosed: { type: Boolean, default: false },
    priorityVariant: { type: Object, default: () => ({}) },
});

defineEmits(['done', 'skip', 'undo']);

const isOverdue = computed(() => Boolean(props.instance.is_overdue));

const dueTimeLabel = computed(() => {
    const dueTime = props.instance.template?.due_time;

    if (!dueTime) {
        return null;
    }

    return String(dueTime).substring(0, 5);
});

const statusStyles = computed(() => {
    if (isOverdue.value) {
        return 'border-rose-300 bg-rose-50/80 shadow-sm shadow-rose-100';
    }

    return {
        done: 'border-emerald-200/80 bg-emerald-50/30',
        skipped: 'border-border/60 bg-muted/20 opacity-75',
        skipped_auto: 'border-border/60 bg-muted/20 opacity-75',
        pending: 'border-border/60 bg-card hover:border-primary/30 hover:shadow-md',
    }[props.instance.status] ?? 'border-border/60 bg-card hover:border-primary/30 hover:shadow-md';
});

const statusIcon = computed(() => {
    if (isOverdue.value) {
        return { name: 'AlertCircle', class: 'text-rose-600' };
    }

    return {
        done: { name: 'CheckCircle2', class: 'text-emerald-500' },
        skipped: { name: 'SkipForward', class: 'text-amber-500' },
        skipped_auto: { name: 'SkipForward', class: 'text-amber-500' },
        pending: { name: 'CalendarCheck', class: 'text-muted-foreground' },
    }[props.instance.status] ?? { name: 'CalendarCheck', class: 'text-muted-foreground' };
});

const statusLabel = computed(() => {
    if (isOverdue.value) {
        return 'Quá hạn';
    }

    return props.instance.status.replace('_', ' ');
});
</script>

<template>
    <div
        :class="[
            'group flex items-center gap-4 rounded-2xl border p-4 transition-all duration-200',
            statusStyles,
        ]"
    >
        <div
            :class="[
                'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl',
                isOverdue ? 'bg-rose-100' : instance.status === 'done' ? 'bg-emerald-100' : 'bg-muted/80',
            ]"
        >
            <DynamicIcon
                :name="statusIcon.name"
                size="18"
                :class="statusIcon.class"
            />
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span
                    v-if="instance.template?.category"
                    class="h-2.5 w-2.5 rounded-full ring-2 ring-white"
                    :style="{ backgroundColor: instance.template.category.color }"
                />
                <span
                    class="font-medium"
                    :class="{
                        'line-through text-muted-foreground': instance.status === 'skipped' || instance.status === 'skipped_auto',
                        'text-rose-700': isOverdue,
                    }"
                >
                    {{ instance.template?.title }}
                </span>
                <Badge v-if="isOverdue" variant="destructive" class="text-[10px] uppercase">
                    Quá hạn
                </Badge>
                <Badge :variant="priorityVariant[instance.template?.priority] ?? 'secondary'" class="text-[10px] uppercase">
                    {{ instance.template?.priority }}
                </Badge>
            </div>
            <p class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                <span class="capitalize" :class="{ 'font-medium text-rose-600': isOverdue }">{{ statusLabel }}</span>
                <span v-if="dueTimeLabel" class="inline-flex items-center gap-1">
                    <DynamicIcon name="Clock" size="12" />
                    Hạn {{ dueTimeLabel }}
                </span>
            </p>
        </div>

        <div v-if="!isDayClosed" class="flex shrink-0 gap-2">
            <template v-if="instance.status === 'pending'">
                <Button size="sm" class="rounded-full bg-emerald-600 px-4 shadow-sm hover:bg-emerald-700" @click="$emit('done', instance.id)">
                    <DynamicIcon name="Check" size="14" />
                    Done
                </Button>
                <Button size="sm" variant="outline" class="rounded-full" @click="$emit('skip', instance.id)">
                    <DynamicIcon name="SkipForward" size="14" />
                    Skip
                </Button>
            </template>
            <Button v-else size="sm" variant="ghost" class="rounded-full" @click="$emit('undo', instance.id)">
                <DynamicIcon name="Undo2" size="14" />
                Undo
            </Button>
        </div>
    </div>
</template>
