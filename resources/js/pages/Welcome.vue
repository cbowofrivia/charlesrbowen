<script setup lang="ts">
import { ref, defineAsyncComponent } from 'vue';
import SplashIntro from '@/components/SplashIntro.vue';
import { Avatar, AvatarImage } from '@/components/ui/avatar';

const Chat = defineAsyncComponent(() => import('@/components/Chat.vue'));

const showChat = ref(false);
const initialMessage = ref<string | undefined>();

function onSplashComplete(message?: string) {
  initialMessage.value = message;
  showChat.value = true;
}

function resetChat() {
  showChat.value = false;
  initialMessage.value = undefined;
}
</script>

<template>
  <div
    class="flex h-dvh items-center justify-center bg-od-gutter pt-[env(safe-area-inset-top)] text-od-text md:p-4"
  >
    <div
      class="scanlines flex h-full w-full flex-col overflow-hidden bg-od-bg md:h-[94vh] md:max-w-[900px] md:rounded-xl md:border md:border-od-border md:shadow-2xl md:shadow-black/50"
    >
      <header
        v-if="showChat"
        class="relative flex shrink-0 items-center border-b border-od-border bg-od-gutter px-4 py-2.5"
        style="animation: fade-in 0.3s ease-out both"
      >
        <div class="flex items-center gap-3 md:hidden">
          <Avatar class="size-5">
            <AvatarImage
              src="/images/charles.webp"
              alt="Charles R. Bowen"
              style="image-rendering: pixelated"
            />
          </Avatar>
          <span class="text-od-muted-foreground text-sm"
            >~/charles-r-bowen</span
          >
        </div>

        <div class="hidden items-center gap-2 md:flex">
          <button
            class="h-3 w-3 rounded-full bg-[#ff5f57] transition-opacity hover:opacity-80"
            aria-label="Close"
            @click="resetChat"
          />
          <span class="h-3 w-3 rounded-full bg-[#febc2e]" />
          <span class="h-3 w-3 rounded-full bg-[#28c840]" />
        </div>

        <span
          class="text-od-muted-foreground pointer-events-none absolute inset-0 hidden items-center justify-center text-sm md:flex"
        >
          ~/charles-r-bowen
        </span>
      </header>

      <SplashIntro v-if="!showChat" @complete="onSplashComplete" />

      <div
        v-if="showChat"
        class="flex min-h-0 flex-1 flex-col"
        style="animation: fade-in 0.3s ease-out both"
      >
        <Chat :initial-message="initialMessage" />
      </div>
    </div>
  </div>
</template>
