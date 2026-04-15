# Conversation Analysis Feedback Loop

**Date:** 2026-04-15
**Status:** Approved

## Overview

A scheduled feedback loop that analyzes CvAgent conversations, identifies improvements to CV content and agent prompting, and emails a structured report for manual review.

This is a **self-improvement tool** — the system surfaces insights, the user decides what to change.

## Architecture

### Analysis Agent

A new `ConversationAnalysisAgent` in `app/Ai/Agents/`, following the same pattern as the existing `CvAgent`. It receives:

- The full text of `documents/cv.md` and `documents/prompt.md` as context
- All conversations (with messages) from the configured rolling window

The agent uses a smarter model than CvAgent (no `#[UseCheapestModel]`) since analysis quality matters. It produces **structured JSON output** covering four areas:

1. **Gap Analysis** — Questions the CvAgent couldn't answer well, "not in the CV" moments, topics visitors expected but weren't covered
2. **Prompt Effectiveness** — Places where tone, format, or guardrail behavior didn't match intent (too verbose, too terse, off-brand responses)
3. **CV Content Suggestions** — Skills/experience worth adding or updating based on visitor interest patterns
4. **Conversation Summary** — Volume stats, common topics, notable interactions

### Scheduled Command

A new artisan command `conversations:analyze` that:

1. Queries conversations from the rolling window (configurable via `ANALYSIS_WINDOW_DAYS`, default: 30)
2. Loads the current `cv.md` and `prompt.md` content
3. If no conversations exist in the window, passes a flag so the agent produces a "no activity" heartbeat report
4. Invokes the `ConversationAnalysisAgent` with all the context
5. Sends the report email via a Mailable

The command is registered in the Laravel scheduler with a configurable frequency (default: weekly). It can also be run manually anytime with `php artisan conversations:analyze`.

### Configuration

A new `config/analysis.php` with:

- `window_days` — Rolling window in days (default: 30)
- `schedule` — Cron expression or named frequency (default: weekly)
- `recipient` — Email address for reports

### Email Transport

Uses **Postmark** as the mail driver. The user has an existing Postmark account. Configuration requires:

- `MAIL_MAILER=postmark` in `.env`
- `POSTMARK_TOKEN` in `.env`
- `composer require symfony/postmark-mailer` (Laravel's built-in Postmark support)

### Email Report

A `ConversationAnalysisReport` Mailable that renders the structured agent output as a clean HTML email:

1. **Summary header** — Date range, conversation count, message count
2. **Gap Analysis** — Bulleted list of unanswered/poorly-answered topics with example quotes
3. **Prompt Effectiveness** — Specific observations about agent behavior with examples
4. **CV Content Suggestions** — Concrete recommendations (e.g., "Consider adding Docker to Technical Skills — 3 visitors asked about it")
5. **Heartbeat case** — When there's nothing to report: "No conversations in this period. The system is running normally."

## Data Flow

```
Scheduler triggers conversations:analyze
  -> Command loads config (window, recipient)
  -> Queries Conversation::with('messages')->where('created_at', '>=', $windowStart)
  -> Loads documents/cv.md + documents/prompt.md
  -> Invokes ConversationAnalysisAgent
  -> Agent returns structured JSON
  -> Command dispatches ConversationAnalysisReport Mailable
  -> Email lands in inbox
```

## What We're NOT Building

- No dashboard UI — email only for V1
- No automated CV/prompt editing — manual review only
- No per-conversation scoring or tagging — single holistic analysis per run
- No new database tables or migrations — existing `conversations` and `messages` tables have everything needed

## Testing

- **Feature test for the command** — Seeds conversations, runs `conversations:analyze`, asserts the Mailable was sent with expected structure
- **Agent output test** — Verifies the agent produces valid structured output against the JSON schema (can use a fake/mock AI response)
- **Mailable test** — Asserts the rendered email contains expected sections and handles both "has data" and "heartbeat" cases
- **Edge cases** — Empty window (heartbeat), single conversation, conversations with only user messages
