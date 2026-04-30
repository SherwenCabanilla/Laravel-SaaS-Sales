# n8n Automation Gaps

## What Laravel already supports

### Outbound events currently emitted to n8n
- `account_owner_paid_signup_created`
- `account_owner_google_paid_signup_created`
- `team_member_invited`
- `customer_portal_invited`
- `setup_link_expiring`
- `setup_link_expired`
- `payment_successful` (only if `N8N_SEND_PAYMENT_SUCCESS_EVENT=true`)
- `lead_captured`
- `lead_stage_changed`
- `payment_failed`
- `payment_recovered`

### Inbound callback/action endpoints already available for n8n
- `POST /api/n8n/email-status`
- `POST /api/n8n/lead-activity`
- `POST /api/n8n/lead-score`
- `POST /api/n8n/send-email`
- `POST /api/n8n/send-sms`
- `POST /api/n8n/agent-task`
- `GET /api/n8n/invoice-status`
- `POST /api/n8n/suspend-subscription`
- `POST /api/n8n/payment-recovered`
- `POST /api/n8n/automation-log`
- `GET /api/n8n/analytics-daily`
- `POST /api/n8n/analytics-store`
- `POST /api/n8n/send-owner-digest`
- `POST /api/n8n/trial-inactive-recovery`
- `POST /api/n8n/run-inactive-trial-recovery`

## Main gaps without changing Laravel

### 1. Funnel events are tracked in Laravel but not emitted to n8n
Laravel records these internally through `FunnelTrackingService`, but they are not currently dispatched through `N8nEmailOrchestrator`:
- `funnel_opt_in_submitted`
- `funnel_checkout_started`
- `funnel_payment_paid`
- `funnel_checkout_abandoned`
- upsell/downsell decision events
- order delivery update events

This is the biggest automation gap. n8n cannot react to those events in real time unless Laravel starts sending them out.

### 2. Trial lifecycle reminders are not emitted as webhook events
The app has support for owner digest and inactive-trial recovery endpoints, but it does not emit dedicated outbound webhook events like:
- `trial_reminder_t3`
- `trial_reminder_t1`
- `trial_expired`
- `trial_upgrade_nudge`

Those can only be approximated by scheduled n8n jobs with the endpoints that already exist.

### 3. Subscription lifecycle coverage is partial
Current outbound billing-related events are:
- `payment_failed`
- `payment_recovered`

Missing outbound events:
- `subscription_renewed`
- `subscription_cancelled`
- `payment_paid` for platform billing in a general-purpose contract

### 4. No strong idempotency key in the outbound event contract
The webhook payloads do not currently include a durable event id such as:
- `event_id`
- `idempotency_key`
- `occurred_at`

That means n8n can branch on business data, but true duplicate-protection is still weaker than it should be.

### 5. Existing workflow JSON had contract drift
The previous export included router branches for events that Laravel does not emit today, and some switch expressions were malformed for several trial-event routes. The replacement JSON removes that drift and aligns with the current codebase.

### 6. Provider setup is still external to the repo
The replacement workflow uses Brevo for onboarding/auth email delivery and Laravel callback endpoints for the rest of the automation actions. You still need n8n env/config values such as:
- `BREVO_API_KEY`
- `BREVO_SENDER_EMAIL`
- `BREVO_SENDER_NAME`
- `N8N_WEBHOOK_TOKEN`
- `N8N_CALLBACK_BEARER_TOKEN`

## What the replacement JSON covers

- Fully connected onboarding/auth email workflow
- Lead capture automation with scoring, follow-up email/SMS, and hot-lead tasking
- Lead stage change proposal reminder
- Failed payment recovery ladder at day 1, day 3, and day 7
- Payment recovered owner notification
- Daily analytics snapshot + owner digest
- Trial owner digest schedule
- Inactive trial recovery schedule
- No disconnected/orphan nodes in the import file

## What still cannot be made truly complete without Laravel changes

- Real-time opt-in automation from funnel submissions
- Checkout-start automation
- Abandoned-checkout automation
- Funnel payment-paid automation
- Upsell/downsell automation
- Trial-expiry outbound event automation
- First-class idempotent event replay protection
