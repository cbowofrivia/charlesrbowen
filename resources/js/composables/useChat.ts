import { useStream } from '@laravel/stream-vue';
import { ref, readonly } from 'vue';
import type { ChatMessage } from '@/types';

type ChatBody = { message: string; session_id: string };

const STORAGE_KEY = 'chat_session_id';

function generateUUID(): string {
  if (
    typeof crypto !== 'undefined' &&
    typeof crypto.randomUUID === 'function'
  ) {
    return crypto.randomUUID();
  }

  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
    const r = (Math.random() * 16) | 0;
    const v = c === 'x' ? r : (r & 0x3) | 0x8;

    return v.toString(16);
  });
}

export function hasExistingSession(): boolean {
  return sessionStorage.getItem(STORAGE_KEY) !== null;
}

export function clearSession(): void {
  sessionStorage.removeItem(STORAGE_KEY);
}

export function useChat() {
  const messages = ref<ChatMessage[]>([]);
  const error = ref<string | null>(null);
  const isLoading = ref(false);

  const sessionId = sessionStorage.getItem(STORAGE_KEY) ?? generateUUID();
  sessionStorage.setItem(STORAGE_KEY, sessionId);

  let currentAssistantId: string | null = null;

  const { isStreaming, send, cancel, clearData } = useStream<ChatBody>(
    '/chat',
    {
      onData: (chunk: string) => {
        if (!currentAssistantId) {
          return;
        }

        const message = messages.value.find(
          (m: ChatMessage) => m.id === currentAssistantId,
        );

        if (message) {
          message.content += chunk;
        }
      },
      onFinish: () => {
        currentAssistantId = null;
        clearData();
      },
      onResponse: (response: Response) => {
        if (response.status === 429) {
          error.value =
            'You are sending messages too quickly. Please wait a moment.';
          currentAssistantId = null;
        } else if (!response.ok) {
          error.value = 'Something went wrong. Please try again.';
          currentAssistantId = null;
        }
      },
      onError: () => {
        error.value =
          'Unable to connect. Please check your connection and try again.';
        currentAssistantId = null;
      },
    },
  );

  async function loadMessages(): Promise<void> {
    isLoading.value = true;

    try {
      const response = await fetch(`/chat/${sessionId}/messages`);

      if (response.ok) {
        const loaded: ChatMessage[] = await response.json();

        if (loaded.length > 0) {
          messages.value = loaded;
        }
      }
    } finally {
      isLoading.value = false;
    }
  }

  function sendMessage(text: string) {
    const trimmed = text.trim();

    if (!trimmed || isStreaming.value) {
      return;
    }

    error.value = null;

    messages.value.push({
      id: generateUUID(),
      role: 'user',
      content: trimmed,
    });

    const assistantId = generateUUID();
    currentAssistantId = assistantId;

    messages.value.push({
      id: assistantId,
      role: 'assistant',
      content: '',
    });

    send({ message: trimmed, session_id: sessionId });
  }

  function clearMessages() {
    messages.value = [];
    error.value = null;
  }

  return {
    messages: readonly(messages),
    isStreaming: readonly(isStreaming),
    isLoading: readonly(isLoading),
    error: readonly(error),
    sendMessage,
    loadMessages,
    clearMessages,
    cancel,
  };
}
