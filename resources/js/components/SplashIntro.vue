<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Avatar, AvatarImage } from '@/components/ui/avatar';

const emit = defineEmits<{
  complete: [message?: string];
}>();

const started = ref(false);
const ready = ref(false);
const done = ref(false);

const suggestions = [
  "What's his tech stack?",
  'Tell me about his experience',
  'What has he built?',
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
  >
    <!-- Avatar -->
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

    <!-- Name with typewriter -->
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

    <!-- Subtitle with flicker -->
    <p
      class="text-sm text-od-text"
      :style="{
        animation: started ? 'flicker-in 0.4s ease-out 1.4s both' : 'none',
        opacity: 0,
      }"
    >
      product engineer
    </p>

    <!-- Tagline -->
    <p
      class="mt-6 max-w-sm text-center text-xs text-od-text/60"
      :style="{
        animation: started ? 'fade-in 0.8s ease-out 2.5s both' : 'none',
        opacity: 0,
      }"
    >
      Instead of a CV, I built a bot. Ask it about me.
    </p>

    <!-- Suggestion buttons -->
    <div
      class="mt-6 flex flex-wrap justify-center gap-2"
      :style="{
        animation: started ? 'fade-slide-up 0.6s ease-out 3.2s both' : 'none',
        opacity: 0,
      }"
    >
      <button
        v-for="suggestion in suggestions"
        :key="suggestion"
        class="rounded border border-od-border px-3 py-1.5 text-xs text-od-text transition-colors hover:border-od-blue hover:text-od-blue"
        @click="complete(suggestion)"
      >
        {{ suggestion }}
      </button>
    </div>

    <!-- Or type hint -->
    <p
      class="mt-4 text-xs text-od-text/60"
      :style="{
        animation: started ? 'fade-in 0.5s ease-out 3.6s both' : 'none',
        opacity: 0,
      }"
    >
      or press any key to start typing
    </p>
  </div>
</template>
