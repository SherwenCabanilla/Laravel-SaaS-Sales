# Payment, Payouts, Commissions Architecture

Source: consolidated from the Codex thread titled `Structure payment prompt`.

## Purpose

This document captures the recommended architecture for billing, tenant payouts, receipts, reports, automation, and commissions in the Laravel multi-tenant SaaS funnel system.

The main goal is to keep three accounting layers separate:

1. Platform subscription billing
2. Tenant funnel earnings
3. Internal tenant commissions

That separation keeps the system cleaner, safer, and easier to scale.

## Core Principle

Treat money flow as two different business domains:

### 1. Platform subscription payment

This is the payment the account owner makes to use the SaaS itself.

- Belongs to platform billing
- Managed by super admin platform rules
- Drives plan access, usage limits, countdowns, grace periods, and subscription lifecycle

### 2. Funnel customer payment

This is the payment made by an end customer inside a tenant funnel.

- Belongs to the tenant
- Should settle to the tenant account owner's verified payout account
- Must not default to the super admin as earnings receiver

## Role Responsibilities

### Super admin

- Manages platform plans, defaults, billing policies, global fee rules, and audit oversight
- Does not receive tenant funnel earnings by default
- Should have `0%` commission on tenant funnel earnings by default
- If needed, may receive a small platform transaction fee such as `1%` to `3%`, but this should be modeled as a platform fee, not a role commission

### Account owner

- Controls tenant payout settings
- Owns tenant funnel net earnings
- Sees subscription status, plan limits, countdowns, billing state, and reports
- Can review payout and commission summaries

### Finance

- Reviews uploaded receipts and payment proofs
- Approves, rejects, or verifies payment evidence
- Oversees commission review and payout readiness
- Should normally receive `0%` sales commission

### Sales-agent

- Can receive commission only when directly assigned to the lead, sale, or customer journey

### Marketing-manager

- Can receive commission only when valid campaign attribution exists

## Payout Account Design

Payout configuration should be tenant-level and controlled by the account owner.

Recommended fields:

- `payout_method`
- `destination_type`
- `destination_value`
- `account_name`
- `provider_reference`
- `verification_status`
- `verified_at`
- `verified_by`
- `notes`

Recommended behavior:

- Support GCash number and provider-managed payout destination
- Do not store raw card numbers
- If card-related payout metadata exists, store only masked or provider-managed references
- Show masked destination values in UI

Recommended form copy:

- `GCash / bank identifier`
  Enter the actual payout destination.
  For GCash, use the registered mobile number like `09XXXXXXXXX`.
- `Provider-managed reference`
  Store the provider destination ID or recipient reference if available.
- `Operations notes`
  Store verification and finance notes only.

## Subscription and Billing UI

The account owner should see:

- current plan
- subscription status
- trial countdown
- active subscription countdown
- grace-period countdown
- next billing cycle
- usage limits
- over-limit warnings

Recommended placement:

- account owner dashboard summary cards
- billing page for details and receipt actions
- profile or billing settings for payout account configuration

## Receipts and Proof of Payment

Manual receipt upload should be supported for payments that need proof.

Recommended flow:

1. User uploads receipt or e-receipt
2. System attempts automated verification
3. If exact match is found, mark as `auto_approved`
4. If not, route to finance review
5. Finance can approve or reject manually

Recommended receipt statuses:

- `pending_review`
- `verified`
- `rejected`
- `auto_approved`

Verification should check:

- amount
- reference number
- payment provider
- tenant
- payment type
- acceptable time window

If any critical field is missing or mismatched, keep the receipt in finance review.

## Commission Model

Commission should be tenant-configurable and tenant-owned, not globally hardcoded.

Recommended V1 defaults:

- platform fee: `1%` to `3%` optional, recommended `2%`
- sales-agent: `5%` to `10%`, recommended `7%`
- marketing-manager: `2%` to `5%`, recommended `3%`
- finance: `0%`
- account-owner: residual net earnings

Recommended commission basis:

- Use `net eligible amount`, not gross paid amount

Net eligible amount should usually mean:

`paid amount - gateway fees - platform fees - refunds - reversals - disqualified amounts`

This is the safer default because it reduces commission overpayment risk.

## Commission Lifecycle

Recommended statuses:

- `pending`
- `held`
- `approved`
- `payable`
- `paid`
- `reversed`
- `cancelled`

Recommended hold period:

- `7` to `14` days
- recommended default: `7` days for V1

Recommended rules:

- New commissions should start as `held`
- Refunds, disputes, reversals, and failed payments should block payout or reverse commission
- Do not mark entries `payable` until hold rules pass

## Attribution Rules

Recommended attribution model:

- sales-agent: assigned lead or assigned customer journey
- marketing-manager: valid attributed campaign only
- tenant may later configure first-touch, last-touch, or custom attribution rules

Recommended V1:

- sales uses direct assignment
- marketing uses explicit campaign attribution

This is simpler and more auditable than trying to implement multi-touch attribution too early.

## Reports Page

Recommended sidebar placement:

- show `Reports` in the account owner sidebar

Recommended reports page content:

- total gross revenue
- total net eligible revenue
- total commissions by role
- platform fees
- gateway fees
- payout summary
- receivables summary
- recent payments
- recent receipts
- recent commission entries
- top funnels
- top campaigns
- revenue trend chart
- date filters
- funnel filters
- campaign filters
- export controls

Recommended export:

- include a `Download Excel` button using the same design pattern as the existing export buttons in the system

Recommended automation on reports:

- do not make the page itself automation-dependent
- use automation for report generation, scheduled summaries, and owner digests
- the page should still render from Laravel data directly

Best use of automation:

- scheduled weekly owner digest
- scheduled finance digest
- scheduled payable commission summary
- report-ready export notifications

## Automation Events for n8n

Recommended events:

- `subscription_paid`
- `subscription_renewed`
- `subscription_overdue`
- `funnel_payment_paid`
- `receipt_uploaded`
- `receipt_auto_approved`
- `receipt_approved`
- `receipt_rejected`
- `commission_created`
- `commission_payable`
- `commission_paid`
- `owner_report_digest_requested`
- `owner_report_digest_scheduled`

Recommended email targets:

- account owner
- buyer
- finance team
- optional sales-agent or marketing-manager based on event type

## Data Model Recommendations

Recommended main entities:

- `payments`
- `tenant_payout_accounts`
- `payment_receipts`
- `commission_plans`
- `commission_entries`

Suggested responsibilities:

### `payments`

- source of paid, pending, failed, refunded, and reversed payment state
- should continue distinguishing platform subscription vs funnel checkout flows

### `tenant_payout_accounts`

- tenant-owned payout destination
- masked display fields
- verification tracking

### `payment_receipts`

- uploaded proof records
- finance review outcome
- automation status

### `commission_plans`

- tenant-level commission defaults
- role percentages
- hold periods
- whether platform fee is enabled

### `commission_entries`

- per-user, per-payment commission records
- basis amount
- percentage
- calculated amount
- status transitions
- reversal references

## Approval and Payout Workflow

Recommended high-level workflow:

1. Payment record is created
2. Payment becomes `paid` by webhook, manual verification, or approved receipt
3. System calculates net eligible amount
4. Commission entries are created for valid attributed roles
5. Commission entries start as `held`
6. Hold period passes and no dispute/refund/reversal exists
7. Entries become `payable`
8. Finance reviews payout readiness
9. Payout is processed to verified tenant payout account
10. Entries become `paid`

Recommended authority:

- account owner: view tenant reports, payout settings, summaries
- finance: review receipts, approve or reject evidence, review payout readiness
- super admin: global override and audit oversight only

## Security and Audit Controls

Recommended controls:

- never store raw card numbers
- encrypt sensitive payout destination values at rest
- display only masked destination values in UI
- keep cross-tenant isolation strict
- log approval actions
- log overrides
- log receipt review actions
- log payout-account changes
- log commission reversals

Recommended audit fields:

- `created_by`
- `updated_by`
- `approved_by`
- `verified_by`
- `override_reason`
- timestamps for each critical transition

## V1 vs Future Scale

### Recommended V1

- tenant payout account settings
- manual receipt upload and finance review
- exact-match auto-approval
- tenant-level commission plan
- commission entries with hold period
- owner reports page
- Excel export
- n8n event hooks for receipts, commissions, and owner digests

### Future scalable version

- payout batches
- payout items
- dispute management
- reversal workflow UI
- advanced attribution rules
- multiple payout destinations
- split payouts
- richer audit viewer
- more advanced scheduled reporting

## Final Recommendation

For this system, the best architecture is:

- keep platform billing separate from tenant earnings
- keep tenant earnings separate from internal commissions
- let the account owner control payout settings
- let finance control proof review and payout readiness
- use n8n for notifications and scheduled reporting, not as the only source of truth
- base commissions on net eligible amount
- hold commissions before payout
- treat super admin platform share as a platform fee, not a sales commission

That gives you the safest V1 and the cleanest path to future scaling.
