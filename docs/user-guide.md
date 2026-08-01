# Kirada user guide

Kirada brings properties, tenants, leases, rent records, maintenance, documents,
contracts, messaging, and landlord teams into one workspace. This guide explains
the normal workflow for each account type.

## 1. Before you begin

Kirada has four account types:

| Account | Main responsibility |
| --- | --- |
| Landlord | Owns the portfolio, controls billing and team administration, and confirms payments |
| Landlord team member | Helps operate one landlord portfolio with role-based access |
| Tenant | Views their lease and invoices, submits payment proof, and reports maintenance |
| Maintenance professional | Publishes a service profile and completes assigned work orders |

The first release does not move or hold rent money. The landlord publishes one
or more payment accounts (for example D-Money, Waafi, CAC Bank, bank transfer, or
cash). The tenant pays outside Kirada and uploads proof. The landlord reviews the
proof before confirming the payment.

## 2. Landlord setup checklist

Complete these steps in order after creating a landlord account:

1. Verify your email and complete your profile.
2. Open **Settings → Payment accounts** and add the accounts tenants may use.
   Give each account a clear name and mark one as primary.
3. Open **Settings → Notifications** and choose invoice and reminder channels.
   Email is available by default; WhatsApp requires the BWA connection and
   approved templates to be configured.
4. Create your properties, buildings, and units.
5. Create a tenant record or send a tenant invitation.
6. Create the lease with the rent, deposit, due day, start date, end date, and
   reminder settings.
7. Create the first rent invoice and review the delivery history.
8. Invite staff from **Property Team** when another person needs access.
9. Review **Reports** regularly for collection, occupancy, maintenance, and lease
   renewal signals.

### Property structure

Use the hierarchy **Property → Building (optional) → Unit → Lease → Tenant**.
Keep one unit number unique within a property. A unit becomes occupied when an
active lease is created and returns to vacant when the lease is ended or
cancelled.

### Payment accounts

Payment accounts are instructions, not payment processors. Add the provider,
account identifier, display name, and any tenant-facing instructions. Do not put
passwords, API tokens, or private banking credentials in the instructions.

### Invoices and payment proof

The normal first-release flow is:

1. Landlord creates an invoice.
2. Tenant receives or opens the invoice.
3. Tenant pays the landlord directly using the published account.
4. Tenant submits the amount, reference, payment account, and proof file.
5. Landlord checks the proof and confirms or rejects the payment.
6. Kirada updates the invoice status and makes a receipt available.

Do not mark a payment confirmed without checking the proof. A rejected proof can
be corrected by submitting a new payment record with a clear reference.

### Lease renewals

The lease list shows leases ending within 30 days, leases in the 90-day renewal
pipeline, and active leases whose end date has passed. Open the lease, contact the
tenant, agree the new terms, and update the lease before creating the next invoice.

## 3. Tenant onboarding and daily use

1. Open the private invitation link sent by the landlord by email, WhatsApp, or
   both. A WhatsApp invitation uses the phone number saved on the tenant record
   and the approved BWA invitation template.
2. Create or link your account using the invited email address. If the invitation
   was sent only by WhatsApp, use the email address shown on the invitation or
   contact the landlord to confirm it before accepting.
3. Set a strong password and complete your profile.
   The invitation itself is not ongoing WhatsApp consent; choose the WhatsApp
   notification preference during onboarding or later in **Settings**.
4. Open the tenant dashboard to review your lease, invoices, payment history, and
   documents.
5. Pay rent directly using the landlord's instructions, then submit payment proof.
6. Use **Maintenance** to report an issue with a description, urgency, preferred
   access time, and photos.
7. Use **Messages** for questions that belong to the landlord or property team.
8. Review and sign contracts from the secure signing link when requested.

The tenant dashboard is the source of truth for invoice status. A WhatsApp or
email reminder is only a notification; it does not confirm that rent was paid.

## 4. Property teams

The account owner invites team members from **Property Team**. Each invitation
belongs to one landlord portfolio and expires if it is not accepted in time.

| Role | Use it for | Important limitation |
| --- | --- | --- |
| Landlord account owner | Billing, portfolio, team, and account control | Only the owner manages subscription and other administrators |
| Landlord Admin | Daily operations and team support | Cannot control subscription or account ownership |
| Property Manager | Properties, units, tenants, leases, maintenance, and messages | Cannot confirm payments or manage billing |
| Accountant | Invoices, payment confirmation, documents, and reports | Property and tenant data is read-only |
| Viewer | Read-only portfolio visibility | Cannot create, edit, or confirm records |

Invite the least-privileged role that can complete the work. Remove a member
immediately when they leave the organization, then review the audit history.

## 5. Maintenance professional workflow

1. Register as a **Maintenance professional** rather than a landlord.
2. Open **Maintenance Profile** and add your business name, trades, service area,
   experience, languages, availability, rates, and contact details.
3. Publish the profile so eligible landlords can discover it.
4. A landlord requests a connection; accept it from **Maintenance Network**.
5. The landlord assigns an approved provider to a maintenance request.
6. Review the request, photos, location, and priority.
7. Submit a quote when required. Work should begin after the landlord approves it.
8. Keep the status current, add notes and completion photos, then mark the work
   resolved.
9. A landlord can publish one verified review after a completed work order.

Never use a tenant's private contact information outside the authorized Kirada
workflow. Keep quotes and completion notes attached to the work order.

## 6. WhatsApp, email, and in-app notifications

Kirada uses a delivery channel preference for invoice and reminder events. When
WhatsApp is enabled, Kirada signs requests to BWA; Meta credentials remain in
the BWA service and are never stored in Kirada.

Recommended notification events:

- Invoice created, due soon, and overdue
- Payment proof submitted, approved, or rejected
- Maintenance request received, assigned, scheduled, quoted, approved, and resolved
- Contract or invitation ready to accept or sign
- Lease renewal due

Each delivery has a status such as queued, sent, delivered, read, or failed.
Use delivery history to diagnose a missing message. A failed WhatsApp delivery
should fall back to email or in-app notification when the landlord's policy allows
it.

WhatsApp business-initiated messages must use the approved template configured by
the messaging provider. Keep templates short, identify Kirada, include the
invoice or work-order reference, and provide an in-app link for full details.

## 7. Contracts and e-signatures

1. A landlord creates a contract from a lease or tenant record.
2. Review the parties, dates, rent, deposits, and legal clauses carefully.
3. Send the signing request.
4. The recipient opens the public signing link, reads the full document, and
   submits their legal name and signature.
5. Download the signed PDF from the contract record.

Electronic-signature validity depends on the applicable jurisdiction. Kirada
stores the evidence and document; it does not provide legal advice.

## 8. Account security

Open **Settings → Security** to manage login protection:

- Enable TOTP 2FA with an authenticator app.
- Save recovery codes in a password manager.
- Add a passkey using a device PIN, fingerprint, Face ID, phone, or security key.
- Remove old passkeys when a device is lost or replaced.
- Confirm your current password before sensitive security changes.

Passkeys and 2FA are complementary. A passkey protects passwordless login; 2FA
protects email-and-password login. Never send recovery codes or passwords through
WhatsApp, email, or in-app messages.

## 9. Common problems

**The tenant did not receive an invitation**

Check the selected delivery channel, contact details, approved WhatsApp template,
and BWA/queue worker status. Resend the invitation or use **Send via WhatsApp**
from the invitation actions. The tenant can use the private invitation link
directly when a delivery is delayed.

**A payment is still pending**

Payment proof is intentionally pending until the landlord or an authorized
accountant reviews it. Kirada does not infer payment from a WhatsApp message.

**No maintenance provider appears in the assignment list**

The provider must have a published profile and an approved landlord connection.
An unapproved directory profile cannot be assigned work.

**WhatsApp is not sending**

Check the tenant's opt-in, international phone number, selected channel, approved
template name/language, BWA delivery history, and queue worker. Do not paste a
Meta credential into Kirada.

**The passkey prompt fails**

Use the same HTTPS domain that registered the passkey, verify that the browser
supports WebAuthn, and try the password login or a recovery method if the device
is unavailable.

## 10. Administrator deployment notes

The Pi should be treated as development/staging only. Production should run on
the Hetzner CloudPanel site with separate credentials, database, storage, queue,
and mail configuration.

Before announcing a release:

1. Run the full test suite and production asset build.
2. Review migrations and back up the database.
3. Set `APP_ENV=production` and `APP_DEBUG=false`.
4. Cache configuration, routes, views, and events after clearing stale caches.
5. Run the queue worker and scheduler under Supervisor/cron.
6. Verify HTTPS, private storage, backups, restore procedure, and `/up` health.
7. Test one landlord, tenant, maintenance, contract, payment-proof, email, and
   WhatsApp flow in production-safe mode.

See the [deployment checklist](deployment-checklist.md) for the server runbook
and the [parity roadmap](appfolio-parity-roadmap.md) for planned modules.
