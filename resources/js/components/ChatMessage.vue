<script setup lang="ts">
import { computed } from 'vue';
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import { Avatar, AvatarImage } from '@/components/ui/avatar';
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
            class="mb-1 flex items-center gap-1.5 text-xs"
            :class="
                message.role === 'user'
                    ? 'text-od-green'
                    : 'text-od-blue'
            "
        >
            <Avatar v-if="message.role === 'assistant'" class="size-4">
                <AvatarImage
                    src="/images/charles.webp"
                    alt=""
                    style="image-rendering: pixelated"
                />
            </Avatar>
            {{ message.role === 'user' ? '> you' : '> bot' }}
        </span>

        <div v-if="message.role === 'user'" class="text-[0.8125rem] leading-[1.4] text-od-bright">
            {{ message.content }}
        </div>

        <template v-else>
            <div
                v-if="message.content"
                class="chat-markdown"
                v-html="renderedContent"
            />
            <span
                v-else
                class="inline-block h-4 w-2 animate-pulse bg-od-blue"
            />
        </template>
    </div>
</template>
