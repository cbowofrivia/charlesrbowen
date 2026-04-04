<script setup lang="ts">
import { onMounted } from 'vue';
import ChatInput from '@/components/ChatInput.vue';
import ChatMessages from '@/components/ChatMessages.vue';
import { useChat } from '@/composables/useChat';

const props = defineProps<{
  initialMessage?: string;
}>();

const { messages, isStreaming, error, sendMessage } = useChat();

onMounted(() => {
  if (props.initialMessage) {
    sendMessage(props.initialMessage);
  }
});
</script>

<template>
  <ChatMessages :messages="messages" />

  <div v-if="error" class="px-4 py-2 text-center text-xs text-od-red">
    {{ error }}
  </div>

  <ChatInput :disabled="isStreaming" @send="sendMessage" />
</template>
