<template>
    <Teleport to="body">
        <Transition name="rpg-modal">
            <div
                v-if="open"
                class="rpg-modal-overlay"
                @click.self="close"
            >
                <div
                    class="rpg-modal"
                    :class="{ 'is-wide': wide, 'is-headerless': headerless }"
                    role="dialog"
                    aria-modal="true"
                    :aria-labelledby="headerless ? undefined : titleId"
                >
                    <header v-if="!headerless" class="rpg-modal-header">
                        <div class="rpg-modal-heading">
                            <i v-if="icon" :class="icon"></i>
                            <h2 v-if="!$slots.title" :id="titleId">{{ title }}</h2>
                            <div v-else :id="titleId" class="rpg-modal-title-slot">
                                <slot name="title" />
                            </div>
                        </div>
                        <div class="rpg-modal-toolbar">
                            <slot name="header-actions" />
                            <button
                                type="button"
                                class="rpg-modal-close"
                                aria-label="閉じる"
                                @click="close"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </header>

                    <button
                        v-else
                        type="button"
                        class="rpg-modal-close rpg-modal-close-floating"
                        aria-label="閉じる"
                        @click="close"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>

                    <div class="rpg-modal-body">
                        <slot />
                    </div>

                    <footer v-if="$slots.footer" class="rpg-modal-footer">
                        <slot name="footer" />
                    </footer>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, useId, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title?: string;
        icon?: string;
        headerless?: boolean;
        wide?: boolean;
    }>(),
    {
        title: '',
        icon: '',
        headerless: false,
        wide: false,
    },
);

const emit = defineEmits<{
    close: [];
}>();

const titleId = useId();

const close = (): void => {
    emit('close');
};

const onKeydown = (event: KeyboardEvent): void => {
    if (props.open && event.key === 'Escape') {
        close();
    }
};

const syncBodyScroll = (isOpen: boolean): void => {
    document.body.style.overflow = isOpen ? 'hidden' : '';
};

watch(
    () => props.open,
    (isOpen) => {
        syncBodyScroll(isOpen);
    },
);

onMounted(() => {
    window.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown);
    syncBodyScroll(false);
});
</script>
