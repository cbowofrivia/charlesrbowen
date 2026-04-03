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
        class="border-l-2 py-2 pl-4"
        :class="
            message.role === 'user'
                ? 'border-od-green/50'
                : 'border-od-blue/40'
        "
    >
        <span
            class="mb-1 block text-xs"
            :class="
                message.role === 'user'
                    ? 'text-od-green'
                    : 'text-od-blue'
            "
        >
            {{ message.role === 'user' ? '> you' : '> bot' }}
        </span>

        <div v-if="message.role === 'user'" class="text-sm text-od-bright">
            {{ message.content }}
        </div>

        <template v-else>
            <div
                v-if="message.content"
                class="prose prose-sm prose-invert prose-compact max-w-none prose-p:text-od-text prose-strong:text-od-bright prose-a:text-od-blue prose-a:no-underline hover:prose-a:underline prose-code:text-od-cyan prose-pre:bg-od-gutter prose-pre:border prose-pre:border-od-border prose-headings:text-od-bright prose-li:text-od-text"
                v-html="renderedContent"
            />
            <span
                v-else
                class="inline-block h-4 w-2 animate-pulse bg-od-blue"
            />
        </template>
    </div>
</template>
