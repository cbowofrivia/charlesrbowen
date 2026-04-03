<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { defineAsyncComponent } from 'vue';

const Chat = defineAsyncComponent(() => import('@/components/Chat.vue'));
</script>

<template>
    <Head title="Charles Bowen" />
    <div
        class="flex h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <header class="border-b border-[#e3e3e0] p-4 dark:border-[#3E3E3A]">
            <h1 class="text-center text-lg font-medium">Charles Bowen</h1>
        </header>

        <Chat />
    </div>
</template>
