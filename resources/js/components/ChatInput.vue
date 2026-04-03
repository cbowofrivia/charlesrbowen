<script setup lang="ts">
import { ref, watch, nextTick, onMounted } from 'vue';

defineProps<{
    disabled?: boolean;
}>();

const emit = defineEmits<{
    send: [message: string];
}>();

const input = ref('');
const textarea = ref<HTMLTextAreaElement | null>(null);

function resize() {
    nextTick(() => {
        if (!textarea.value) return;

        textarea.value.style.height = 'auto';
        textarea.value.style.height = `${textarea.value.scrollHeight}px`;
    });
}

watch(input, resize);

onMounted(() => {
    textarea.value?.focus();
});

function handleSend() {
    const trimmed = input.value.trim();

    if (!trimmed) {
        return;
    }

    emit('send', trimmed);
    input.value = '';
}

function handleKeydown(event: KeyboardEvent) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        handleSend();
    }
}
</script>

<template>
    <div class="border-t border-od-border bg-od-bg-light px-4 py-3">
        <div class="flex items-start gap-3">
            <span class="mt-0.75 text-sm leading-none text-od-blue select-none"
                >&gt;</span
            >
            <textarea
                ref="textarea"
                v-model="input"
                :disabled="disabled"
                rows="1"
                placeholder="Ask about Charles..."
                class="max-h-40 flex-1 resize-none overflow-y-auto bg-transparent text-sm text-od-bright outline-none placeholder:text-od-border disabled:opacity-50"
                @keydown="handleKeydown"
            />
            <button
                :disabled="disabled || !input.trim()"
                class="mt-0.5 hidden text-od-blue disabled:opacity-30 touch:block"
                aria-label="Send message"
                @click="handleSend"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    class="h-5 w-5"
                >
                    <path
                        d="M3.105 2.288a.75.75 0 0 0-.826.95l1.414 4.926A1.5 1.5 0 0 0 5.135 9.25h6.115a.75.75 0 0 1 0 1.5H5.135a1.5 1.5 0 0 0-1.442 1.086l-1.414 4.926a.75.75 0 0 0 .826.95 28.897 28.897 0 0 0 15.293-7.155.75.75 0 0 0 0-1.114A28.897 28.897 0 0 0 3.105 2.288Z"
                    />
                </svg>
            </button>
        </div>
    </div>
</template>
