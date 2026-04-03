<?php

namespace App\Services;

class SystemPromptService
{
    /**
     * Get the assembled system prompt.
     */
    public function getPrompt(): string
    {
        return once(function () {
            $promptPath = storage_path('app/prompt.md');
            $cvPath = storage_path('app/cv.md');

            $guardrails = file_exists($promptPath) ? file_get_contents($promptPath) : '';
            $cv = file_exists($cvPath) ? file_get_contents($cvPath) : '';

            return trim($guardrails."\n\n---\n\n".$cv);
        });
    }
}
