# Conversation Analysis Feedback Loop — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a scheduled feedback loop that analyzes CvAgent conversations and emails improvement suggestions for CV content and agent prompting.

**Architecture:** A `ConversationAnalysisAgent` with structured JSON output analyzes conversations from a configurable rolling window. An artisan command (`conversations:analyze`) invokes the agent on a configurable schedule and sends the report via a `ConversationAnalysisReport` Mailable to a configured recipient. Postmark is used as the mail transport.

**Tech Stack:** Laravel 13, Laravel AI SDK, Pest, Postmark (via `symfony/postmark-mailer`)

---

### Task 1: Install Postmark Transport

**Files:**
- Modify: `composer.json` (dependency addition)

- [ ] **Step 1: Install symfony/postmark-mailer**

Run:
```bash
composer require symfony/postmark-mailer
```

Expected: Package installs successfully with no errors.

- [ ] **Step 2: Commit**

```bash
git add composer.json composer.lock
git commit -m "chore: add symfony/postmark-mailer for Postmark email transport"
```

---

### Task 2: Create Configuration File

**Files:**
- Create: `config/analysis.php`

- [ ] **Step 1: Create the config file**

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Analysis Window
    |--------------------------------------------------------------------------
    |
    | The number of days to look back when analyzing conversations. The
    | analysis agent will receive all conversations from this rolling
    | window, regardless of whether they were included in a previous
    | report.
    |
    */

    'window_days' => (int) env('ANALYSIS_WINDOW_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Schedule
    |--------------------------------------------------------------------------
    |
    | How often the analysis command should run. Accepts any valid cron
    | expression. Defaults to weekly (Mondays at 9:00 AM).
    |
    */

    'schedule' => env('ANALYSIS_SCHEDULE', '0 9 * * 1'),

    /*
    |--------------------------------------------------------------------------
    | Recipient
    |--------------------------------------------------------------------------
    |
    | The email address that analysis reports should be sent to.
    |
    */

    'recipient' => env('ANALYSIS_RECIPIENT', 'charlesrbowen@gmail.com'),

];
```

- [ ] **Step 2: Commit**

```bash
git add config/analysis.php
git commit -m "feat: add analysis config for conversation feedback loop"
```

---

### Task 3: Create the ConversationAnalysisAgent

**Files:**
- Create: `app/Ai/Agents/ConversationAnalysisAgent.php`
- Test: `tests/Feature/Ai/Agents/ConversationAnalysisAgentTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Ai/Agents/ConversationAnalysisAgentTest.php`:

```php
<?php

use App\Ai\Agents\ConversationAnalysisAgent;
use App\Enums\MessageRole;
use App\Models\Conversation;
use App\Models\Message;

beforeEach(function () {
    ConversationAnalysisAgent::fake();
});

it('produces structured output with the expected schema keys', function () {
    $response = (new ConversationAnalysisAgent(
        conversations: collect(),
        cvContent: 'Test CV content',
        promptContent: 'Test prompt content',
    ))->prompt('Analyze these conversations.');

    expect($response['gap_analysis'])->toBeArray();
    expect($response['prompt_effectiveness'])->toBeArray();
    expect($response['cv_suggestions'])->toBeArray();
    expect($response['summary'])->toBeArray();
});

it('includes conversation data in its instructions', function () {
    $conversation = Conversation::factory()->create();
    $conversation->messages()->createMany([
        ['role' => MessageRole::User, 'content' => 'What DevOps experience does Charles have?'],
        ['role' => MessageRole::Assistant, 'content' => 'That information is not in the CV.'],
    ]);

    $agent = new ConversationAnalysisAgent(
        conversations: Conversation::with('messages')->get(),
        cvContent: 'Test CV content',
        promptContent: 'Test prompt content',
    );

    $instructions = $agent->instructions();

    expect((string) $instructions)
        ->toContain('What DevOps experience does Charles have?')
        ->toContain('That information is not in the CV.')
        ->toContain('Test CV content')
        ->toContain('Test prompt content');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact tests/Feature/Ai/Agents/ConversationAnalysisAgentTest.php`
Expected: FAIL — class `ConversationAnalysisAgent` not found.

- [ ] **Step 3: Create the agent**

Create `app/Ai/Agents/ConversationAnalysisAgent.php`:

```php
<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Collection;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[Temperature(0.3)]
#[MaxTokens(4096)]
class ConversationAnalysisAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        protected Collection $conversations,
        protected string $cvContent,
        protected string $promptContent,
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $conversationData = $this->formatConversations();

        return <<<PROMPT
        You are an expert conversation analyst. Your job is to review interactions that visitors have had with a CV chatbot agent and produce actionable feedback.

        ## Context

        The chatbot agent represents Charles Bowen on his portfolio website. It answers questions about his professional experience, skills, and background using the CV and prompt instructions provided below.

        ### Current Agent Prompt Instructions

        {$this->promptContent}

        ### Current CV Content

        {$this->cvContent}

        ### Conversations to Analyze

        {$conversationData}

        ## Your Task

        Analyze the conversations above and produce a structured report covering:

        1. **Gap Analysis** — Identify questions the agent couldn't answer well, topics where it said "not in the CV" or gave vague responses, and areas visitors expected to be covered but weren't. Include specific quotes from conversations as evidence.

        2. **Prompt Effectiveness** — Evaluate whether the agent's tone, format, and behavior matched the prompt instructions. Flag responses that were too verbose, too terse, off-brand, broke guardrails, or could have been better structured. Include specific examples.

        3. **CV Content Suggestions** — Based on visitor interest patterns, suggest specific additions, updates, or reorganizations to the CV content. Focus on gaps that multiple visitors hit or topics that generated the most engagement.

        4. **Conversation Summary** — Provide an overview: total conversations analyzed, total messages, most common topics, and any notable or unusual interactions worth highlighting.

        If there are no conversations to analyze, provide a brief heartbeat report noting that the system is running but there was no activity.
        PROMPT;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'gap_analysis' => $schema->array()
                ->items(
                    $schema->object(fn (JsonSchema $schema) => [
                        'topic' => $schema->string()->required(),
                        'description' => $schema->string()->required(),
                        'evidence' => $schema->string()->required(),
                        'severity' => $schema->string()->enum(['low', 'medium', 'high'])->required(),
                    ])
                )
                ->required(),
            'prompt_effectiveness' => $schema->array()
                ->items(
                    $schema->object(fn (JsonSchema $schema) => [
                        'observation' => $schema->string()->required(),
                        'example' => $schema->string()->required(),
                        'suggestion' => $schema->string()->required(),
                    ])
                )
                ->required(),
            'cv_suggestions' => $schema->array()
                ->items(
                    $schema->object(fn (JsonSchema $schema) => [
                        'section' => $schema->string()->required(),
                        'recommendation' => $schema->string()->required(),
                        'rationale' => $schema->string()->required(),
                    ])
                )
                ->required(),
            'summary' => $schema->object(fn (JsonSchema $schema) => [
                'conversation_count' => $schema->integer()->required(),
                'message_count' => $schema->integer()->required(),
                'common_topics' => $schema->array()->items($schema->string())->required(),
                'notable_interactions' => $schema->string()->required(),
                'is_heartbeat' => $schema->boolean()->required(),
            ])->required(),
        ];
    }

    /**
     * Format conversations into a readable string for the agent.
     */
    protected function formatConversations(): string
    {
        if ($this->conversations->isEmpty()) {
            return 'No conversations in this analysis window.';
        }

        return $this->conversations->map(function ($conversation, $index) {
            $messages = $conversation->messages->map(function ($message) {
                $role = strtoupper($message->role->value);

                return "[{$role}]: {$message->content}";
            })->implode("\n");

            $date = $conversation->created_at->format('Y-m-d H:i');

            return "--- Conversation #".($index + 1)." ({$date}) ---\n{$messages}";
        })->implode("\n\n");
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `php artisan test --compact tests/Feature/Ai/Agents/ConversationAnalysisAgentTest.php`
Expected: PASS (2 tests)

- [ ] **Step 5: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 6: Commit**

```bash
git add app/Ai/Agents/ConversationAnalysisAgent.php tests/Feature/Ai/Agents/ConversationAnalysisAgentTest.php
git commit -m "feat: add ConversationAnalysisAgent with structured output schema"
```

---

### Task 4: Create the Mailable

**Files:**
- Create: `app/Mail/ConversationAnalysisReport.php`
- Create: `resources/views/mail/analysis-report.blade.php`
- Test: `tests/Feature/Mail/ConversationAnalysisReportTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Mail/ConversationAnalysisReportTest.php`:

```php
<?php

use App\Mail\ConversationAnalysisReport;
use Carbon\CarbonImmutable;

it('renders the report with all sections when data is present', function () {
    $report = [
        'gap_analysis' => [
            [
                'topic' => 'DevOps experience',
                'description' => 'Visitors asked about Docker/Kubernetes experience',
                'evidence' => 'User asked: "What DevOps experience does Charles have?"',
                'severity' => 'high',
            ],
        ],
        'prompt_effectiveness' => [
            [
                'observation' => 'Agent was too verbose in responses',
                'example' => 'Response was 500 words for a simple question',
                'suggestion' => 'Add instruction to keep responses under 200 words',
            ],
        ],
        'cv_suggestions' => [
            [
                'section' => 'Technical Skills',
                'recommendation' => 'Add Docker and Kubernetes prominently',
                'rationale' => '3 visitors asked about containerization',
            ],
        ],
        'summary' => [
            'conversation_count' => 12,
            'message_count' => 48,
            'common_topics' => ['Technical skills', 'Work experience', 'Availability'],
            'notable_interactions' => 'One visitor had a 20-message conversation about billing platform architecture.',
            'is_heartbeat' => false,
        ],
    ];

    $windowStart = CarbonImmutable::parse('2026-03-16');
    $windowEnd = CarbonImmutable::parse('2026-04-15');

    $mailable = new ConversationAnalysisReport($report, $windowStart, $windowEnd);

    $mailable->assertHasSubject('CV Agent Analysis Report — 16 Mar to 15 Apr 2026');
    $mailable->assertSeeInHtml('DevOps experience');
    $mailable->assertSeeInHtml('Agent was too verbose');
    $mailable->assertSeeInHtml('Add Docker and Kubernetes');
    $mailable->assertSeeInHtml('12 conversations');
    $mailable->assertSeeInHtml('48 messages');
});

it('renders a heartbeat report when there are no conversations', function () {
    $report = [
        'gap_analysis' => [],
        'prompt_effectiveness' => [],
        'cv_suggestions' => [],
        'summary' => [
            'conversation_count' => 0,
            'message_count' => 0,
            'common_topics' => [],
            'notable_interactions' => '',
            'is_heartbeat' => true,
        ],
    ];

    $windowStart = CarbonImmutable::parse('2026-03-16');
    $windowEnd = CarbonImmutable::parse('2026-04-15');

    $mailable = new ConversationAnalysisReport($report, $windowStart, $windowEnd);

    $mailable->assertSeeInHtml('No conversations in this period');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact tests/Feature/Mail/ConversationAnalysisReportTest.php`
Expected: FAIL — class `ConversationAnalysisReport` not found.

- [ ] **Step 3: Generate the Mailable**

Run:
```bash
php artisan make:mail ConversationAnalysisReport --no-interaction
```

- [ ] **Step 4: Implement the Mailable**

Replace the contents of `app/Mail/ConversationAnalysisReport.php` with:

```php
<?php

namespace App\Mail;

use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConversationAnalysisReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{gap_analysis: array<int, array{topic: string, description: string, evidence: string, severity: string}>, prompt_effectiveness: array<int, array{observation: string, example: string, suggestion: string}>, cv_suggestions: array<int, array{section: string, recommendation: string, rationale: string}>, summary: array{conversation_count: int, message_count: int, common_topics: array<int, string>, notable_interactions: string, is_heartbeat: bool}}  $report
     */
    public function __construct(
        public array $report,
        public CarbonImmutable $windowStart,
        public CarbonImmutable $windowEnd,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CV Agent Analysis Report — '.$this->windowStart->format('j M').' to '.$this->windowEnd->format('j M Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.analysis-report',
        );
    }
}
```

- [ ] **Step 5: Create the Blade template**

Create `resources/views/mail/analysis-report.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1a1a1a; line-height: 1.6; max-width: 680px; margin: 0 auto; padding: 20px; }
        h1 { font-size: 22px; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; }
        h2 { font-size: 18px; color: #374151; margin-top: 28px; }
        .meta { color: #6b7280; font-size: 14px; margin-bottom: 24px; }
        .severity { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .severity-high { background: #fee2e2; color: #991b1b; }
        .severity-medium { background: #fef3c7; color: #92400e; }
        .severity-low { background: #dbeafe; color: #1e40af; }
        .item { background: #f9fafb; border-radius: 8px; padding: 16px; margin-bottom: 12px; }
        .item-title { font-weight: 600; margin-bottom: 4px; }
        .evidence, .example { font-style: italic; color: #6b7280; margin-top: 6px; font-size: 14px; }
        .stats { display: flex; gap: 24px; margin-bottom: 16px; }
        .stat { text-align: center; }
        .stat-value { font-size: 28px; font-weight: 700; color: #111827; }
        .stat-label { font-size: 13px; color: #6b7280; }
        .topics { margin-top: 8px; }
        .topic-tag { display: inline-block; background: #e5e7eb; padding: 2px 10px; border-radius: 12px; font-size: 13px; margin: 2px 4px 2px 0; }
        .heartbeat { text-align: center; padding: 40px 20px; color: #6b7280; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 12px; }
    </style>
</head>
<body>
    <h1>CV Agent Analysis Report</h1>
    <p class="meta">{{ $windowStart->format('j M Y') }} &mdash; {{ $windowEnd->format('j M Y') }}</p>

    @if($report['summary']['is_heartbeat'])
        <div class="heartbeat">
            <p><strong>No conversations in this period.</strong> The system is running normally.</p>
        </div>
    @else
        <h2>Summary</h2>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 16px;">
            <tr>
                <td style="text-align: center; padding: 12px;">
                    <div style="font-size: 28px; font-weight: 700; color: #111827;">{{ $report['summary']['conversation_count'] }}</div>
                    <div style="font-size: 13px; color: #6b7280;">conversations</div>
                </td>
                <td style="text-align: center; padding: 12px;">
                    <div style="font-size: 28px; font-weight: 700; color: #111827;">{{ $report['summary']['message_count'] }}</div>
                    <div style="font-size: 13px; color: #6b7280;">messages</div>
                </td>
            </tr>
        </table>

        @if(count($report['summary']['common_topics']) > 0)
            <div class="topics">
                <strong>Common topics:</strong>
                @foreach($report['summary']['common_topics'] as $topic)
                    <span class="topic-tag">{{ $topic }}</span>
                @endforeach
            </div>
        @endif

        @if($report['summary']['notable_interactions'])
            <p><strong>Notable:</strong> {{ $report['summary']['notable_interactions'] }}</p>
        @endif

        @if(count($report['gap_analysis']) > 0)
            <h2>Gap Analysis</h2>
            @foreach($report['gap_analysis'] as $gap)
                <div class="item">
                    <div class="item-title">
                        {{ $gap['topic'] }}
                        <span class="severity severity-{{ $gap['severity'] }}">{{ $gap['severity'] }}</span>
                    </div>
                    <p>{{ $gap['description'] }}</p>
                    <p class="evidence">{{ $gap['evidence'] }}</p>
                </div>
            @endforeach
        @endif

        @if(count($report['prompt_effectiveness']) > 0)
            <h2>Prompt Effectiveness</h2>
            @foreach($report['prompt_effectiveness'] as $item)
                <div class="item">
                    <div class="item-title">{{ $item['observation'] }}</div>
                    <p class="example">{{ $item['example'] }}</p>
                    <p><strong>Suggestion:</strong> {{ $item['suggestion'] }}</p>
                </div>
            @endforeach
        @endif

        @if(count($report['cv_suggestions']) > 0)
            <h2>CV Content Suggestions</h2>
            @foreach($report['cv_suggestions'] as $suggestion)
                <div class="item">
                    <div class="item-title">{{ $suggestion['section'] }}</div>
                    <p>{{ $suggestion['recommendation'] }}</p>
                    <p class="evidence">{{ $suggestion['rationale'] }}</p>
                </div>
            @endforeach
        @endif
    @endif

    <div class="footer">
        Generated automatically by charlesrbowen.com conversation analysis.
    </div>
</body>
</html>
```

- [ ] **Step 6: Run the test to verify it passes**

Run: `php artisan test --compact tests/Feature/Mail/ConversationAnalysisReportTest.php`
Expected: PASS (2 tests)

- [ ] **Step 7: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 8: Commit**

```bash
git add app/Mail/ConversationAnalysisReport.php resources/views/mail/analysis-report.blade.php tests/Feature/Mail/ConversationAnalysisReportTest.php
git commit -m "feat: add ConversationAnalysisReport mailable with Blade template"
```

---

### Task 5: Create the Artisan Command

**Files:**
- Create: `app/Console/Commands/AnalyzeConversationsCommand.php`
- Test: `tests/Feature/Console/AnalyzeConversationsCommandTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Console/AnalyzeConversationsCommandTest.php`:

```php
<?php

use App\Ai\Agents\ConversationAnalysisAgent;
use App\Enums\MessageRole;
use App\Mail\ConversationAnalysisReport;
use App\Models\Conversation;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    ConversationAnalysisAgent::fake();
    Mail::fake();
});

it('sends an analysis report email', function () {
    $conversation = Conversation::factory()->create([
        'created_at' => now()->subDays(5),
    ]);
    $conversation->messages()->createMany([
        ['role' => MessageRole::User, 'content' => 'Tell me about Charles'],
        ['role' => MessageRole::Assistant, 'content' => 'Charles is a product engineer.'],
    ]);

    $this->artisan('conversations:analyze')
        ->assertSuccessful();

    Mail::assertSent(ConversationAnalysisReport::class, function ($mail) {
        return $mail->hasTo(config('analysis.recipient'));
    });
});

it('sends a heartbeat report when no conversations exist', function () {
    $this->artisan('conversations:analyze')
        ->assertSuccessful();

    Mail::assertSent(ConversationAnalysisReport::class);
});

it('only includes conversations within the configured window', function () {
    config(['analysis.window_days' => 7]);

    // Old conversation — outside window
    $old = Conversation::factory()->create([
        'created_at' => now()->subDays(14),
    ]);
    $old->messages()->create([
        'role' => MessageRole::User,
        'content' => 'Old message',
    ]);

    // Recent conversation — inside window
    $recent = Conversation::factory()->create([
        'created_at' => now()->subDays(3),
    ]);
    $recent->messages()->create([
        'role' => MessageRole::User,
        'content' => 'Recent message',
    ]);

    $this->artisan('conversations:analyze')
        ->assertSuccessful();

    ConversationAnalysisAgent::assertPrompted(function ($prompt) {
        $instructions = $prompt->instructions;

        return str_contains($instructions, 'Recent message')
            && ! str_contains($instructions, 'Old message');
    });
});

it('prompts the agent with cv and prompt content', function () {
    $this->artisan('conversations:analyze')
        ->assertSuccessful();

    ConversationAnalysisAgent::assertPrompted(function ($prompt) {
        return str_contains($prompt->instructions, 'Product Engineer')
            && str_contains($prompt->instructions, 'Agent Instructions');
    });
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact tests/Feature/Console/AnalyzeConversationsCommandTest.php`
Expected: FAIL — command `conversations:analyze` not registered.

- [ ] **Step 3: Generate the command**

Run:
```bash
php artisan make:command AnalyzeConversationsCommand --no-interaction
```

- [ ] **Step 4: Implement the command**

Replace the contents of `app/Console/Commands/AnalyzeConversationsCommand.php` with:

```php
<?php

namespace App\Console\Commands;

use App\Ai\Agents\ConversationAnalysisAgent;
use App\Mail\ConversationAnalysisReport;
use App\Models\Conversation;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AnalyzeConversationsCommand extends Command
{
    protected $signature = 'conversations:analyze';

    protected $description = 'Analyze recent CvAgent conversations and email an improvement report';

    public function handle(): int
    {
        $windowDays = config('analysis.window_days', 30);
        $recipient = config('analysis.recipient');
        $windowStart = CarbonImmutable::now()->subDays($windowDays)->startOfDay();
        $windowEnd = CarbonImmutable::now();

        $this->info("Analyzing conversations from the last {$windowDays} days...");

        $conversations = Conversation::with('messages')
            ->where('created_at', '>=', $windowStart)
            ->get();

        $cvContent = file_get_contents(base_path('documents/cv.md')) ?: '';
        $promptContent = file_get_contents(base_path('documents/prompt.md')) ?: '';

        $agent = new ConversationAnalysisAgent(
            conversations: $conversations,
            cvContent: $cvContent,
            promptContent: $promptContent,
        );

        $this->info("Found {$conversations->count()} conversations. Running analysis...");

        $response = $agent->prompt('Analyze the conversations provided in your instructions.');

        /** @var array{gap_analysis: array<int, array{topic: string, description: string, evidence: string, severity: string}>, prompt_effectiveness: array<int, array{observation: string, example: string, suggestion: string}>, cv_suggestions: array<int, array{section: string, recommendation: string, rationale: string}>, summary: array{conversation_count: int, message_count: int, common_topics: array<int, string>, notable_interactions: string, is_heartbeat: bool}} $report */
        $report = $response->toArray();

        Mail::to($recipient)
            ->send(new ConversationAnalysisReport($report, $windowStart, $windowEnd));

        $this->info("Report sent to {$recipient}.");

        return self::SUCCESS;
    }
}
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test --compact tests/Feature/Console/AnalyzeConversationsCommandTest.php`
Expected: PASS (4 tests)

- [ ] **Step 6: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 7: Commit**

```bash
git add app/Console/Commands/AnalyzeConversationsCommand.php tests/Feature/Console/AnalyzeConversationsCommandTest.php
git commit -m "feat: add conversations:analyze artisan command"
```

---

### Task 6: Register the Schedule

**Files:**
- Modify: `routes/console.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Console/AnalyzeConversationsScheduleTest.php`:

```php
<?php

use Illuminate\Console\Scheduling\Schedule;

it('registers the conversations:analyze command on the configured schedule', function () {
    $schedule = app(Schedule::class);

    $events = collect($schedule->events())->filter(function ($event) {
        return str_contains($event->command, 'conversations:analyze');
    });

    expect($events)->not->toBeEmpty();
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact tests/Feature/Console/AnalyzeConversationsScheduleTest.php`
Expected: FAIL — no scheduled event found.

- [ ] **Step 3: Add the schedule to routes/console.php**

Add to the end of `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('conversations:analyze')
    ->cron(config('analysis.schedule', '0 9 * * 1'));
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `php artisan test --compact tests/Feature/Console/AnalyzeConversationsScheduleTest.php`
Expected: PASS

- [ ] **Step 5: Verify with schedule:list**

Run: `php artisan schedule:list`
Expected: Output includes `conversations:analyze` with the configured cron expression.

- [ ] **Step 6: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 7: Commit**

```bash
git add routes/console.php tests/Feature/Console/AnalyzeConversationsScheduleTest.php
git commit -m "feat: schedule conversations:analyze command"
```

---

### Task 7: Run Full Test Suite

**Files:** None (verification only)

- [ ] **Step 1: Run the full test suite**

Run: `php artisan test --compact`
Expected: All tests pass.

- [ ] **Step 2: Run linting and formatting**

Run:
```bash
vendor/bin/pint --dirty --format agent
npm run lint
npm run format
```

- [ ] **Step 3: Run CI check**

Run: `composer run ci:check`
Expected: All checks pass.
