<script setup>
import { ref } from 'vue';

const props = defineProps({
    content: { type: String, default: '' },
});

const visible = ref(false);
const position = ref({ top: 0, left: 0 });

let hideTimer = null;

const show = (event) => {
    if (!props.content) {
        return;
    }

    clearTimeout(hideTimer);

    const rect = event.currentTarget.getBoundingClientRect();

    position.value = {
        top: rect.top - 6,
        left: rect.left + rect.width / 2,
    };
    visible.value = true;
};

const hide = () => {
    hideTimer = setTimeout(() => {
        visible.value = false;
    }, 60);
};
</script>

<template>
    <span
        class="block min-w-0"
        @mouseenter="show"
        @mouseleave="hide"
    >
        <slot />
    </span>
    <Teleport to="body">
        <div
            v-if="visible"
            role="tooltip"
            class="pointer-events-none fixed z-[60] max-w-[14rem] -translate-x-1/2 -translate-y-full rounded-md bg-foreground px-2 py-1 text-[11px] leading-snug text-background shadow-md"
            :style="{ top: `${position.top}px`, left: `${position.left}px` }"
        >
            {{ content }}
        </div>
    </Teleport>
</template>
