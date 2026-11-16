# Capital Cart pre-launch security audit

Reviewed on 16 November 2026 against the attached *Pre-Launch Security Audit* (85 checks). This is a code and local test review. The live Plesk, GitHub and payment-provider settings require separate verification after deployment. No payment was charged during testing.

## Progress

All 85 checklist questions have been assessed. Of 67 application checks, 45 are addressed or verified in the local code, 5 need a production or provider check, and 17 do not apply to this Laravel storefront. Of 18 human checks, 5 do not apply and 13 require owner or hosting access. Overall, 45 of 63 applicable checks have local evidence (71%). The remaining 18 are deployment, account, policy or operational checks. These counts describe local audit coverage and are not a claim that production is secure.

| Checklist items | Local result | Evidence or remaining action |
| --- | --- | --- |
| 1.1, 1.4 | Addressed | Credentials stay in server configuration; SQL errors and upload errors return generic messages. Logs use reference IDs and omit bindings. |
| 1.2, 1.3 | Production check | Verify Git history and Plesk secret storage. Rotate every credential that was previously exposed or shared. Removing a value from current code does not revoke it. |
| 2.1, 2.2, 2.4, 2.5, 2.6 | Addressed | Server sessions, password hashes, login throttles, session rotation and secure production cookies. Password change available to signed-in users. |
| 2.3 | Open | Password reset and email verification are not implemented. Confirm SMTP and account recovery process before enabling those flows. |
| 2.7 | N/A | No social login. |
| 3.1, 3.2, 3.3, 3.6 | Addressed | Admin routes and Livewire uploads require server authorization. Admin role is not mass assignable. Demo credentials and public workflow page removed. |
| 3.4, 3.5 | N/A | No subscription entitlements or team tenancy. |
| 4.1, 4.2, 4.3 | Addressed | Eloquent bindings, random order references and controlled image storage. CV download path restricted to stored portfolio files. |
| 4.4 | Production check | Confirm database firewall and that Plesk serves `httpdocs/public` as document root. |
| 4.5 | N/A | No Supabase or Firebase. |
| 5.1–5.6 | Addressed | Server validation, product HTML sanitization, restricted image formats and sizes, safe links and generic error responses. Review any historical imported HTML after rollout. |
| 6.1–6.6 | Addressed | Login, signup, contact, newsletter and checkout limits; same-origin CORS; CSRF for browser actions; signed Stripe webhook. |
| 7.1–7.6 | Addressed locally | Checkout rechecks catalog prices, discounts and stock. Repeat submissions reuse an order. Card confirmation matches server order, amount and PKR currency. Signed payment/refund events are idempotent. Ordering information is linked from checkout. Live payment setup remains an operator step. |
| 8.1–8.5 | N/A | Storefront has no AI runtime integration. |
| 9.1–9.4 | Addressed | Composer and npm locks updated. Both dependency audits report zero known advisories as of review. Third-party assets were reviewed. |
| 10.1, 10.2, 10.5 | Addressed locally | Security headers, guarded production debug, audit log events. Verify actual HTTPS response headers after deployment. |
| 10.3 | N/A | No FastAPI. |
| 10.4 | Production check | Docker web root is `public` and only index.php is executable through Nginx. Verify Plesk IIS routing and sensitive-file rules separately. |
| 11.1–11.3 | Addressed locally | Visitor writes limited, exports stream, dead public workflow removed and production bootstrap fails on migration errors. Review cloud billing alerts. |
| 12.1–12.3 | Addressed locally | Privacy and ordering pages linked, visitor count requires opt-in, customer can delete login and reviews, and purchase records remain for accounting. Legal policy wording and retention periods need owner approval. |
| 13.1–13.7 | N/A | No mobile app. |

## Owner and hosting checks

| Items | State | Action |
| --- | --- | --- |
| H1, H3, H4, H12 | Open | Rotate any shared keys or old seeded passwords, check Git history, enable MFA for GitHub, Plesk, Stripe, email and registrar, verify SPF/DKIM/DMARC. GitHub repository is currently private. |
| H2, H7, H13, H14 | Open | Set spending alerts, test database and image backup restoration, document rollback of the Git commit and previous database backup, set uptime and error alerts. |
| H5, H6, H9, H11, H16 | Open | Perform two-account manual privacy check on staging, review retained personal data, approve refunds/tax and newsletter unsubscribe process, keep test data off production. |
| H8, H10, H15, H17, H18 | N/A | No Supabase/Firebase, AI product integration or mobile app. Child privacy requirements should be reviewed if the store specifically targets children. |

## Release procedure

1. Back up the live database and record the current Git commit. Confirm the restoration process before migration.
2. Deploy the reviewed Git commit through Plesk Laravel Toolkit. Run `php artisan migrate --force` once and confirm success. Clear config and view caches. Confirm the site, admin login, checkout and an existing product photo on HTTPS.
3. The new gallery picker accepts multiple images in one selection and uploads each file sequentially. Create one draft product with five photos, check their order and primary image, then repeat on Edit with two more. Individual photo uploads remain available.
4. Card checkout remains unavailable until valid PKR capable live Stripe keys and a signed `STRIPE_WEBHOOK_SECRET` are configured in the server secret store. Register `https://capitalcart.pk/payments/stripe/webhook` for `payment_intent.succeeded` and `charge.refunded`; verify using provider test mode before enabling live credentials. Configure `TRUSTED_PROXIES` only with actual reverse proxy addresses if needed. Never put secrets in Git or a support message.
5. Verify the site document root and file-access restrictions on Plesk. Confirm production mode, TLS, email delivery, backups, staff MFA and access to the admin password-change page. Rotate any earlier demo or disclosed credentials.

## Verification completed locally

`php artisan test --compact` passed 68 tests and 350 assertions. Blade and route caches compiled. `npm run build` succeeded. `composer audit --locked` and `npm audit` found zero known advisories. A local Chrome test selected five gallery photos in one picker on Create and two on Edit, then saved both. Sass reported deprecation warnings during the successful build.
