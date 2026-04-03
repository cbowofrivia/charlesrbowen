<script setup lang="ts">
import ChatMessages from '@/components/ChatMessages.vue';
import ChatInput from '@/components/ChatInput.vue';
import { useChat } from '@/composables/useChat';

const { messages, isStreaming, error, sendMessage } = useChat();
</script>

<template>
    <ChatMessages :messages="messages" />

    <div
        v-if="error"
        class="px-4 py-2 text-center text-xs text-od-red"
    >
        {{ error }}
    </div>

    <ChatInput :disabled="isStreaming" @send="sendMessage" />
</template>
