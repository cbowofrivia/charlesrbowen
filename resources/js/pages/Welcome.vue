<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { defineAsyncComponent } from 'vue';

const Chat = defineAsyncComponent(() => import('@/components/Chat.vue'));
</script>

<template>
    <Head title="Charles Bowen" />
    <div class="scanlines flex h-screen flex-col bg-od-bg text-od-text">
        <!-- Terminal title bar -->
        <header class="flex items-center gap-2 border-b border-od-border bg-od-gutter px-4 py-2.5">
            <span class="h-2.5 w-2.5 rounded-full bg-od-green" />
            <span class="text-sm text-od-muted-foreground">~/charles-bowen</span>
        </header>

        <Chat />
    </div>
</template>
