<script setup lang="ts">
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue';
import ChatMessage from '@/components/ChatMessage.vue';
import type { ChatMessage as ChatMessageType } from '@/types';

const props = defineProps<{
  messages: readonly ChatMessageType[];
}>();

const container = ref<HTMLElement | null>(null);

function scrollToBottom() {
  nextTick(() => {
    if (container.value) {
      container.value.scrollTop = container.value.scrollHeight;
    }
  });
}

watch(
  () => props.messages.length,
  () => scrollToBottom(),
);

watch(
  () => props.messages[props.messages.length - 1]?.content,
  () => scrollToBottom(),
);

function onViewportResize() {
  scrollToBottom();
}

onMounted(() => {
  window.visualViewport?.addEventListener('resize', onViewportResize);
});

onUnmounted(() => {
  window.visualViewport?.removeEventListener('resize', onViewportResize);
});
</script>

<template>
  <div
    ref="container"
    class="flex-1 space-y-3 overflow-y-auto p-4 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-white/20 [&::-webkit-scrollbar-thumb:hover]:bg-white/30 [&::-webkit-scrollbar-track]:bg-transparent"
  >
    <ChatMessage
      v-for="message in messages"
      :key="message.id"
      :message="message"
    />
  </div>
</template>
