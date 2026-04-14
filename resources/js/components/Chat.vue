<script setup lang="ts">
import { onMounted } from 'vue';
import ChatInput from '@/components/ChatInput.vue';
import ChatMessages from '@/components/ChatMessages.vue';
import { useChat } from '@/composables/useChat';

const props = defineProps<{
  initialMessage?: string;
}>();

const emit = defineEmits<{
  reset: [];
}>();

const { messages, isStreaming, isLoading, error, sendMessage, loadMessages } =
  useChat();

onMounted(async () => {
  await loadMessages();

  if (props.initialMessage && messages.value.length === 0) {
    sendMessage(props.initialMessage);
  }
});
</script>

<template>
  <ChatMessages :messages="messages" />

  <div v-if="error" class="px-4 py-2 text-center text-xs text-od-red">
    {{ error }}
  </div>

  <ChatInput
    :disabled="isStreaming || isLoading"
    @send="sendMessage"
    @reset="emit('reset')"
  />
</template>
