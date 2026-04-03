<script setup lang="ts">
import { computed } from 'vue';
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import type { ChatMessage } from '@/types';

const props = defineProps<{
    message: ChatMessage;
}>();

const renderedContent = computed(() => {
    if (!props.message.content) return '';

    return DOMPurify.sanitize(marked.parse(props.message.content) as string);
});
</script>

<template>
    <div
        class="flex"
        :class="message.role === 'user' ? 'justify-end' : 'justify-start'"
    >
        <div
            class="max-w-[80%] rounded-lg px-4 py-2"
            :class="
                message.role === 'user'
                    ? 'bg-[#1b1b18] text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]'
                    : 'bg-[#f0f0ee] text-[#1b1b18] dark:bg-[#1c1c1a] dark:text-[#EDEDEC]'
            "
        >
            <div
                v-if="message.content"
                class="prose prose-sm dark:prose-invert max-w-none"
                v-html="renderedContent"
            />
            <span
                v-else-if="message.role === 'assistant'"
                class="inline-block h-4 w-1 animate-pulse bg-current"
            />
        </div>
    </div>
</template>
