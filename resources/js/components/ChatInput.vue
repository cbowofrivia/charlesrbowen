<script setup lang="ts">
import { ref } from 'vue';

defineProps<{
    disabled?: boolean;
}>();

const emit = defineEmits<{
    send: [message: string];
}>();

const input = ref('');

function handleSend() {
    const trimmed = input.value.trim();

    if (!trimmed) return;

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
    <div class="border-t border-[#e3e3e0] p-4 dark:border-[#3E3E3A]">
        <div class="flex gap-2">
            <textarea
                v-model="input"
                :disabled="disabled"
                rows="1"
                placeholder="Ask about Charles..."
                class="flex-1 resize-none rounded-lg border border-[#e3e3e0] bg-transparent px-3 py-2 text-sm outline-none placeholder:text-[#A1A09A] focus:border-[#1b1b18] disabled:opacity-50 dark:border-[#3E3E3A] dark:placeholder:text-[#706f6c] dark:focus:border-[#EDEDEC]"
                @keydown="handleKeydown"
            />
            <button
                :disabled="disabled || !input.trim()"
                class="rounded-lg bg-[#1b1b18] px-4 py-2 text-sm text-white disabled:opacity-50 dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                @click="handleSend"
            >
                Send
            </button>
        </div>
    </div>
</template>
