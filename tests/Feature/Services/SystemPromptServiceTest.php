<?php

use App\Services\SystemPromptService;
use Illuminate\Support\Facades\Storage;

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
    Storage::disk('local')->delete('prompt.md');
    Storage::disk('local')->delete('cv.md');

    $service = new SystemPromptService;
    $prompt = $service->getPrompt();

    expect($prompt)->toBeString();
});
