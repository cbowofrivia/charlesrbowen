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
            $promptPath = base_path('documents/prompt.md');
            $cvPath = base_path('documents/cv.md');

            $guardrails = file_exists($promptPath) ? file_get_contents($promptPath) : '';
            $cv = file_exists($cvPath) ? file_get_contents($cvPath) : '';

            return trim($guardrails."\n\n---\n\n".$cv);
        });
    }
}
