<script setup>
import { computed, ref } from "vue";

const props = defineProps({
  instances: { type: Array, default: () => [] },
  isDayClosed: { type: Boolean, default: false },
});

const emit = defineEmits(["done", "skip"]);

const open = ref(false);

const visible = computed(
  () => !props.isDayClosed && props.instances.length > 0
);

const overdueCount = computed(
  () => props.instances.filter((i) => i.is_overdue).length
);

const badgeCount = computed(() => {
  const n = props.instances.length;

  return n > 99 ? "99+" : String(n);
});

const taskTitle = (instance) => instance.template?.title ?? "";

const taskTooltip = (instance) => {
  const parts = [taskTitle(instance)];

  if (instance.is_overdue) {
    parts.push("Quá hạn");
  }

  const dueTime = instance.template?.due_time;

  if (dueTime) {
    parts.push(`Hạn ${String(dueTime).substring(0, 5)}`);
  }

  return parts.filter(Boolean).join(" · ");
};

const accentStyle = (instance) => {
  if (instance.is_overdue) {
    return undefined;
  }

  const color = instance.template?.category?.color;

  return color ? { backgroundColor: color } : undefined;
};

const toggle = () => {
  open.value = !open.value;
};
</script>

<template>
  <Teleport to="body">
    <div
      v-if="visible"
      class="pointer-events-none fixed bottom-[max(0.75rem,env(safe-area-inset-bottom))] right-3 z-40 flex flex-col items-end gap-1.5 sm:bottom-5 sm:right-4"
    >
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 translate-y-2 scale-[0.98]"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2 scale-[0.98]"
      >
        <div
          v-if="open"
          class="pointer-events-auto flex max-h-[min(55vh,16rem)] w-[min(calc(100vw-1.5rem),15.5rem)] flex-col overflow-hidden rounded-xl border border-border bg-card shadow-xl shadow-black/10 sm:w-60"
        >
          <div
            class="flex shrink-0 items-center gap-1.5 border-b border-border bg-muted/40 px-2.5 py-2"
          >
            <p class="flex-1 min-w-0 text-xs font-semibold text-foreground">
              Cần làm
              <span class="font-normal text-muted-foreground"
                >({{ instances.length }})</span
              >
            </p>
            <span
              v-if="overdueCount"
              class="shrink-0 rounded-full bg-rose-100 px-1.5 py-0.5 text-[10px] font-medium text-rose-700"
            >
              {{ overdueCount }} trễ
            </span>
            <Button
              variant="ghost"
              size="icon-xs"
              aria-label="Thu gọn"
              @click="open = false"
            >
              <DynamicIcon name="ChevronDown" size="12" />
            </Button>
          </div>

          <ul
            class="min-h-0 flex-1 space-y-1 overflow-y-auto bg-muted/20 p-1.5"
          >
            <li
              v-for="instance in instances"
              :key="instance.id"
              class="flex items-stretch gap-1.5 overflow-hidden rounded-md border bg-background shadow-sm"
              :class="
                instance.is_overdue ? 'border-rose-200' : 'border-border/80'
              "
            >
              <span
                class="w-1 shrink-0"
                :class="
                  instance.is_overdue ? 'bg-rose-500' : 'bg-muted-foreground/20'
                "
                :style="accentStyle(instance)"
              />
              <div class="flex min-w-0 flex-1 items-center gap-1.5 py-1.5 pr-1">
                <div class="flex-1 min-w-0">
                  <Tooltip :content="taskTooltip(instance)">
                    <p
                      class="line-clamp-2 text-[11px] font-medium leading-[1.3] text-foreground"
                      :class="instance.is_overdue ? 'text-rose-900' : ''"
                    >
                      {{ taskTitle(instance) }}
                    </p>
                  </Tooltip>
                </div>
                <div class="flex shrink-0 items-center gap-0.5">
                  <Button
                    variant="success"
                    size="icon-xs"
                    aria-label="Done"
                    @click="emit('done', instance.id)"
                  >
                    <DynamicIcon name="Check" size="12" />
                  </Button>
                  <Button
                    variant="outline"
                    size="icon-xs"
                    class="bg-transparent border-transparent hover:bg-background"
                    aria-label="Skip"
                    @click="emit('skip', instance.id)"
                  >
                    <DynamicIcon name="SkipForward" size="12" />
                  </Button>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </Transition>

      <Button
        variant="emphasis"
        size="fab-sm"
        class="relative pointer-events-auto"
        :aria-label="open ? 'Thu gọn danh sách task' : 'Mở danh sách task'"
        :aria-expanded="open"
        @click="toggle"
      >
        <DynamicIcon :name="open ? 'ChevronDown' : 'CalendarCheck'" size="18" />
        <span
          v-if="!open"
          class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full px-0.5 text-[9px] font-bold leading-none text-white ring-2 ring-card"
          :class="overdueCount ? 'bg-rose-500' : 'bg-emerald-600'"
        >
          {{ badgeCount }}
        </span>
      </Button>
    </div>
  </Teleport>
</template>
