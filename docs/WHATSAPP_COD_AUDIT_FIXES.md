# WhatsApp cash on delivery and audit corrections

These changes follow the owner's decision to manage new orders through WhatsApp with cash on delivery. They are based on GitHub commit `aec16a57cd35f13692e451643cc9fac54ca00971` and prepared in the separate `codex/audit-corrections` checkout. The initial audit did not change production. The owner subsequently authorized these corrections and deployment; release outcomes are recorded separately.

## New checkout behavior

- Checkout saves a pending WhatsApp request. The customer then taps **Send Order Details on WhatsApp** and sends the prepared message to the seller.
- Cash on delivery is the only offered payment arrangement. The separate COD, bank transfer and card selections, Stripe checkout script, and public Livewire card creation/finalization actions are removed.
- The server accepts only `whatsapp` for new customer checkout submissions, even if old Stripe credentials remain configured.
- Name and Pakistani mobile number are required. Email, address, city and notes remain optional for the assisted WhatsApp conversation. Supplied delivery details and notes are included in the prepared message.
- Historical payment records and signed Stripe webhook handling are preserved for any outstanding old transactions. They do not provide a new checkout entry point. No new bank details or payment-reconciliation system was added.

## Corrected behavior

- F01: no default maximum catalog price; filter/sort use the effective selling price; reset restores the same defaults; pagination size is bounded.
- F03: unpaid pre-dispatch cancellation restores stock/coupon usage once; closed orders cannot be reopened; invalid/backward status transitions are rejected. Paid orders require reconciliation before cancellation. This does not introduce a returns/refunds workflow or retroactively repair old stock.
- F04: inventory and product forms reject stale stock snapshots; variant inventory updates are scoped to the product and transactional.
- F06: reimports preserve existing inventory, active state and images; exact SKU matching replaces ambiguous name matching. A full preview workflow remains future work.
- F08: coupon eligibility and amount refresh when cart items change; demo coupon suggestions removed.
- F09: generated JPEG fallback images are served, while unrelated MIME types remain rejected.
- F10: receipts show actual order status; pending WhatsApp instructions explain the remaining send step; automatic popup removed.
- F11: Deals and Shop Sale use a real sale filter. The production category data was not changed.
- F12/F13: named navigation/search/filter/checkout controls, keyboard-operable gallery buttons and horizontally scrollable cart. Full device/screen-reader verification remains outstanding.
- F15: public smoke checks target capitalcart.pk. Build output must still be deployed together with its manifest.
- F16: new reservations use the configured expiry duration; explicit deadlines take precedence over the legacy created-at cutoff.
- F19: named event payloads display their actual message/severity; toast messages are inserted as text.
- Dashboard pending counts include WhatsApp requests. Review text no longer invites an unsupported submission action.

## Validation

The original suite passed before editing: 68 tests and 350 assertions. The corrected suite includes 13 new regression tests for catalog filters, sale discovery, cancellation idempotency, closed-order protection, stale inventory/product forms, repeated inventory saves, coupon recalculation, JPEG responses, rejected alternate payment methods, WhatsApp content/receipts, configured expiry and safe reimports.

Use `php vendor/phpunit/phpunit/phpunit`, `php artisan view:cache`, and `npm ci && npm run build` in an isolated development environment. Tests use SQLite in memory. MariaDB concurrency and real-device browser testing are still required before production rollout. Sass deprecation warnings remain upstream build maintenance work.

## Before deploying

1. Verify production environment/HTTPS recognition and secure session cookies. The current code already defaults secure cookies on in production; the live header issue requires hosting/environment verification. Do not blindly broaden trusted proxy settings.
2. Back up the database/media and verify a restore path. Record the deployed source commit and build manifest. This change introduces no database migration.
3. Deploy PHP/templates and the newly built `public/build` manifest/assets as one release. Refresh compiled views/config through the established deployment process and retain a rollback artifact.
4. Verify the scheduler executes reservation expiry. Check old cancelled/expired records for any historical stock imbalance manually; this change does not mutate them.
5. In staging, exercise the WhatsApp/COD journey on phone and desktop, confirm the seller destination, and verify long messages with addresses and notes. Do not use real customer orders as tests.

## Remaining audit items

The original audit PDF is a dated pre-change baseline, not a claim that all findings remain unresolved. Hosting cookie/HSTS settings, production deployment drift, category cleanup, broader SEO/sitemap work, account recovery/order history, notification delivery, image derivatives and complete device testing remain open. The owner's WhatsApp-only decision supersedes proposals to add bank/card checkout or a broader payment reconciliation feature. Business policies and provider-specific legacy refunds still need owner handling.
