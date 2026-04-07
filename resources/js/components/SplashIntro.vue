<script setup lang="ts">
import { Code, Briefcase, Lightbulb } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';
import { Avatar, AvatarImage } from '@/components/ui/avatar';

const emit = defineEmits<{
  complete: [message?: string];
}>();

const started = ref(false);
const ready = ref(false);
const done = ref(false);

const suggestions = [
  { label: 'Tech Stack', icon: Code, prompt: "What's his tech stack?" },
  {
    label: 'Experience',
    icon: Briefcase,
    prompt: 'Tell me about his work experience and career history.',
  },
  {
    label: 'Interests',
    icon: Lightbulb,
    prompt: 'What are his interests and what is he exploring right now?',
  },
];

function complete(message?: string) {
  if (!ready.value || done.value) {
    return;
  }

  done.value = true;

  setTimeout(() => {
    emit('complete', message);
  }, 400);
}

function onKeydown(e: KeyboardEvent) {
  if (e.key === '/' || e.key === 'Escape') {
    return;
  }

  complete();
}

onMounted(() => {
  started.value = true;

  setTimeout(() => {
    ready.value = true;
  }, 2000);

  window.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
  <div
    class="flex flex-1 flex-col items-center justify-center px-6 transition-all duration-400 ease-out"
    :class="
      done ? 'pointer-events-none -translate-y-4 opacity-0' : 'opacity-100'
    "
    @click.self="complete()"
  >
    <Avatar
      class="mb-5 size-14"
      :style="{
        animation: started ? 'fade-in 0.4s ease-out 0.1s both' : 'none',
        opacity: 0,
      }"
    >
      <AvatarImage
        src="/images/charles.webp"
        alt="Charles R. Bowen"
        style="image-rendering: pixelated"
      />
    </Avatar>

    <div class="mb-3">
      <span
        class="inline-block overflow-hidden border-r-[3px] text-2xl font-bold whitespace-nowrap text-od-bright"
        :style="{
          animation: started
            ? 'typewriter 0.8s steps(16) 0.2s forwards, blink-caret 1s step-end 0.2s infinite'
            : 'none',
          width: 0,
        }"
        >Charles R. Bowen</span
      >
    </div>

    <p
      class="text-sm text-od-text"
      :style="{
        animation: started ? 'flicker-in 0.4s ease-out 1.4s both' : 'none',
        opacity: 0,
      }"
    >
      Product Engineer
    </p>

    <p
      class="mt-6 max-w-sm text-center text-xs text-od-text/60"
      :style="{
        animation: started ? 'fade-in 0.8s ease-out 2.5s both' : 'none',
        opacity: 0,
      }"
    >
      Instead of a CV, I built an AI agent. Ask it anything.
    </p>

    <div
      class="mt-6 flex flex-wrap justify-center gap-2"
      :style="{
        animation: started ? 'fade-slide-up 0.6s ease-out 3.2s both' : 'none',
        opacity: 0,
      }"
    >
      <button
        v-for="suggestion in suggestions"
        :key="suggestion.label"
        class="flex items-center gap-2 rounded border border-od-border px-4 py-3 text-xs text-od-text transition-colors hover:border-od-blue hover:text-od-blue"
        @click="complete(suggestion.prompt)"
      >
        <component :is="suggestion.icon" class="size-3.5" />
        {{ suggestion.label }}
      </button>
    </div>

    <p
      class="mt-4 text-xs text-od-text/60"
      :style="{
        animation: started ? 'fade-in 0.5s ease-out 3.6s both' : 'none',
        opacity: 0,
      }"
    >
      <span class="hidden md:inline">or press any key to start typing</span>
      <span class="md:hidden">or tap anywhere to start</span>
    </p>
  </div>
</template>
