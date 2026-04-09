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

- **Transformed Teemail from a campaign tool into a full marketing automation platform** — designed and led the build of audience segmentation, behavioural subscriber labelling, automated email flows, and a modernised email rendering engine. This spanned backend APIs, frontend UI, OpenAPI schema, and permissions definitions simultaneously across four repositories.
- **Built a subscriber segmentation and labelling system from scratch** — behavioural labels tracking purchases, product views, and demographic data. Labels are auto-enriched via event-driven background jobs, importable via CSV, and queryable through a rule-based segment builder used to target campaigns and automated flows.
- **Designed and shipped the audience segment builder** — a full UI and API allowing marketers to construct rule-based audiences from subscriber attributes. Replaced legacy filter dropdowns with a flexible condition system featuring async option resolvers pulling from external data sources (product catalogues, countries, warehouses).
- **Built the automated email flows platform** — extended the flows system with new flow types (winback series with unique per-recipient coupon generation, surprise offers), segment integration, A/B testable flow duplication, and unified B2C/B2B flow architecture with shared components and composables.
- **Rebuilt the Teemail email rendering engine** — replaced a tightly coupled Blade component system with a modern `x-email` primitive library (layout, section, column, text, button, image, etc.) driven by a ThemeResolver singleton. Added dark mode support, Outlook VML compatibility, and a responsive multi-column layout system. 117 tests, 183 assertions.
- **Shipped dynamic product recommendation email blocks** — email blocks that populate a product grid from a collection at send time, with live preview in the page builder. Full backend rendering with currency-aware pricing, UTM tracking, and responsive two-column layout.
- **Continued hardening the billing platform** — built a billing cycle catch-up mechanism to recover from missed scheduler windows, added Datadog tracing across the entire billing flow, introduced bulk pricing, flat invoice discounts, and database indexing for query performance.

Technologies: PHP, Laravel, Vue, TypeScript, Inertia.js, Tailwind CSS, PostgreSQL, Redis, Mailgun, Datadog, OpenAPI, Stripe

### Senior Software Engineer — PodOS

**January 2022 – January 2025 | Hybrid (UK)**

Part of the founding team of three engineers that built the PodOS platform from scratch. The team grew from 3 to 14 full-time engineers during this period. Responsible for major platform systems including billing, real-time chat, SEO tooling, and search — making architecture decisions across backend, frontend, and API layers.

Key achievements:

- **Built a complete B2B billing platform from the ground up** — a dedicated BillingDB (PostgreSQL), versioned billing API (OpenAPI/YAML), and full Stripe integration. Features include cron-based invoice generation with idempotency, credit control with auto-retry on failed payments, multi-currency support, usage reporting with bulk pricing, transaction CSV exports, and billing plan management. Feature-flagged launch across architecture, API, and UI.
- **Rebuilt the chat system as a standalone cloud platform (Chat V2)** — ground-up rebuild with WebSocket-based live updates (MQTT), TipTap rich text editor with markdown and syntax highlighting, file attachments via Drive, push notifications (WebPush with service workers), @mentions, group DMs, channel management, and a permissions system. Full backend API and frontend UI.
- **Built SEO SuperGrid / Teerank** — an internal SEO monitoring tool tracking keyword rankings, page-level metrics, and content quality across thousands of operator stores. Features include a configurable data grid, time-series metric aggregation, semantic URL matching, and CSV-importable keyword campaign management with A/B testing.
- **Shipped Elasticsearch search with live A/B testing** — prototyped and iterated a new search experience, validated through a live split test on Rapanui's customer base. Made data-driven decisions including rolling back when results didn't justify the change.
- **Built the notification and email subscriber acquisition system** — new lightweight "shove notification" format for non-blocking overlays, operator-configurable subscribe-for-discount offers, and a full push notification rewrite in TypeScript with auto-resubscribe logic.
- **Led the cloud platform migration** — operator mode support (headless vs default), iframe-based routing architecture, and white-label cleanup for B2B readiness.
- **Ran Laravel 10 + Pest V2 upgrade** — major framework upgrade across the core API with simultaneous test framework upgrade. Introduced Laravel Pint as the canonical auto-formatter.
- **Prototyped AI-powered SEO content generation** — early integration of the ChatGPT API to generate product descriptions in bulk for operator stores.

Technologies: PHP, Laravel, Vue, TypeScript, Node.js, Tailwind CSS, PostgreSQL, MySQL, Redis, Elasticsearch, MQTT, Stripe, GCP, Docker, Kubernetes, Datadog, Sentry, Mailgun, OpenAPI

### Software Engineer — Teemill

**December 2018 – January 2022 | Isle of Wight, UK**

Grew from a platform engineer into someone owning major systems end-to-end. Built and shipped core infrastructure and product features across the Teemill platform — a print-on-demand operating system serving thousands of operator stores.

Key achievements:

- **Built and launched Teemail, an internal email marketing platform, end-to-end** — from domain warmup and deliverability infrastructure (DMARC/DNS setup, bounce/complaint webhook handlers, Mailgun integration) through to a drag-and-drop email builder, campaign scheduling, mailing list management, subscriber origin tracking, and campaign statistics with revenue attribution via UTM-tracked conversions. The platform now sends close to 300 million emails per year. Public launch in October 2021.
- **Built a real-time internal chat platform** — MQTT-based messaging with encrypted broadcasting, file/image uploads, channel management, task assignment from messages, toxic word filtering with async moderation, sentiment analysis with a KPI dashboard, private VIP channels, team mentions, and jump-to-message linking.
- **Built and owned a Node.js image processing microservice** — image transformation (resize, crop, format conversion) backed by GCP/GCS and Redis. Designed an encrypted URL signing scheme with batch encryption tooling, implemented CORS domain restrictions, and led a phased platform-wide migration routing all images through the service. 1,400+ line refactor PR.
- **Led incremental major version upgrades** — Laravel 7 to 8, PHP 7.4 to 8.0. Introduced PHPStan for static analysis and migrated the test suite from PHPUnit to Pest.
- **Implemented end-to-end encrypted MQTT broadcasting** — baked encryption into the real-time messaging layer at the infrastructure level across both backend and frontend simultaneously.

Technologies: PHP, Laravel, Vue, Node.js, JavaScript, GCP, GCS, Redis, MQTT, Docker, Kubernetes, Mailgun, PHPStan, Pest

### Software Engineering Trainee — Teemill

**November 2017 – November 2018 | Isle of Wight, UK**

First engineering role after a self-taught pivot from geology to software. Shipped production features from the first months — building application and storefront features for Rapanui Clothing and the Teemill Trading Partnerships platform.

Key achievements:

- **Built a multi-tenant blog platform** — allowing client stores (Rapanui, BBC Earth Clothing, MCS Clothing, and others) to publish front-facing blog content for their customers
- **Worked extensively in factory code** — bugfixing, refactoring, and Kaizen improvement projects across the manufacturing software
- **Integrated early web push notifications** — allowing timely notifications to store owners across the platform
- **Built early email marketing integration with Sendy** — an initial implementation that laid the groundwork for the later Teemail platform

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
****
### [dndplaybook.com](https://dndplaybook.com)

A D&D companion app and Charles's primary side project — self-funding and actively developed. Built with Laravel, Inertia.js, Vue 3, and TypeScript with server-side rendering. Users value it for its AI integrations. Built as a playground for exploring agentic AI, text-to-image generation, and emerging AI tooling. Showcases product thinking applied outside of work: identifying a niche, building for real users, and iterating based on usage.

### charlesrbowen.dev

This site. A chat-based CV built with Laravel 13, Vue 3, and Inertia.js v3. Visitors interact with an AI agent rather than reading a static page — demonstrating that Charles builds things differently when given the freedom to.

### dmarc-record-builder

An open-source PHP package for building DMARC records programmatically. Born from hands-on experience building email deliverability infrastructure at Teemill. Available on GitHub.

### Hardware & IoT

Built a custom gaming table with LED light strips controllable via the Philips Hue API, driven by an Arduino controller with custom lighting functions. Various Raspberry Pi and IoT automation projects over the years — the same curiosity that led from geology to software.

## Working Style

Charles is initiative-driven — he proposes solutions proactively rather than waiting for detailed requirements. He's strongest when given a problem and the autonomy to figure out the path from zero to one.

He's a big believer in iterative development: find a working solution, ship it, then improve from there. This applies equally to solo work and collaborative product development.

A lot of his satisfaction comes from building products alongside other people — working cross-functionally with design, marketing, finance, and operations to solve real problems and seeing those products work for users. He's not just technically capable; he genuinely enjoys the human side of building software.

## Contact

- **Email:** charlesrbowen@gmail.com
- **LinkedIn:** [linkedin.com/in/charles-r-bowen](https://linkedin.com/in/charles-r-bowen)
- **GitHub:** [github.com/cbowofrivia](https://github.com/cbowofrivia)
- **Location:** Near Bristol, UK
- **Work arrangement:** Open to remote, hybrid, or onsite — happy to discuss
