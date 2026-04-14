---
availability: Open to discussing opportunities — direct enquiries welcome
---

# Charles Robert Bowen — Product Engineer

## Summary

Product engineer with over 8 years of experience building user-facing products from concept to production. Combines strong technical execution with product thinking — comfortable speccing, designing, and shipping features end-to-end with minimal supervision. Thrives working cross-functionally with stakeholders in design, marketing, finance, and operations. Experienced across the full stack with a focus on Laravel, Vue.js, TypeScript, and Node.js.

Proposes solutions proactively, iterates from zero to one, and takes ownership of outcomes — not just output.

## Experience

> **Teemill** is a trading partnerships platform where charities, businesses, and brands partner with a sustainable print-on-demand manufacturer to sell merchandise and garments through bespoke e-commerce storefronts. **PodOS** grew out of Teemill — the same team and organisation, but a new company offering the all-in-one operating system as a standalone B2B product. PodOS covers everything from domain management and website hosting through to stock management, fulfilment, order management, and factory robotics for print-on-demand manufacturing. The platform serves hundreds of factories and tens of thousands of brands. Charles has been part of this organisation for over 8 years, progressing through four distinct roles.

### Project Lead Engineer — PodOS (Current Role)

**January 2025 – Present | Hybrid (near Bristol, UK)**

Responsible for end-to-end spec, design, ownership, and communication of product delivery. Given a high-level objective with low detail, Charles owns the process from stakeholder alignment through to shipped product — defining what gets built, how it rolls out, and delegating to junior developers where appropriate.

Day-to-day involves running cross-functional meetings with finance, marketing, and operations teams, mentoring engineers, making architecture decisions, and still writing significant volumes of production code.

Key achievements:

- **Transformed the internal email marketing platform into a full marketing automation system** — designed and led the build of audience segmentation, behavioural subscriber labelling, automated email flows, a rebuilt email rendering engine with dark mode and cross-client compatibility, and dynamic product recommendation blocks. This spanned backend APIs, frontend UI, OpenAPI schema, and permissions definitions across multiple systems simultaneously.
- **Built a subscriber segmentation and labelling system from scratch** — behavioural labels tracking purchases, product views, and demographic data. Labels are auto-enriched via event-driven background jobs, importable via CSV, and queryable through a rule-based segment builder used to target campaigns and automated flows. Designed and shipped the full UI and API for constructing rule-based audiences from subscriber attributes.
- **Built the automated email flows platform** — new flow types including winback series with automated coupon generation, segment-targeted flows, A/B testable flow duplication, and a unified B2C/B2B architecture.
- **Continued hardening the billing platform** — built a billing cycle catch-up mechanism to recover from missed scheduler windows, added Datadog tracing across the entire billing flow, introduced bulk pricing, flat invoice discounts, and database indexing for query performance.

Technologies: PHP, Laravel, Vue, TypeScript, Inertia.js, Tailwind CSS, PostgreSQL, Redis, Mailgun, Datadog, OpenAPI, Stripe

### Senior Software Engineer — PodOS

**January 2022 – January 2025 | Hybrid (UK)**

Part of the founding team of three engineers that built the PodOS platform from scratch. The team grew from 3 to 14 full-time engineers during this period. Responsible for major platform systems including billing, real-time chat, SEO tooling, and search — making architecture decisions across backend, frontend, and API layers.

Key achievements:

- **Built a complete B2B billing platform from the ground up** — dedicated PostgreSQL database, versioned billing API (OpenAPI), and full Stripe integration. Features include cron-based invoice generation with idempotency, credit control with auto-retry on failed payments, multi-currency support, usage reporting with bulk pricing, transaction CSV exports, and billing plan management. Feature-flagged launch across architecture, API, and UI.
- **Built a real-time chat platform** — WebSocket-based live updates (MQTT), rich text editor with markdown and syntax highlighting, file attachments, push notifications with service workers, @mentions, group DMs, channel management, end-to-end encrypted broadcasting, and a permissions system. Full backend API and frontend UI.
- **Built an internal SEO monitoring tool** — tracked keyword rankings, page-level metrics, and content quality across thousands of client stores. Configurable data grid, time-series metric aggregation, and keyword campaign management with A/B testing.
- **Shipped Elasticsearch search with live A/B testing** — prototyped and iterated a new search experience, validated through a live split test on real customer traffic. Made data-driven decisions including rolling back when results didn't justify the change.
- **Built the notification and subscriber acquisition system** — non-blocking notification overlays, configurable subscribe-for-discount offers, and a full push notification rewrite in TypeScript with auto-resubscribe logic.
- **Led the platform migration to support white-label B2B delivery** — multi-tenancy support and branding cleanup for client-facing readiness.
- **Ran a major Laravel framework upgrade** — framework upgrade across the core API with simultaneous test framework migration to Pest. Introduced Laravel Pint as the canonical auto-formatter.
- **Prototyped AI-powered SEO content generation** — early integration of the ChatGPT API to generate product descriptions in bulk for client stores.

Technologies: PHP, Laravel, Vue, TypeScript, Node.js, Tailwind CSS, PostgreSQL, MySQL, Redis, Elasticsearch, MQTT, Stripe, GCP, Docker, Kubernetes, Datadog, Sentry, Mailgun, OpenAPI

### Software Engineer — Teemill

**December 2018 – January 2022 | Isle of Wight, UK**

Grew from a platform engineer into someone owning major systems end-to-end. Built and shipped core infrastructure and product features across the Teemill platform — a print-on-demand operating system serving thousands of client stores.

Key achievements:

- **Built and launched an internal email marketing platform end-to-end** — from domain warmup and deliverability infrastructure (DMARC/DNS setup, bounce/complaint webhook handlers, Mailgun integration) through to a drag-and-drop email builder, campaign scheduling, mailing list management, subscriber origin tracking, and campaign statistics with revenue attribution. The platform now sends close to 300 million emails per year.
- **Built a real-time chat platform** — MQTT-based messaging with end-to-end encrypted broadcasting, file and image uploads, channel management, task assignment from messages, content moderation, team mentions, and deep-linking to messages.
- **Built and owned a Node.js image processing microservice** — image transformation (resize, crop, format conversion) backed by GCP and Redis. Designed an encrypted URL signing scheme and led a phased platform-wide migration routing all images through the service.
- **Led incremental major version upgrades** — Laravel 7 to 8, PHP 7.4 to 8.0. Introduced PHPStan for static analysis and migrated the test suite from PHPUnit to Pest.

Technologies: PHP, Laravel, Vue, Node.js, JavaScript, GCP, GCS, Redis, MQTT, Docker, Kubernetes, Mailgun, PHPStan, Pest

### Software Engineering Trainee — Teemill

**November 2017 – November 2018 | Isle of Wight, UK**

First engineering role after a self-taught pivot from geology to software. Shipped production features from the first months — building application and storefront features across the Teemill platform.

Key achievements:

- **Built a multi-tenant blog platform** — allowing client stores to publish front-facing blog content for their customers
- **Worked extensively in factory code** — bugfixing, refactoring, and continuous improvement projects across the manufacturing software
- **Integrated early web push notifications** — allowing timely notifications to store owners across the platform
- **Built an early email marketing integration** — an initial implementation that laid the groundwork for the platform's later full-scale email marketing system

Technologies: PHP, Laravel, Vue.js, JavaScript, MySQL

## Education

### BSc Physical Geography & Geology — University of Plymouth

**2012 – 2016 | 2:1**

Used ArcGIS for map modelling and data analysis, which sparked an interest in software development. After graduating, spent a year self-teaching PHP, JavaScript, CSS, and Linux through personal projects — including Raspberry Pi automations and IoT builds — before landing the trainee role at Teemill.

## Technical Skills

**Languages:** - PHP, JavaScript, TypeScript, HTML, CSS
**Backend:** - Laravel, Node.js, Inertia.js, Server-Side Rendering (SSR)
**Frontend:** - Vue.js, Tailwind CSS, TipTap
**Databases:** - PostgreSQL, MySQL, SQLite, Redis, Elasticsearch
**Infrastructure:** - GCP, Docker, Kubernetes, CI/CD, GitHub Actions
**Email & Deliverability:** - Mailgun, DMARC/DNS, domain warmup, bounce handling, campaign analytics
**APIs & Integration:** - Stripe, OpenAPI/YAML, REST, MQTT, WebPush
**AI:** - Laravel AI SDK, Anthropic Claude API, OpenAI API, agentic AI patterns
**Monitoring:** - Datadog (APM, tracing), Sentry
**Practices:** - Test-driven development (Pest), static analysis (PHPStan), code review, A/B testing, agile, iterative delivery
**Tools:** - Git, Laravel Pint, Vite, Figma, Make.com

## Side Projects

### [dndplaybook.com](https://dndplaybook.com)

A D&D companion app and Charles's primary side project — self-funding and actively developed. Built with Laravel, Inertia.js, Vue 3, and TypeScript with server-side rendering. Users value it for its AI integrations. Built as a playground for exploring agentic AI, text-to-image generation, and emerging AI tooling. Showcases product thinking applied outside of work: identifying a niche, building for real users, and iterating based on usage.

### charlesrbowen.dev

This site. A chat-based CV built with Laravel 13, Vue 3, and Inertia.js v3. Visitors interact with an AI agent rather than reading a static page — demonstrating that Charles builds things differently when given the freedom to.

### dmarc-record-builder

An open-source PHP package for building DMARC records programmatically. Born from hands-on experience building email deliverability infrastructure at Teemill. Available on GitHub.

### Hardware & IoT

Built a custom gaming table with LED light strips controllable via the Philips Hue API, driven by an Arduino controller with custom lighting functions. Various Raspberry Pi and IoT automation projects over the years — the same curiosity that led from geology to software.

## Interests

Charles is an active tabletop gaming enthusiast. He plays D&D regularly with friends, has DM'd campaigns in the past, and built dndplaybook.com specifically to enhance his own games — bridging the physical tabletop experience with digital tools. He uses Obsidian as a campaign management tool alongside the app.

He's a keen reader, particularly fantasy fiction — Robin Hobb, Brandon Sanderson, and Andrzej Sapkowski are favourites. He's read the full Witcher saga, which ties into a broader interest in RPG-adjacent games like Skyrim and The Witcher 3. He also enjoys social board games — Blood on the Clocktower is a regular with friends.

Outside of screens, he plays cricket for a local village team on Sundays — casual, social, and a good excuse to get outdoors. He's also a keen cook, with a particular interest in outdoor cooking and smoking meat in the summer months.

On the hardware side, he still tinkers with IoT and home automation. He's currently working smart lighting and automations into a house renovation — the same curiosity that originally drew him from geology into software.

## How He Learns

Charles is a use-case-driven learner. He picks up new technologies when he can see a concrete application for them, rather than exploring for its own sake. For example, he adopted Meshy AI because it enabled a specific feature — turning 2D artwork into 3D-printable miniatures for D&D players — not because the technology was novel in isolation.

His primary learning vehicle is dndplaybook.com. Running the entire product end-to-end — from marketing and copywriting through to DevOps, SSR, modern CSS, TypeScript, and integrations with email providers, 3D modelling services, and AI generation APIs — keeps him across a broad surface area of the current landscape.

He subscribes to a wide range of RSS feeds and newsletters, following voices in the Laravel and open-source communities — Matt Pocock, Jess Archer, and Freek Van der Herten among them.

He also uses Obsidian as a personal knowledge and productivity system, synced via Dropbox and augmented with AI-driven automations — including a workflow where documents dropped into an inbox folder are automatically analysed, tagged, and filed by Claude.

## Working Style

Charles is initiative-driven — he proposes solutions proactively rather than waiting for detailed requirements. He's strongest when given a problem and the autonomy to figure out the path from zero to one.

He's a big believer in iterative development: find a working solution, ship it, then improve from there. This applies equally to solo work and collaborative product development.

A lot of his satisfaction comes from building products alongside other people — working cross-functionally with design, marketing, finance, and operations to solve real problems and seeing those products work for users.

In a room, Charles tends to be the more outspoken and lighthearted presence — particularly in engineering settings where others may be quieter. He keeps things light without losing focus, which helps sustain energy through long spec sessions and technical discussions.

He's empathetic and diplomatic in how he communicates. He's conscious that divisions within teams are easy to create and hard to repair, so he's deliberate about how he delivers feedback and navigates disagreement. That said, he will push back — especially on technical decisions. He sees it as a core part of his role to represent the technical perspective when it diverges from business or product priorities, without being stubborn about it.

He builds relationships broadly, not just within his immediate team. He values in-person and voice communication over async text, believing that the most valuable information often surfaces in the margins of conversation rather than in the main agenda. Having strong cross-team relationships is something he sees as directly contributing to better delivery.

If asked what he's most proud of: the billing platform he built at PodOS. Not just for the technical complexity — time zones, currencies, multi-product Stripe integration — but for the cross-functional collaboration it demanded, working closely with finance, tax, accounting, sales, and support teams to get it right.

His career trajectory speaks for itself — self-taught after a geology degree, starting as a trainee and progressing to one of the most senior engineers in the organisation, with a large hand in building the platform as it exists today.

## Contact

- **Email:** charlesrbowen@gmail.com
- **LinkedIn:** [linkedin.com/in/charles-r-bowen](https://linkedin.com/in/charles-r-bowen)
- **GitHub:** [github.com/cbowofrivia](https://github.com/cbowofrivia)
- **Location:** Near Bristol, UK
- **Work arrangement:** Open to remote, hybrid, or onsite — happy to discuss

## What He's Looking For

Charles thrives in startup and scale-up environments — that's where all of his experience lies and where he's done his best work. He values the flexibility, ownership, and pace that come with smaller, growing teams.

He's drawn to roles where software solves physical-world problems. The intersection of digital and physical is what he finds most compelling about engineering — it's the thread that runs through his work on factory systems, print-on-demand fulfilment, and email at scale.

What matters most: product impact, a strong team, and clearly defined goals. He believes most projects fail due to misaligned goals, and he's at his best when the objective is sharp and the team is motivated. He cares deeply about who he's working with day-to-day — good peers and good leadership both matter.

He sees himself staying in the web, cloud, or platform space. He prefers working in his existing stack but has always been open to learning new technologies when the role calls for it.

**Location:** Open to remote, hybrid, or onsite. Happy to commute into Bristol, Bath, or the surrounding area. London is viable if hybrid — a couple of days a week for the right role.
