<script setup>
defineProps({
    instance: { type: Object, required: true },
    isDayClosed: { type: Boolean, default: false },
    priorityVariant: { type: Object, default: () => ({}) },
});

defineEmits(['done', 'skip', 'undo']);

const statusStyles = {
    done: 'border-emerald-200/80 bg-emerald-50/30',
    skipped: 'border-border/60 bg-muted/20 opacity-75',
    skipped_auto: 'border-border/60 bg-muted/20 opacity-75',
    pending: 'border-border/60 bg-card hover:border-primary/30 hover:shadow-md',
};

const statusIcon = {
    done: { name: 'CheckCircle2', class: 'text-emerald-500' },
    skipped: { name: 'SkipForward', class: 'text-amber-500' },
    skipped_auto: { name: 'SkipForward', class: 'text-amber-500' },
    pending: { name: 'CalendarCheck', class: 'text-muted-foreground' },
};
</script>

<template>
    <div
        :class="[
            'group flex items-center gap-4 rounded-2xl border p-4 transition-all duration-200',
            statusStyles[instance.status] ?? statusStyles.pending,
        ]"
    >
        <div
            :class="[
                'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl',
                instance.status === 'done' ? 'bg-emerald-100' : 'bg-muted/80',
            ]"
        >
            <DynamicIcon
                :name="statusIcon[instance.status]?.name ?? 'CalendarCheck'"
                size="18"
                :class="statusIcon[instance.status]?.class"
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
                    :class="{ 'line-through text-muted-foreground': instance.status === 'skipped' || instance.status === 'skipped_auto' }"
                >
                    {{ instance.template?.title }}
                </span>
                <Badge :variant="priorityVariant[instance.template?.priority] ?? 'secondary'" class="text-[10px] uppercase">
                    {{ instance.template?.priority }}
                </Badge>
            </div>
            <p class="mt-0.5 text-xs capitalize text-muted-foreground">{{ instance.status.replace('_', ' ') }}</p>
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
