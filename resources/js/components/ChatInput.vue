<script setup lang="ts">
import { SendHorizontal } from 'lucide-vue-next';
import { ref, computed, watch, nextTick, onMounted } from 'vue';

const props = defineProps<{
  disabled?: boolean;
}>();

const emit = defineEmits<{
  send: [message: string];
  reset: [];
}>();

type Command = {
  name: string;
  description: string;
  prompt?: string;
  action?: () => void;
};

const commands: Command[] = [
  {
    name: '/skills',
    description: 'Technical skills & stack',
    prompt: "What are Charles's technical skills and tech stack?",
  },
  {
    name: '/experience',
    description: 'Work history',
    prompt: "Tell me about Charles's work experience and career history.",
  },
  {
    name: '/projects',
    description: "Things he's built",
    prompt: 'What projects has Charles worked on?',
  },
  {
    name: '/education',
    description: 'Education & learning',
    prompt: "What is Charles's educational background?",
  },
  {
    name: '/contact',
    description: 'How to reach him',
    prompt: 'How can I get in touch with Charles?',
  },
  {
    name: '/reset',
    description: 'Start a new conversation',
    action: () => emit('reset'),
  },
];

const input = ref('');
const textarea = ref<HTMLTextAreaElement | null>(null);
const showCommands = ref(false);
const selectedCommandIndex = ref(0);

const filteredCommands = computed(() => {
  if (!input.value.startsWith('/')) {
    return [];
  }

  const query = input.value.toLowerCase();

  return commands.filter((cmd) => cmd.name.startsWith(query));
});

watch(input, (value: string) => {
  showCommands.value =
    value.startsWith('/') && filteredCommands.value.length > 0;
  selectedCommandIndex.value = 0;
});

function resize() {
  nextTick(() => {
    if (!textarea.value) {
      return;
    }

    textarea.value.style.height = 'auto';
    textarea.value.style.height = `${textarea.value.scrollHeight}px`;
  });
}

watch(input, resize);

onMounted(() => {
  textarea.value?.focus();
});

watch(
  () => props.disabled,
  (disabled: boolean | undefined) => {
    if (!disabled) {
      nextTick(() => textarea.value?.focus());
    }
  },
);

function executeCommand(command: Command) {
  showCommands.value = false;
  input.value = '';

  if (command.action) {
    command.action();
  } else if (command.prompt) {
    emit('send', command.prompt);
  }
}

function handleSend() {
  if (showCommands.value && filteredCommands.value.length > 0) {
    executeCommand(filteredCommands.value[selectedCommandIndex.value]);

    return;
  }

  const trimmed = input.value.trim();

  if (!trimmed) {
    return;
  }

  emit('send', trimmed);
  input.value = '';
  nextTick(() => textarea.value?.focus());
}

function handleKeydown(event: KeyboardEvent) {
  if (showCommands.value) {
    if (event.key === 'ArrowDown') {
      event.preventDefault();
      selectedCommandIndex.value = Math.min(
        selectedCommandIndex.value + 1,
        filteredCommands.value.length - 1,
      );

      return;
    }

    if (event.key === 'ArrowUp') {
      event.preventDefault();
      selectedCommandIndex.value = Math.max(selectedCommandIndex.value - 1, 0);

      return;
    }

    if (event.key === 'Escape') {
      event.preventDefault();
      showCommands.value = false;

      return;
    }

    if (event.key === 'Tab') {
      event.preventDefault();
      executeCommand(filteredCommands.value[selectedCommandIndex.value]);

      return;
    }
  }

  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault();
    handleSend();
  }
}
</script>

<template>
  <div
    class="relative border-t border-od-border bg-od-bg-light px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]"
  >
    <div
      v-if="showCommands"
      class="absolute right-0 bottom-full left-0 border-t border-od-border bg-od-gutter p-1"
    >
      <button
        v-for="(command, index) in filteredCommands"
        :key="command.name"
        class="flex w-full items-center gap-3 rounded px-3 py-1.5 text-left text-xs transition-colors"
        :class="
          index === selectedCommandIndex
            ? 'bg-od-border/50 text-od-bright'
            : 'text-od-text hover:bg-od-border/30'
        "
        @click="executeCommand(command)"
        @mouseenter="selectedCommandIndex = index"
      >
        <span class="text-od-blue">{{ command.name }}</span>
        <span class="text-od-border">{{ command.description }}</span>
      </button>
    </div>

    <div class="flex items-start gap-3">
      <span
        class="mt-0.75 text-[0.8125rem] leading-none text-od-blue select-none"
        >&gt;</span
      >
      <textarea
        ref="textarea"
        v-model="input"
        :disabled="disabled"
        rows="1"
        placeholder="Ask something..."
        class="scrollbar-none max-h-40 flex-1 resize-none overflow-y-auto bg-transparent text-[0.8125rem] leading-[1.4] text-od-bright outline-none placeholder:text-od-border disabled:opacity-50 touch:text-base"
        @keydown="handleKeydown"
      />
      <button
        :disabled="disabled || !input.trim()"
        class="hidden p-2 text-od-blue disabled:opacity-30 touch:block"
        aria-label="Send message"
        @click="handleSend"
      >
        <SendHorizontal class="size-5" />
      </button>
    </div>

    <p
      v-if="!input && !showCommands"
      class="mt-2 text-right text-xs text-od-text/30"
    >
      type <span class="text-od-blue/40">/</span> for commands
    </p>
  </div>
</template>
