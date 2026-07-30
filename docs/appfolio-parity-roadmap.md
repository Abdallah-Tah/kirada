# Kirada product parity roadmap

Last reviewed: July 30, 2026

This roadmap is a delivery contract, not a claim that Kirada already contains
every AppFolio feature. It separates production capabilities from planned work
and from integrations that require a country, regulated provider, or commercial
agreement.

## Kirada foundation available now

| Domain | Current Kirada capability |
| --- | --- |
| Portfolio | Properties, buildings, units, landlord scoping |
| Residents | Tenants, guest invitations, tenant portal |
| Leasing | Leases, contracts, public e-signature |
| Rent | Invoices, line items, late fees, reminders, payment confirmation, receipts |
| Payments | Manual and gateway payment records, landlord payout accounts |
| Maintenance | Requests, attachments, comments, assignments, provider marketplace, quotes, reviews |
| Communication | Conversations, messages, notification channels |
| Documents | Scoped storage, tenant visibility, protected downloads |
| Teams | Landlord team invitations, roles, permissions |
| Security | Verified accounts, throttling, CSP, anti-framing headers, secure production passwords, 2FA, passkeys |
| Accountability | Encrypted, portfolio-scoped Audit Center |
| Reporting | Portfolio, collections, occupancy, maintenance summaries |

## Safe product modules to build

These modules can be built without pretending that Kirada is a bank, insurer,
screening bureau, or licensed accounting product.

### Phase 1 — operating control

1. Inspections with reusable checklists, photos, signatures, and offline drafts.
2. Move-in, move-out, and unit-turn board with tasks, owners, dates, and costs.
3. Vendor records, compliance documents, trade coverage, and work history.
4. Purchase orders, bill approval, and quote-to-invoice workflow.
5. Owner portal with property-scoped reports, documents, and maintenance visibility.
6. Notification preferences, templates, delivery history, and retry controls.

### Phase 2 — leasing growth

1. Prospect and leasing CRM.
2. Listings, availability, lead sources, and marketing attribution.
3. Showing calendar, reminders, and follow-up tasks.
4. Rental applications and configurable approval workflow.
5. Renewals, rent-change notices, and move workflow.

### Phase 3 — accounting

1. Double-entry general ledger and chart of accounts.
2. Bills, vendor credits, owner contributions and distributions.
3. Bank accounts, statement imports, and reconciliation.
4. Budgets, variance reporting, and cash forecasting.
5. Property and owner statements with immutable reporting periods.
6. Approval policies and separation of duties.

Accounting work must use append-only postings, idempotent payment references,
balanced journals, locked periods, and auditable corrections. Financial history
must never be regenerated from mutable current profile data.

### Phase 4 — scale and specialization

1. Inventory and fixed assets.
2. Common-area work, violations, associations, and architectural requests.
3. Affordable housing or student housing workflows only when a target market is selected.
4. Public API, webhooks, integration controls, and data exports.
5. Rules and automation engine with approval and failure queues.
6. AI assistance only with human review, visible fallback, and data-retention controls.

## Provider and jurisdiction decisions required

The following cannot be securely completed as generic checkboxes:

| Capability | Decision required |
| --- | --- |
| Tenant screening | Country, licensed screening provider, consent and adverse-action rules |
| Online applications | Required identity data, document retention, privacy law |
| Bank feeds and reconciliation | Banking/aggregation provider and supported countries |
| Resident and vendor payments | Processor, settlement model, refunds, chargebacks, KYC/KYB |
| Vendor tax reporting | Country and tax forms |
| Insurance verification | Licensed partner and jurisdiction |
| Utility billing | Utility providers, meter model, tariff rules |
| E-signature compliance | Target jurisdictions and evidence requirements |
| AI workflows | Model provider, data residency, opt-out and review rules |

## Security completion gates

Every new module must pass all of these:

- landlord-account isolation tests;
- explicit role and permission checks for read and write actions;
- server-side authorization independent of navigation visibility;
- validation, upload type/size checks, and safe download responses;
- CSRF protection or cryptographically verified webhook signatures;
- request throttling for authentication and public tokens;
- secret and personal-data redaction from logs and audit records;
- encrypted sensitive fields where application-level querying is not required;
- idempotency for payments, webhooks, posting, and retryable jobs;
- audit events for material financial, access, document, and workflow changes;
- focused tests plus the full production asset build.

## Official comparison sources

- [AppFolio property management platform](https://www.appfolio.com/property-management-software)
- [AppFolio maintenance](https://www.appfolio.com/property-manager/maintenance)
- [AppFolio owner experience](https://www.appfolio.com/property-management-owner-experience)
- [AppFolio Q1 2026 product update](https://www.appfolio.com/blog/product-update-2026-q1)
- [AppFolio AI platform](https://www.appfolio.com/property-management-ai)
- [AppFolio marketplace](https://www.appfolio.com/stack/marketplace)
