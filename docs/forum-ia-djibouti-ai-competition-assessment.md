# Kirada AI — Forum IA Djibouti Technical and Product Assessment

**Review date:** 2026-08-06

**Repository:** Kirada

**Reviewed branch:** `master`

**Reviewed commit:** `9e564d0`

**Review mode:** Read-only source assessment; no application code, dependencies, database records, or commits were changed.

# 1. Executive Summary

Kirada is a substantial Laravel property-management application, not a thin competition prototype. The code implements real portfolio, leasing, billing, payment-proof, maintenance, contract, document, team, notification, and tenant-portal workflows. Its best competition advantage is this existing domain foundation.

However:

- Kirada currently contains no operational AI integration.
- The README and legal/deployment documentation still describe an AI assistant that was explicitly removed from the database schema. This is documentation drift, not unfinished working AI.
- Several existing security, tenancy, messaging, and reporting defects must be fixed before an AI feature is allowed to create application records.
- A general chatbot, lease generator, voice assistant, or autonomous financial copilot would be poorly supported and unsafe within a short timeline.
- The strongest achievable competition MVP is a multilingual maintenance-intake copilot that converts a tenant’s natural-language description into a structured, translated, reviewable maintenance request and writes nothing until the tenant explicitly confirms it.

The three coherent AI capabilities should be:

1. Language detection and multilingual understanding.
2. Structured maintenance extraction and conservative urgency/safety classification.
3. A translated preview followed by confirmed creation through Kirada’s existing maintenance service.

The MVP should remain in Laravel. It does not require Python, RAG, embeddings, vector storage, or fine-tuning.

Current verification:

- Repository remained clean on `master` at `9e564d0`.
- PHPUnit: 312 tests, 303 passed, 9 skipped, 986 assertions.
- Pint: passed.
- No browser or provider integration acceptance was performed.
- The composite `composer test` command did not complete because `optimize:clear` attempted to access the unavailable MySQL-backed cache. Standalone PHPStan also exited without useful diagnostics in this environment, so the static-analysis gate is not claimed as passing. The repository documents this cached-configuration hazard in [README.md](../README.md#L45) and guards PHPUnit against a real-database reset in [tests/TestCase.php](../tests/TestCase.php#L10).

# 2. What Kirada Currently Implements

## Product definition

Kirada is a subscription-gated, multi-role property-management SaaS application. It is centered on landlords and their portfolios, with separate tenant and maintenance-provider portals and a global Filament administration panel.

The product is more accurately described by its user guide than by its README:

- Landlords manage properties, buildings, units, tenants, leases, rent records, contracts, teams, and maintenance.
- Tenants access their own invoices, contracts, documents, payment-proof submission, maintenance requests, and messaging.
- Maintenance professionals publish profiles, join landlord networks, receive work, submit quotes, and update work orders.
- Kirada does not currently move rent money. Tenants pay landlords externally and submit evidence for confirmation. This boundary is documented in [docs/user-guide.md](user-guide.md#L9).

## Current roles

The implemented roles are:

- `admin`: global Filament administration and selected application-wide access.
- `landlord`: portfolio owner.
- `landlord-admin`: operational administrator for one landlord account.
- `property-manager`: portfolio, leasing, maintenance, messaging, and document operations.
- `accountant`: invoices, payment confirmation, documents, and reports; portfolio records are intended to be read-only.
- `viewer`: read-only portfolio access.
- `tenant`: own rent, documents, maintenance, and messaging.
- `maintenance`: approved-provider work and messaging.

The permission assignments are explicit in [RolePermissionSeeder.php](../database/seeders/RolePermissionSeeder.php#L21), including the landlord-team roles at lines 102–142.

## Main end-to-end workflows

1. **Landlord onboarding and portfolio setup:** registration, email verification, trial/subscription, payout instructions, properties, buildings, units, tenants, and leases.
2. **Tenant invitation:** expiring email and optional WhatsApp invitation, followed by account creation or secure account linking.
3. **Rent invoicing:** manual or scheduled invoice creation, overdue marking, reminders, line items, and late fees. Schedules are defined in [routes/console.php](../routes/console.php#L13).
4. **Manual payment proof:** external tenant payment, reference and private proof submission, pending state, landlord review, confirmation/rejection, invoice synchronization, and receipt delivery. See [RentPayments/Submit.php](../app/Livewire/RentPayments/Submit.php#L44) and [RentPaymentService.php](../app/Services/RentPaymentService.php#L69).
5. **Lease contracts and electronic signatures:** French commercial-lease draft, signer records, expiring public links, signature evidence, completion, and private PDF archival. See [ContractService.php](../app/Services/ContractService.php#L22).
6. **Maintenance:** request creation, structured issue fields, private photos, comments, provider network, assignment, state changes, quotes, completion, and reviews. Current input validation is in [MaintenanceRequests/Create.php](../app/Livewire/MaintenanceRequests/Create.php#L72).
7. **Documents:** private files connected to tenants, leases, invoices, and payments, with linked-entity consistency checks in [DocumentService.php](../app/Services/DocumentService.php#L15).
8. **Messaging:** persisted landlord, tenant, and maintenance conversations. This is basic in-app messaging, not an omnichannel inbox.
9. **Notifications:** email, database notifications, SMS scaffolding, jobs, and signed BWA WhatsApp delivery.
10. **Reports and dashboards:** deterministic portfolio, collections, occupancy, renewal, and maintenance summaries.

## Existing AI-suitable data

Subject to authorization and minimization, the following data can support maintenance intake:

- Authorized tenant, lease, property, and unit context selected by the server.
- Maintenance narrative, category, location, access preference, priority, status, timestamps, and comments.
- Maintenance attachment metadata; images should remain excluded from the first MVP.
- Historical quotes, provider trades, and reviews for possible later workflows.
- User and landlord language preferences.
- Existing maintenance state-transition rules.
- Existing audit actor and portfolio context.

The model should not receive national IDs, identity documents, payment proofs, full contracts, payout details, unrelated messages, or unrelated portfolio records for maintenance triage.

## Important missing data

Kirada does not currently have:

- Labeled maintenance-triage examples or validated urgency outcomes.
- Failure diagnoses, root-cause classifications, service-level targets, appointments, or escalation rules.
- Reliable actual repair costs, vendor bills, purchase orders, or a general ledger.
- Document text extraction or OCR output.
- A verified Djiboutian legal corpus.
- AI consent, retention, feedback, prompt, token, latency, or cost records.
- Speech recordings, transcripts, embeddings, or a vector index.
- A trustworthy consolidated multi-currency reporting model.

## Market specificity

Kirada is mixed:

- It is Djibouti-centered in its default/demo configuration: DJF, `+253`, Djibouti properties, and local payment methods.
- It also seeds Ethiopia, Somalia, the United States, Saudi Arabia, UAE, Qatar, and Egypt with their currencies in [CountryCurrencySeeder.php](../database/seeders/CountryCurrencySeeder.php#L13).
- The schema is broadly regional/global, but contracts and payment assumptions are not globally normalized.
- The only contract template is a French `bail_commercial` template, identified in [ContractTemplateService.php](../app/Services/ContractTemplateService.php#L23).

The correct positioning is “built first for Djibouti and similar multilingual rental markets,” not “globally compliant.”

# 3. Architecture Assessment

## Technical architecture

Kirada is a conventional Laravel monolith:

- PHP constraint: `^8.3`, although the current lock/CI effectively requires PHP 8.4.1 or later.
- Inspected runtime: PHP 8.4.22 and Laravel 13.24.0.
- CI matrix: PHP 8.4 and 8.5, Node 22, asset build, PHPStan, and PHPUnit in [.github/workflows/tests.yml](../.github/workflows/tests.yml#L20).
- Backend: Laravel 13, Eloquent, Fortify, Cashier, and Spatie Permission.
- UI: server-rendered Blade and Livewire class components; Volt is used mainly for starter-kit/auth/settings pages.
- UI libraries: Flux, Blaze, Tailwind CSS 4, and TipTap.
- Administration: Filament 5.
- PDF generation: Dompdf.
- Frontend build: Vite 8; no React, Vue, or Inertia application layer.
- Production database assumption: MySQL 8 or MariaDB; tests use SQLite in-memory.
- Sessions, cache, and queues default to database-backed drivers.
- Storage: local private disk, public disk, and optional S3 configuration.
- Deployment assumes Nginx/PHP-FPM, Supervisor workers, cron, and eventually Hetzner/CloudPanel; see [deployment-checklist.md](deployment-checklist.md#L5).

The source contains approximately 36 models, 56 migrations, 48 Livewire classes, 28 service files, and 55 test files. It is large enough to justify a bounded AI domain rather than provider calls inside Livewire components.

## Tenancy model

Kirada uses a shared database with row-level portfolio isolation:

- Portfolio-owned records carry `landlord_id`, directly or through a related property.
- A landlord team member resolves to the owner through `landlordAccountId()`.
- Tenants can theoretically have multiple tenant profiles with different landlords.
- Admins operate globally.

The account helpers are in [User.php](../app/Models/User.php#L140), including the multi-profile tenant relationship at lines 193–207.

There is no tenancy package or universal global scope. Every query, policy, service, and component must therefore apply the correct landlord or tenant constraint. Existing messaging defects show that this is not guaranteed today.

## Authorization model

Authorization is split across:

- Route role middleware.
- Spatie permissions.
- Laravel policies.
- Livewire `authorize()` calls.
- Manual service-level domain checks.
- Subscription middleware.

This creates defense in depth but also inconsistency. Some services mutate a supplied model without receiving or authorizing an actor. An AI executor must not call arbitrary service methods on the assumption that a route already authorized the action.

## Business logic and reusable foundations

Most significant behavior lives in service classes such as `RentInvoiceService`, `RentPaymentService`, `MaintenanceRequestService`, `ContractService`, `DocumentService`, `TenantInvitationService`, and `LandlordTeamService`.

Useful AI foundations include:

- `User::landlordAccountId()` and `belongsToLandlordAccount()`.
- `MaintenanceRequestPolicy`.
- `MaintenanceRequestService::normalizeRequestEntities()`.
- Deterministic maintenance state transitions.
- Private maintenance attachments.
- `Locales`.
- Notifications and queue infrastructure.
- Audit actor and portfolio fields.
- Laravel’s HTTP client.
- Existing Livewire and feature-test patterns.

## Architectural obstacles

1. No universal tenant/portfolio scope.
2. Services are not uniformly actor-aware or internally authorized.
3. Validation is split between UI and service boundaries.
4. Statuses and categories are largely strings rather than typed enums.
5. Multi-tenant-profile support is declared but many workflows use `first()`.
6. Some notifications occur inside service operations instead of reliably after commit.
7. Audit logging deliberately fails open.
8. Reports contain incorrect financial aggregation.
9. Sensitive tenant identity files are stored publicly.
10. Deployment and legal documentation has drifted from the source.

A new AI domain should live in `app/AI`, with only a confirmed, typed domain action crossing into existing services. Provider calls must never receive database or arbitrary tool access.

# 4. Feature Maturity Matrix

“Production-ready” here means coherent code and tests, not an independent security audit or proven production load. Given the identified vulnerabilities, the application as a whole is not presently production-ready.

| Feature | Status | Assessment |
|---|---|---|
| Authentication, verification, 2FA, passkeys | Implemented; relatively mature | Strong foundation and tests. Production admin seeding still permits a known default password. |
| Property/building/unit management | Implemented; relatively mature | Scoped CRUD and occupancy relationships are useful AI context. |
| Tenant records | Implemented but unsafe in one area | Identity documents are stored publicly in [Tenants/Create.php](../app/Livewire/Tenants/Create.php#L56). |
| Tenant invitations | Implemented; relatively mature | Expiring links, email/WhatsApp delivery, consent handling, and security tests exist. |
| Leases | Implemented; mature basic workflow | Terms, deposits, billing configuration, renewals, and invoice linkage exist; no leasing CRM. |
| Rent invoices | Implemented; relatively mature | Transactional duplicate checks, schedules, line items, late fees, PDFs, and references exist. Number generation may still race. |
| Manual payment proofs | Implemented; relatively mature | Pending/confirm/reject, private proof, payout accounts, idempotent webhook support, overpayment checks, and receipts exist. Landlord edit MIME checks are incomplete. |
| Automated rent collection | Scaffolded/future | Webhooks create pending records only; Kirada does not settle or reconcile funds. |
| Contracts/e-signatures | Implemented but unsafe and legally fragile | Signature evidence and private PDFs exist. Editable HTML is unsanitized, and only an unverified French commercial template exists. |
| Maintenance intake/workflow | Implemented; strongest AI foundation | Structured requests, photos, comments, assignment, providers, quotes, reviews, and notifications exist. Team authorization is inconsistent. |
| Maintenance scheduling/SLA/cost accounting | Partial or missing | No reliable appointments, deadlines, SLA escalation, emergency response, actual vendor bills, or cost ledger. |
| Documents | Implemented; mostly mature | Main documents are private and policy-protected. Tenant ID uploads bypass this design. No OCR. |
| Landlord teams | Implemented; recent | Invitations, roles, permissions, lifecycle, and WhatsApp delivery exist. |
| Messaging | Implemented but fragile | Basic conversations work, but tenant selection, team scoping, and permission behavior contain serious defects. |
| Notifications/WhatsApp | Implemented with external dependency | Signed outbound BWA requests, idempotency, delivery events, jobs, and template-language mapping exist. No free-text inbound intake. |
| Reports | Implemented but unreliable | Pending/rejected payments are included in collections and the period selector is unused in [Reports/Index.php](../app/Livewire/Reports/Index.php#L27). |
| Global search | Implemented | Permission-scoped deterministic `LIKE` search in [GlobalSearchService.php](../app/Services/GlobalSearchService.php#L23); not semantic search. |
| Localization | Implemented unevenly | Five selectable languages exist. French has broader coverage and the only completeness test. User-generated content is not translated. |
| PWA/offline | Scaffolded | Manifest and fallback exist; domain workflows are not proven offline-capable. |
| Filament administration | Implemented | Global-admin resources and actions exist. |
| AI | Missing | Historical tables were explicitly dropped; no gateway, route, model, component, configuration, or working assistant remains. |

# 5. Existing AI or Automation Capabilities

## AI

There is no operational AI code.

- The README claims an “AI assistant” at [README.md](../README.md#L13).
- A historical migration created `ai_conversations` and `ai_messages` in [2026_06_27_240000_create_ai_assistant_tables.php](../database/migrations/2026_06_27_240000_create_ai_assistant_tables.php#L11).
- A later migration explicitly says the feature was removed and drops those tables in [2026_07_02_000000_drop_ai_assistant_tables.php](../database/migrations/2026_07_02_000000_drop_ai_assistant_tables.php#L9).
- There is no OpenAI, Anthropic, Gemini, embedding, vector, OCR, speech, or LLM client in the application or dependency manifests.
- The deployment checklist still describes an OpenAI key and read-only chatbot in [deployment-checklist.md](deployment-checklist.md#L234).
- Terms/privacy text claims an AI assistant exists and that its data is not shared with third parties in [terms-of-service.md](terms-of-service.md#L87) and lines 256–263. Those statements are stale and would be inaccurate if an external provider processes prompts.

## Existing non-AI automation

Kirada has deterministic automation for invoice generation, reminders, late fees, invoice synchronization, overpayment protection, webhook verification, duplicate detection, contract completion, PDF archival, delivery jobs, invitation expiry, dashboards, and search. These should remain deterministic and should not be marketed as AI.

# 6. Competition Opportunity Matrix

Effort assumes one Laravel developer and completion of relevant prerequisite fixes. It excludes legal review and commercial provider procurement.

| Direction | Foundation and missing work | Complexity / safety | Demo and local value | Effort | Decision |
|---|---|---|---|---:|---|
| General Kirada AI chat | Broad domain data; missing safe query tools, citations, intent limits, and evaluation | High tenancy and hallucination risk | Easy to show but vague and weakly differentiated | 8–15 days | Reject for MVP |
| AI lease drafting | Lease data, one template, e-sign and PDFs; missing verified law, clause library, sanitizer, and legal review | Very high legal/XSS risk | Visually impressive but unsafe | 10–18 days plus legal review | Reject |
| Document/lease extraction | Private documents and uploads; missing OCR, schemas, malware controls, review UI, and labeled examples | Medium-high privacy risk | Useful for paper-heavy workflows | 7–12 days | Post-MVP |
| Maintenance triage | Strong request, tenant/lease, category, priority, attachment, provider, and notification foundation | Medium and manageable because output is a proposal | Strong end-to-end multilingual value | 5–8 days after prerequisites | MVP |
| Rent/payment insights | Invoices, payments, reminders, dashboards; reports need correction | Medium-high financial interpretation risk | Useful but current metrics are not trustworthy enough | 5–8 days | Post-MVP |
| Multilingual communication | Five UI locales, messaging, notifications; missing safe translation storage and messaging fixes | Medium privacy/mistranslation risk | High usefulness | 4–7 days standalone | Include only inside maintenance MVP |
| Voice commands | No current foundation | High complexity and privacy risk | High stage appeal, little code support | 7–12 days | Reject for MVP |
| Portfolio search | Deterministic scoped search; missing typed natural-language filters and citations | Medium tenancy risk, low write risk | Useful but less compelling than a closed workflow | 3–6 days | Post-MVP |
| Invoice/payment automation | Mature state machines; missing OCR, reconciliation, review, and stronger financial audit | High financial and duplicate risk | Strong value but unsafe as a first workflow | 8–14 days | Post-MVP; never auto-confirm |
| Landlord business copilot | Broad product data; missing correct accounting/reporting and safe cross-domain tools | Very high and vague | Attractive branding but not a credible bounded MVP | 12–25 days | Reject as MVP |

# 7. Recommended Competition MVP

## Product concept

**Kirada AI Maintenance Intake — multilingual, structured, and human-confirmed.**

## User problem and target

Tenants often describe maintenance issues in the language they are comfortable using. Conventional forms require them to understand maintenance categories, priority levels, access fields, and property-management vocabulary. Managers then interpret, translate, normalize, and re-enter the report.

The primary user is an authenticated tenant. The secondary user is the landlord or property manager reviewing the resulting ticket.

## Why this matters and why AI is appropriate

Kirada’s code already targets a multilingual, locally configured context: French, Arabic, Somali, English, and Amharic UI options; DJF; `+253`; D-Money/Waafi; and Djibouti demo properties.

AI is justified only at the unstructured-language boundary:

- Detect language.
- Extract issue, location, access instructions, and facts.
- Map varied descriptions to constrained categories.
- Produce a concise manager-facing translation.
- Identify uncertainty and possible urgency signals.

CRUD should continue to enforce final fields, authorization, validation, and state changes.

## Three MVP capabilities

1. Multilingual natural-language intake in French, Arabic, Somali, or English.
2. Schema-constrained extraction into title, category, normalized description, location, access preference, suggested priority, safety flags, confidence, and uncertainties.
3. Translated preview and confirmed proposal execution through the existing maintenance workflow.

Do not add a general chat screen.

## Existing code to reuse

- Tenant, lease, property, and unit context resolution.
- Maintenance category and validation rules.
- `MaintenanceRequestPolicy`.
- Cross-entity checks in [MaintenanceRequestService.php](../app/Services/MaintenanceRequestService.php#L320).
- Maintenance notifications and private attachments.
- `Locales`.
- Existing request/detail screens.
- Audit actor and portfolio fields.
- Queue infrastructure.

## New code required

- Provider-neutral AI gateway and one adapter.
- Structured response DTO, schema, and maintenance prompt.
- Data minimizer/redactor and deterministic safety rules.
- AI request and proposed-action records.
- Proposal policy, preview UI, and confirmation action.
- Transactional execution with idempotency.
- AI limiter, feature flags, and multilingual evaluation fixtures.
- Explicit AI execution audit evidence.

## Data rules

Send only the tenant-entered text, interface-language hint, allowed categories/priorities, and possibly a non-identifying location hint. Do not send names, phone numbers, email, national ID, payment information, contracts, unrelated messages, or portfolio lists.

Trusted property, unit, lease, tenant, and landlord IDs must be added by the server after model output. The model must never choose record IDs.

## Synthetic data and APIs

The demonstration portfolio, identities, narratives, expected outputs, and evaluation cases should be synthetic.

Only one external API is required: a multilingual model provider supporting schema-constrained JSON output. No separate OCR, translation, vector, or speech API is necessary.

## Incorrect response behavior

- Preserve original text.
- Show a low-confidence or invalid-result state.
- Return to the existing manual form with safe prefilled values.
- Never create a request automatically.
- Allow every proposed field to be corrected.
- Display the source text alongside any translation.

## Review and confirmation

The preview must show original text, detected language, title, summary/translation, category, priority, location, access details, safety warning, uncertainties, and confidence. Only confirmation may create the request, and confirmation must reauthorize, revalidate, lock the proposal, and enforce idempotency.

## What not to promise

Do not promise diagnosis, emergency response, automatic assignment, guaranteed urgency, WhatsApp intake, voice, offline AI, legal compliance, autonomous writes, repair-cost estimates, predictive maintenance, every dialect, or production readiness.

# 8. Exact Demo Scenario

Use a synthetic Djibouti portfolio and a tenant with an active lease.

1. Tenant opens **Report a problem with Kirada AI** in the web portal.
2. Kirada resolves the authorized lease and unit from the authenticated user; the model never chooses these IDs.
3. Tenant enters in French: “Il y a une forte odeur de gaz près de la cuisinière depuis ce matin. Je suis disponible après 16 h.”
4. Kirada returns language, title, safety category, suggested urgent priority, kitchen location, after-16:00 access preference, suspected-gas flag, confidence, uncertainties, and manager translation.
5. A deterministic rule displays a warning telling the tenant not to wait for Kirada if immediate danger exists and to contact the landlord or appropriate emergency service. It must not invent an emergency number.
6. Show that no maintenance request exists yet.
7. Correct one proposed field manually to demonstrate human control.
8. Press **Confirm request**.
9. The server rechecks policy and active lease, creates one request through the normal maintenance workflow, records the executed proposal, and dispatches the usual notification.
10. Open the landlord view and show the original report, translation, structured fields, safety flag, and actor/prompt/model audit metadata.
11. Repeat confirmation to prove the idempotency key prevents a duplicate.
12. Optionally use one prepared Arabic or Somali case to demonstrate the same schema.

# 9. Safe AI Architecture

```text
Authenticated tenant input
  → trusted lease context resolved by Kirada
  → data minimization and rate limit
  → language detection + maintenance intent
  → schema-constrained provider response
  → JSON/schema validation
  → deterministic maintenance safety rules
  → authorized proposed action
  → editable preview
  → explicit human confirmation
  → policy and business-rule revalidation
  → transaction + proposal row lock + idempotency
  → MaintenanceRequestService
  → durable execution record
  → after-commit notifications
```

## Required components

- **`AiGateway`:** provider-neutral typed interface returning output, provider/model, token use, latency, request ID, and finish reason.
- **Provider adapter:** Laravel HTTP client, five-second connection timeout, about twenty-second response timeout, schema mode, and at most one retry for a pre-response transport failure.
- **`AiIntent`:** only `MaintenanceIntake` in the MVP.
- **DTO/schema:** fixed keys for language, title, normalized description, manager translation, category, priority, location, access fields, safety flags, uncertainties, and confidence.
- **Deterministic safety validation:** code decides which warning is shown; the model does not diagnose emergencies.
- **Proposed action:** typed, portfolio-scoped, expiring, and idempotent; not a maintenance request.
- **Confirmation action:** lock proposal, reauthorize, re-resolve IDs, revalidate, execute transactionally, link target, mark executed, then notify after commit.
- **Durable audit:** proposal state changes inside the same transaction. Existing `audit_events` can supplement this but cannot be the only evidence because the observer deliberately fails open in [PortfolioAuditObserver.php](../app/Observers/PortfolioAuditObserver.php#L67).
- **Redaction/minimization:** no unnecessary identifiers; user input is untrusted data, never prompt instructions; no raw provider payloads in standard logs.
- **Rate limiting:** approximately five AI requests per minute and thirty per day per tenant for the prototype, plus idempotent confirmation.
- **Fallback:** preserve input and open the ordinary maintenance form when disabled, invalid, unavailable, timed out, or rate-limited.
- **Feature flags:** require both general AI and maintenance-intake flags.

The existing authenticated throttle does not reliably cover Livewire updates, as noted in [bootstrap/app.php](../bootstrap/app.php#L50), so AI requires its own limiter.

## Post-MVP components

Generic action registries, general conversations, prompt administration, feedback tables, provider failover, asynchronous intake, vision, OCR, RAG, semantic search, automatic assignment, and advanced cost dashboards can wait.

# 10. Proposed File and Class Structure

```text
app/
  AI/
    Contracts/
      AiGateway.php
    DTOs/
      AiGeneration.php
      MaintenanceIntakeResult.php
    Enums/
      AiIntent.php
      AiRequestStatus.php
      AiProposalStatus.php
    Providers/
      StructuredLlmGateway.php
    Prompts/
      MaintenanceIntakePrompt.php
    Schemas/
      MaintenanceIntakeSchema.php
    Safety/
      AiDataMinimizer.php
      MaintenanceSafetyRules.php
    Actions/
      ProposeMaintenanceRequest.php
      ConfirmMaintenanceRequestProposal.php
  Livewire/
    MaintenanceRequests/
      AiIntake.php
  Models/
    AiRequest.php
    AiProposedAction.php
  Policies/
    AiProposedActionPolicy.php
  Providers/
    AiServiceProvider.php

config/
  ai.php

database/migrations/
  xxxx_create_ai_requests_table.php
  xxxx_create_ai_proposed_actions_table.php

resources/views/livewire/maintenance-requests/
  ai-intake.blade.php

tests/
  Unit/AI/
    MaintenanceIntakeSchemaTest.php
    MaintenanceSafetyRulesTest.php
    AiDataMinimizerTest.php
  Feature/AI/
    MaintenanceIntakeProposalTest.php
    MaintenanceIntakeConfirmationTest.php
    MaintenanceIntakeIsolationTest.php
    MaintenanceIntakeFailureTest.php
  Fixtures/AI/
    maintenance-intake.en.json
    maintenance-intake.fr.json
    maintenance-intake.ar.json
    maintenance-intake.so.json
```

## Responsibilities

- `AiGateway`: provider-neutral generation contract.
- `AiGeneration`: normalized response metadata.
- `MaintenanceIntakeResult`: immutable validated result.
- Enums: closed intent and lifecycle values.
- `StructuredLlmGateway`: authentication, timeout, provider schema request, and normalization.
- Prompt/schema classes: versioned contract and one source of validation truth.
- `AiDataMinimizer`: allowlisted outbound fields.
- `MaintenanceSafetyRules`: deterministic warnings and bounds.
- Proposal/confirmation actions: the only AI-to-domain bridge.
- Models/policy: observability, preview, confirmation, idempotency, execution evidence, and portfolio isolation.
- Livewire component: input, loading, preview, edit, failure, and confirmation UX.
- Fixtures: reviewed multilingual examples stored in source control.

## Existing files likely to change

- [routes/web.php](../routes/web.php#L202): one tenant maintenance-intake route.
- `bootstrap/providers.php`: provider registration.
- `AppServiceProvider.php` or `AiServiceProvider.php`: dedicated limiter.
- [MaintenanceRequestService.php](../app/Services/MaintenanceRequestService.php#L20): transaction/notification and team-authorization consistency.
- `MaintenanceRequest.php` and its detail UI: source/translation metadata if not kept solely on the proposal.
- App sidebar: one contextual entry, not a global “AI” section.
- `config/logging.php`: payload exclusion/redaction.
- `.env.example`: provider, retention, and feature flags.
- Legal/privacy documentation: actual provider and retention behavior.
- Landing page only after the workflow exists.

# 11. Database Changes

## Required: `ai_requests`

Purpose: operational provider request record.

Important fields:

- UUID, `landlord_id`, `actor_id`, optional `tenant_id`.
- Intent, provider, model, prompt version, and source locale.
- Input hash and optional encrypted minimized input.
- Optional encrypted structured output.
- Status, provider request ID, token counts, latency, optional estimated cost.
- Sanitized error code/message, expiry, and timestamps.

Every maintenance request must have a portfolio scope. Raw/minimized text should default to retention of thirty days or less, while non-identifying usage aggregates may be retained longer. Tenant narrative and provider output are sensitive and should be encrypted.

MVP: **Required.**

## Required: `ai_proposed_actions`

Purpose: authoritative preview, confirmation, idempotency, and execution record.

Important fields:

- UUID, `ai_request_id`, `landlord_id`, `actor_id`, and `tenant_id`.
- Action type.
- Encrypted trusted context, proposed payload, and optional edited payload.
- Pending, confirmed, executed, rejected, expired, failed status.
- Unique idempotency key, expiry, confirmation actor/time, execution time.
- Nullable target morph to the created maintenance request.
- Payload hash and sanitized failure code.

Trusted IDs are written by the application, not copied from the model. Keep execution metadata with the work order; purge rejected/expired payload text sooner while preserving hashes, actor, timestamps, action type, and target reference.

MVP: **Required.**

## Optional or unnecessary for MVP

- `ai_feedback`: useful later for corrections and evaluation.
- `ai_conversations`: unnecessary because this is not a chatbot.
- `ai_action_executions`: proposal lifecycle plus normal audit can represent execution.
- `ai_prompt_versions`: keep prompts in Git and store version strings on requests.
- Embedding/vector tables: unnecessary.

# 12. Security, Privacy, and Tenant Isolation

## Existing strengths

Verified accounts, policies, roles, password hardening, 2FA, passkeys, throttling, signed/replay-protected BWA requests, private core document storage, transactional payment/contract locks, encrypted audit payloads, security headers, and trusted-proxy guidance are useful foundations.

## Existing issues requiring correction

### Critical: public identity documents

Tenant ID files use the public disk and direct `/storage` URLs in [Tenants/Create.php](../app/Livewire/Tenants/Create.php#L65) and [Tenant.php](../app/Models/Tenant.php#L80). Move them private behind a policy-authorized controller.

### Critical: stored contract XSS

Editable contract HTML is assembled without server-side sanitization in [Contracts/Show.php](../app/Livewire/Contracts/Show.php#L207) and rendered raw on the public signing page in [contracts/sign.blade.php](../resources/views/livewire/contracts/sign.blade.php#L32).

### Critical: messaging tenant isolation

The tenant selector exposes other tenants under the same landlord in [Messages/Index.php](../app/Livewire/Messages/Index.php#L55). [MessagingService.php](../app/Services/MessagingService.php#L49) checks the landlord but not that the selected tenant is the current tenant’s own profile.

### High: team messaging scope

The team selector uses `$user->id` instead of `$user->landlordAccountId()` at [Messages/Index.php](../app/Livewire/Messages/Index.php#L63).

### High: tenant messaging permission mismatch

Tenants receive `messages.send.own`, while [ConversationPolicy.php](../app/Policies/ConversationPolicy.php#L69) requires `messages.send`.

### High: maintenance team authorization mismatch

The policy recognizes landlord team accounts, while service transition and internal-comment checks compare the request owner directly to the team member ID in [MaintenanceRequestService.php](../app/Services/MaintenanceRequestService.php#L143).

### High: default production administrator password

[AdminUserSeeder.php](../database/seeders/AdminUserSeeder.php#L19) falls back to `ChangeMe123!`; production seeding should fail unless a non-default secret is provided.

### Medium: payment-proof edit validation and reporting

Landlord payment editing lacks the tenant form’s MIME allowlist. Reports count payment statuses that should not be treated as collected.

## AI isolation requirements

- No model-selected table, ID, query, URL, or arbitrary action may reach execution.
- Model output is semantic data only.
- Resolve portfolio IDs from the authenticated actor and active lease.
- Authorize proposal creation and confirmation independently.
- Reauthorize inside the transaction.
- Use row locks, expiry, and unique idempotency keys.
- Escape all rendered fields and treat prompts/results as untrusted.
- Exclude attachments from MVP provider requests.
- Add manual opt-out/fallback.
- Document the model provider as a data processor.
- Apply deletion/retention jobs to encrypted AI content.
- Use synthetic competition accounts and data.

# 13. Multilingual Strategy

Kirada supports English, French, Arabic, Somali, and Amharic locale codes in [Locales.php](../app/Support/Locales.php#L17). Locale resolution uses query, session, cookie, user preference, browser language, and fallback in [LocaleMiddleware.php](../app/Http/Middleware/LocaleMiddleware.php#L16). Arabic RTL behavior is covered in [LanguageSwitcherTest.php](../tests/Feature/LanguageSwitcherTest.php#L41).

Actual maturity is uneven:

- `fr.json` is approximately 1,877 lines.
- Arabic, Somali, and Amharic JSON files are approximately 785 lines each.
- Only French has repository-wide literal-key coverage in [FrenchTranslationCompletenessTest.php](../tests/Feature/FrenchTranslationCompletenessTest.php#L53).
- Translation quality in Arabic, Somali, and Amharic is not proven.
- User-generated content is not translated.
- PDFs default to French.
- Outbound messages use the landlord account’s language.
- WhatsApp language changes require approved Meta templates.

The AI MVP should explicitly evaluate French, Arabic, Somali, and English. Preserve original text, store source and target locales separately, display translation alongside source, allow language correction, render Arabic content RTL, expose translation failures, and use at least ten human-reviewed cases per language.

The first demo must use the web portal. Current WhatsApp integration is outbound/template delivery, not inbound free-text maintenance intake.

# 14. Testing Strategy

## Current coverage

The suite meaningfully covers authentication, billing, payments, contracts, invitations, maintenance, notifications, BWA integration, roles, audit, French localization, dashboards, and search. Maintenance creation with private photos and safe tenant transitions is covered in [MvpSmokeTest.php](../tests/Feature/MvpSmokeTest.php#L204).

Weaknesses:

- Traditional unit tests are effectively absent.
- No AI tests exist.
- No Arabic, Somali, or Amharic completeness/semantic-quality suite exists.
- Messaging does not cover the discovered same-landlord cross-tenant exploit.
- Reports tests access, not calculation correctness.
- No contract XSS or tenant-ID privacy regression exists.
- No current real-provider suite exists.
- Static analysis was not reproduced successfully during this review.

## Required unit tests

- Valid response-to-DTO mapping.
- Rejection of invalid category, priority, language, confidence, extra keys, and oversized values.
- PII minimization/redaction.
- Deterministic safety-warning behavior.
- Prompt version and input hash stability.
- Missing provider usage fields.
- Unicode, combining characters, and Arabic text preservation.

## Required feature and policy tests

- Proposal only for the actor’s active lease.
- No maintenance row before confirmation.
- Server-derived record IDs.
- Cross-landlord and same-landlord cross-tenant denial.
- Active/inactive team membership and every relevant role.
- Confirmation reauthorization and validation.
- Proposal editing, expiry, rejection, and failure.
- Concurrent/duplicate confirmation idempotency.
- Transaction rollback consistency.
- Durable audit/target linkage.
- After-commit notifications only.
- Disabled, unconfigured, timeout, rate-limit, and invalid-output fallback.
- Multiple tenant profiles and soft-deleted records.

## Malicious-input tests

Include prompt-injection instructions, SQL/PHP/shell/HTML, arbitrary model IDs, unexpected actions, oversized input, base64, bidi controls, system-prompt requests, and other-tenant information. Expected behavior is schema rejection or harmless text preservation, never execution.

## Multilingual cases

For every supported language test plumbing, electricity, lock, AC, pests, ambiguity, low urgency, possible gas/fire/electrical danger, access permissions, and mixed-language text. Expected translation wording may vary, but meaning, category, safety flag, and priority bounds must be reviewed.

## Mock versus real provider

Mock the gateway, clock, request IDs, network failures, latency, and token metadata. Do not mock policies, transactions, locks, idempotency, tenant relationships, existing maintenance services, or audit persistence.

A manually triggered provider suite should use synthetic text, an explicit environment flag, one or two cases per language, strict spending limits, schema/latency assertions, and no business-record creation.

# 15. Implementation Phases

| Phase | Goal and scope | Tests/completion criteria | Dependencies and risk | Effort |
|---|---|---|---|---:|
| Phase 0 — prerequisite fixes | Private tenant IDs; sanitized contract HTML; messaging isolation/team/permission fixes; maintenance team authorization; non-default admin secret; proof MIME and confirmed-payment reporting fixes | Regression tests, PHPUnit, Pint, restored static-analysis gate | Avoid unrelated redesign; defects may expose more assumptions | 3–4 days |
| Phase 1 — AI foundation | Gateway, adapter, config, DTO/schema, prompt, minimizer, enums, request/proposal models, policy, feature flag, limiter | Schema/redaction units; invalid output never becomes a proposal | Provider terms and schema differences | 2–3 days |
| Phase 2 — end-to-end workflow | Tenant input, trusted lease context, persisted proposal, editable preview, confirmed transaction | No-write-before-confirmation, isolation, idempotency, expiry, rollback, audit, notification tests | Existing profile/team inconsistencies must be fixed | 3–4 days |
| Phase 3 — multilingual and UX | Four-language evaluation, source/translation display, Arabic RTL, low-confidence fallback, safety banner | At least 40 reviewed fixtures and human language review | Dialect and translation quality | 2–3 days |
| Phase 4 — demo hardening | Synthetic demo, feature check, timeout mode, cost/latency display, mobile/browser run, deployment and rehearsal | Real-provider smoke, network failure, clean install, queue/scheduler, recorded rehearsal | Venue internet, secrets, provider availability | 2–3 days |
| Phase 5 — optional post-competition | Vision, inbound WhatsApp, OCR, portfolio tools, feedback, broader metrics | Separate safety/evaluation gate for each | Scope and external approvals | 5+ days each |

A narrow prototype is plausible in approximately 8–10 focused days if only path-critical Phase 0 fixes are included. A defensible hardened submission is closer to 12–17 developer days.

# 16. Landing Page Recommendations

The visible landing page currently avoids claiming AI. Its hero says “Smart rent management” and accurately lists the broad product at [welcome.blade.php](../resources/views/welcome.blade.php#L207). Its honest distinction between Stripe software subscriptions and external tenant rent payments should remain.

Do not rename the whole product around AI. Add one maintenance-specific section only after the workflow works.

## Positioning

**Kirada is the rental-management platform. Kirada AI removes the language and form barrier from maintenance reporting.**

## Headline

> Report rental problems in any language. Kirada turns them into clear, reviewable work orders.

## Supporting paragraph

> Tenants describe an issue in French, Arabic, Somali, or English. Kirada detects the language, extracts the important facts, flags possible urgent risks, and prepares a translated maintenance request for human confirmation—without taking action on its own.

## Call to action

> Try AI maintenance intake

Do not use “Ask Kirada anything.”

## How it works

1. **Describe the problem naturally:** write in French, Arabic, Somali, or English.
2. **Review Kirada’s interpretation:** check category, priority, location, translation, and safety warning.
3. **Confirm and track:** Kirada creates the work order only after confirmation.

## Demonstrable claims

After implementation and verification, Kirada may claim:

- Four explicitly evaluated input languages.
- Human confirmation for every AI-generated work order.
- Zero database writes before confirmation.
- Duplicate confirmation creates no duplicate ticket.
- Every execution records actor, prompt version, model, target, latency, and token usage.
- A published synthetic evaluation set.
- Measured form-interaction or completion-time results from the demo.

## Unsupported claims

Do not claim universal dialect support, diagnosis, guaranteed emergency detection, autonomous dispatch, Djiboutian legal compliance, no third-party processing, zero provider retention, WhatsApp input, offline AI, production readiness, fine-tuning on local housing data, or unmeasured cost/time savings.

# 17. Competition Pitch

## Exact live-demo story

A tenant in a synthetic Djibouti apartment enters a possible gas issue in French. Kirada detects French, extracts a safety-category request, suggests urgent priority, captures kitchen and after-16:00 access information, creates a manager translation, and displays a deterministic warning. No ticket exists yet.

The tenant corrects one field and confirms. Kirada rechecks the active lease and authorization, creates exactly one work order, sends the normal landlord notification, and records an audit trail. Repeating confirmation does not duplicate the ticket. A prepared Arabic or Somali case shows the same schema.

## 30-second pitch

> Kirada already manages properties, tenants, rent records, contracts, and maintenance. Kirada AI solves one concrete problem: tenants should not need to understand property-management forms or work in the manager’s language to report an issue. A tenant describes a problem in French, Arabic, Somali, or English. Kirada extracts the facts, translates the report, flags possible urgent risks, and prepares a work order. Nothing is created until the tenant reviews and confirms it. The result is a practical multilingual AI workflow built into a real rental platform—not a decorative chatbot.

## Two-minute pitch outline

**0:00–0:20 — Problem:** unstructured maintenance descriptions, language differences, and form terminology delay complete reporting.

**0:20–0:40 — Foundation:** authenticated tenants, leases, properties, maintenance categories, providers, notifications, and audits already exist.

**0:40–1:20 — Demo:** enter French issue; show extraction, translation, safety warning, confidence, and editable fields; prove no ticket exists; confirm and show work order/audit.

**1:20–1:40 — Responsible AI:** no arbitrary tools, no unrestricted writes, server-controlled IDs, schema validation, dual authorization, transaction, idempotency, and manual fallback.

**1:40–2:00 — Impact/path:** publish multilingual evaluation results, measure completion and correction rates, pilot maintenance first, then consider inbound WhatsApp or document extraction only after evidence and privacy review.

# 18. Risks and Limitations

## Top five technical risks

1. Row-level tenancy mistakes without a global scope.
2. Existing public-ID, contract-XSS, messaging, and default-admin vulnerabilities.
3. Fragmented route, policy, component, and service authorization.
4. Unknown multilingual and urgency reliability due to no labeled dataset.
5. Provider availability, latency, cost, retention, and deployment reliability.

## Top five product risks

1. Expanding into a vague landlord copilot.
2. Creating false emergency-response expectations.
3. Assuming all languages and dialects perform equally.
4. Claiming user impact before collecting evidence.
5. Overstating legal, financial, or autonomous capability.

## Additional limitations

- Vision should not be included merely because maintenance photos exist.
- WhatsApp is outbound/template-oriented, not inbound free-text intake.
- General documents lack OCR and extracted text.
- Financial reports require correction before AI summaries.
- The commercial-lease template has no verified legal-source record in the repository.
- Current auditing does not comprehensively cover messages, comments, signatures, quotes, reviews, and notification deliveries.
- Automated tests do not replace mobile, browser, provider, deployment, or human-language acceptance.

# 19. Final Verdict

1. **Is the existing Kirada codebase suitable for this competition?**

   Yes, conditionally. It has strong domain workflows, but security/tenancy defects and stale AI claims must be addressed.

2. **What is the strongest supported AI idea?**

   Multilingual maintenance intake with structured triage, manager translation, deterministic safety warnings, and human-confirmed creation.

3. **What is the smallest convincing MVP?**

   One authenticated web flow, four evaluated languages, one schema, one editable preview, one confirmed maintenance action, one audit trail, and one safe fallback.

4. **What should be excluded?**

   General chat, lease drafting, legal advice, payment confirmation, predictive finance, voice, OCR, vision, autonomous assignment, inbound WhatsApp, semantic search, and multi-action agents.

5. **Top five technical risks?**

   Tenant isolation, current security defects, fragmented authorization, multilingual reliability, and provider/deployment reliability.

6. **Top five product risks?**

   Scope dilution, emergency overclaiming, unequal language quality, lack of user evidence, and legal/financial overstatement.

7. **Can one developer complete it quickly?**

   Yes in roughly 8–10 focused days for a narrow prototype or 12–17 days for a defensible hardened submission. Not credibly in 48 hours.

8. **Evolve Kirada or build separately?**

   Evolve Kirada behind feature flags. A separate prototype would discard its strongest evidence: real authorization, tenancy, leases, workflows, notifications, and audit context.

9. **Does it need RAG?**

   No. The MVP needs constrained extraction, not knowledge retrieval.

10. **Does it need fine-tuning?**

    No. Start with structured output, deterministic validation, and an evaluation set.

11. **Does it need Python?**

    No. Laravel’s HTTP client, validation, queues, transactions, encryption, policies, Livewire, and PHPUnit are sufficient.

12. **What should be built first?**

    Fix path-critical security and tenant-isolation defects, then define the maintenance JSON schema and reviewed multilingual evaluation cases before building the provider adapter or UI.

# 20. Questions Requiring Product-Owner Confirmation

1. What is the exact submission deadline and available developer time?
2. Will the venue have reliable internet, or is a recorded/fixture fallback required?
3. Which model providers are acceptable for cost, retention, and data residency?
4. May synthetic tenant narratives be sent to an external provider during the demo?
5. What retention period should apply to AI input and output?
6. Should users have an explicit AI opt-out beyond the manual form?
7. Are French, Arabic, Somali, and English the committed AI languages, or must Amharic also be evaluated?
8. Which language should the manager receive when the tenant uses another language?
9. Who will human-review Somali and Arabic cases?
10. What wording is approved for possible emergency warnings?
11. Should the first intake be tenant-only, or may landlords enter phone-reported issues?
12. Should the commercial-lease feature be hidden from the demo pending legal and XSS review?
13. Should stale AI claims in README, deployment, translations, terms, and privacy documentation be removed in Phase 0?
14. Is deployment targeted at Hetzner/CloudPanel, current staging, or an isolated demo environment?
15. Which impact metric will be collected: completion time, corrections, field completeness, manager review time, or satisfaction?

READY FOR CHATGPT REVIEW
