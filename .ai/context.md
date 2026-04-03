# App Context

## What This Is

`charlesrbowen.com` is a CV/portfolio application with a chat interface instead of a traditional UI. Rather than a page to read, visitors interact with an AI agent that represents Charles — answering questions about his experience, skills, and projects conversationally.

The target audience is recruiters and professional contacts. The agent speaks in the **third person** (not as Charles) to maintain a safe separation between the AI and the person.

## Core Concept

- Single-page application: a splash screen leading into a chat interface
- An AI agent loaded with Charles's CV/experience data via a system prompt
- The agent can discuss experience, education, projects, and technical/soft skills
- Guardrails prevent off-limits topics (e.g. salary expectations, personal matters)
- Streaming responses for a polished, familiar chat experience

## Key Decisions

| Decision | Choice | Reason |
|---|---|---|
| Agent persona | Third person | Avoids weirdness/liability if guardrails break |
| Provider layer | Laravel AI SDK (`laravel/ai`) | First-party, provider-agnostic; failover between Claude, GPT-4o, etc. |
| CV data | Markdown config file | Simple, version-controlled, no DB admin UI needed |
| Visitor identity | Anonymous session UUID | No friction; no opt-in email required |
| Conversation storage | DB (conversations + messages) | Lets Charles review what people ask and iterate |
| Admin interface | None (v1) | File-based config managed through deployments |

## Data Model

- `conversations` — one per visitor session (keyed by session UUID)
- `messages` — role (user/assistant), content, timestamps, foreign key to conversation

## Roadmap (v1)

1. **Data model** — `conversations` and `messages` migrations, models, factories, relationship tests
2. **CV config + system prompt** — markdown CV file, service class to assemble system prompt with guardrails, placeholder content
3. **Laravel AI SDK + streaming endpoint** — install `laravel/ai`, `POST /chat` controller with streaming, conversation history as context, message persistence
4. **Rate limiting** — per-session limits on chat endpoint, configurable via `.env`, graceful frontend error
5. **Chat UI** — Vue chat component, SSE streaming display, session UUID in localStorage, message bubbles, auto-scroll, error handling, mobile-first
6. **Splash screen + styling** — intro screen with name/tagline/CTA, transition to chat, visual design pass, dark mode
7. **Cleanup + pre-launch** — remove leftover Wayfinder/Fortify files, populate real CV content, browser testing, production env config

## Out of Scope (v1)

- Admin UI for editing config or browsing conversations
- Visitor opt-in (name/email)
- Auth for visitors
- Multi-language support
- Analytics dashboard
