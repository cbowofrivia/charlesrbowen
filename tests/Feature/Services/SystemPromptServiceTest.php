<?php

use App\Services\SystemPromptService;

it('assembles the system prompt from prompt.md and cv.md', function () {
    $service = app(SystemPromptService::class);
    $prompt = $service->getPrompt();

    expect($prompt)
        ->toContain('Agent Instructions')
        ->toContain('Charles Bowen')
        ->toContain('Product Engineer');
});

it('includes guardrail content', function () {
    $service = app(SystemPromptService::class);
    $prompt = $service->getPrompt();

    expect($prompt)
        ->toContain('Off-Limits Topics')
        ->toContain('Salary')
        ->toContain('third person');
});

it('includes cv content', function () {
    $service = app(SystemPromptService::class);
    $prompt = $service->getPrompt();

    expect($prompt)
        ->toContain('Experience')
        ->toContain('Technical Skills')
        ->toContain('Education');
});

it('handles missing files gracefully', function () {
    $promptPath = base_path('documents/prompt.md');
    $cvPath = base_path('documents/cv.md');

    $promptBackup = file_get_contents($promptPath);
    $cvBackup = file_get_contents($cvPath);

    rename($promptPath, $promptPath.'.bak');
    rename($cvPath, $cvPath.'.bak');

    try {
        $service = new SystemPromptService;
        $prompt = $service->getPrompt();

        expect($prompt)->toBeString();
    } finally {
        rename($promptPath.'.bak', $promptPath);
        rename($cvPath.'.bak', $cvPath);
    }
});
