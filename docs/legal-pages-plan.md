# Kirada Legal Documents — Production Deployment Plan

## Routes
| Page | URL | View |
|------|-----|------|
| Terms of Service | `/terms-of-service` | `pages.legal.terms-of-service` |
| Privacy Policy | `/privacy-policy` | `pages.legal.privacy-policy` |
| How It Works | `/how-it-works` | `pages.legal.how-it-works` |

All three are:
- ✅ Public (no auth required)
- ✅ Using the `public.blade.php` layout (header + nav)
- ✅ Multi-language ready (all strings wrapped in `__()`)
- ✅ Translations added for Arabic, Somali, Amharic, French
- ✅ Footer links added on welcome page

## Liability Protection Summary

**Kirada is explicitly NOT:**
- A bank, payment processor, money transmitter, or escrow service
- A real estate broker, property management company, or maintenance company
- A debt collector, legal service provider, or insurance provider

**Kirada IS only software for:**
- Record keeping, communication, document organization
- Maintenance tracking, lease tracking, reporting, dashboards

**Liability cap:** Subscription amount paid in previous 12 months, or $100 USD if free.

**Governing law:** State of Maine, United States (configurable via `LEGAL_JURISDICTION` env var).

**Force majeure clause** included (important for East African markets — power/internet instability).