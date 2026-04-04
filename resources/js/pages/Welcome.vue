<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
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
    <Head title="Charles R. Bowen" />

    <!-- Desktop: dark "desktop" background with floating window -->
    <!-- Mobile: full screen, no window chrome -->
    <div class="flex h-screen items-center justify-center bg-od-gutter text-od-text md:p-4">
        <div
            class="scanlines flex h-full w-full flex-col overflow-hidden bg-od-bg md:h-[94vh] md:max-w-[900px] md:rounded-xl md:border md:border-od-border md:shadow-2xl md:shadow-black/50"
        >
            <!-- Title bar (only visible during chat) -->
            <header
                v-if="showChat"
                class="relative flex shrink-0 items-center border-b border-od-border bg-od-gutter px-4 py-2.5"
                style="animation: fade-in 0.3s ease-out both"
            >
                <!-- Mobile: left-aligned with avatar -->
                <div class="flex items-center gap-3 md:hidden">
                    <Avatar class="size-5">
                        <AvatarImage src="/images/charles.webp" alt="Charles R. Bowen" style="image-rendering: pixelated" />
                    </Avatar>
                    <span class="text-sm text-od-muted-foreground">~/charles-r-bowen</span>
                </div>

                <!-- Desktop: window controls + centred title -->
                <div class="hidden items-center gap-2 md:flex">
                    <button
                        class="h-3 w-3 rounded-full bg-[#ff5f57] transition-opacity hover:opacity-80"
                        aria-label="Close"
                        @click="resetChat"
                    />
                    <span class="h-3 w-3 rounded-full bg-[#febc2e]" />
                    <span class="h-3 w-3 rounded-full bg-[#28c840]" />
                </div>

                <!-- Desktop title (centred) -->
                <span class="pointer-events-none absolute inset-0 hidden items-center justify-center text-sm text-od-muted-foreground md:flex">
                    ~/charles-r-bowen
                </span>
            </header>

            <!-- Splash intro -->
            <SplashIntro v-if="!showChat" @complete="onSplashComplete" />

            <!-- Chat -->
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
