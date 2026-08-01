# WhatsApp Order Summary Integration — Plan

## Goal

Let staff send a summary of an Order (customer info, items, totals, status) from
the order detail page in KIMS to a WhatsApp group chat, so the group stays
updated on new/changed orders without someone manually retyping the details.

## Constraint to understand first: WhatsApp APIs don't post into arbitrary group chats

This shapes every option below, so it's worth stating up front. WhatsApp's
**official** APIs (Meta Cloud API, and every reseller built on top of it like
Twilio) are designed for business-to-customer messaging — sending to
individual phone numbers, using pre-approved message templates for
business-initiated conversations. None of them expose an endpoint to "post a
message into this group chat I'm a member of." Group posting only exists in
the **unofficial**, ToS-violating automation space (tools that drive a real
WhatsApp Web session).

That means "fully automatic send-to-group" and "fully official/supported" are
mutually exclusive today. The plan below picks a pragmatic path through that
tradeoff rather than pretending it doesn't exist.

## Options comparison

| Option | Setup effort | Cost | Reliability / ToS risk | Actually posts to a group? | Ongoing maintenance |
|---|---|---|---|---|---|
| **1. Meta WhatsApp Cloud API (official)** | Medium — Meta developer app, verified business, phone number, message templates | Free tier, then per-conversation pricing | Official, stable, supported by Meta | **No** — 1:1 / template-based only, no group post endpoint | Low |
| **2. Third-party provider (Twilio, MessageBird, etc.)** | Low-medium — API key, hosted onboarding | Paid per message, often pricier than direct Cloud API | Official, supported by vendor | **No** — same underlying limitation as option 1 | Low |
| **3. Unofficial bridge (whatsapp-web.js / Baileys, self-hosted Node service)** | High — stand up and run a separate Node.js service, link it to a real WhatsApp number via QR code | Free (just hosting) | Violates WhatsApp's Terms of Service — real risk of the linked number getting banned; session can silently drop and need re-linking; no official support | **Yes** — can post into any group the linked number belongs to | High — you own uptime, reconnects, and ban risk |
| **4. Manual fallback: `wa.me` share link** | Very low — one URL, no backend integration | Free | Totally reliable (it's just WhatsApp's own client) | Effectively yes — a staff member taps the link and picks the group themselves | None |

## Recommendation

Ship in two phases:

- **Phase 0 (now, no new infrastructure)** — a "Share via WhatsApp" button on
  the order detail page that opens a `https://wa.me/?text=...` deep link with
  the order summary pre-filled. A staff member taps it, WhatsApp opens with
  the message ready, they pick the group, and send. This isn't "automatic"
  sending, but it satisfies the actual need (get an order summary into the
  group with one tap) with zero new services, zero API costs, and zero ToS
  risk.
- **Phase 1 (only if staff outgrow the one-tap flow and demand true
  automation)** — stand up a self-hosted Baileys/whatsapp-web.js bridge and
  call it from KIMS. Documented below so it's ready to build, but not started
  until Phase 0 proves insufficient, given its maintenance and ToS cost.

## Phase 0 implementation plan

1. **Message formatting** — add a small helper, e.g.
   `OrderController::buildWhatsAppSummary(int $id): string`, that reuses the
   existing data-loading calls from `OrderController::show()` (L254):
   `Order::find($id)` (`src/Models/Order.php`) and
   `OrderItem::getByOrder($id)` (`src/Models/OrderItem.php`). Format a plain-text
   summary mirroring what `src/Views/orders/show.php` already displays:
   order number, customer name/phone/delivery address, each item
   (product, size, qty, unit price, subtotal), total amount, payment method,
   payment/delivery status, notes.
2. **Deep link** — URL-encode that string into
   `https://wa.me/?text=<encoded summary>` (no phone number in the URL, since
   the destination is a group the user picks manually).
3. **UI** — add a "Share via WhatsApp" button/link (`target="_blank"`) next
   to the existing action buttons on `src/Views/orders/show.php`. If the
   formatting logic is simple, build the link directly in the view from data
   already passed to it; only add a dedicated controller method if the
   formatting is complex enough to be worth testing in isolation.
4. **No new route, config, or dependency needed** — this is a client-side
   link, so it doesn't touch `public/index.php` routing, `config/config.php`,
   or `composer.json`.

## Phase 1 implementation plan (documented for later, not built now)

1. **Config** — add a config block to `config/config.php` (following the
   existing `$_ENV`-backed constant pattern used for `MAIL_*` settings) for
   `WHATSAPP_BRIDGE_URL` and `WHATSAPP_GROUP_ID`, mirrored in `config/.env`.
2. **Service class** — new `src/Services/WhatsAppService.php` (no
   `src/Services/` directory exists yet) with a
   `sendOrderSummary(array $order, array $items): bool` method that issues a
   `curl` POST to the bridge service's send-message endpoint. Raw `curl` is
   used since Guzzle isn't installed (`composer.json` has no HTTP client
   dependency) and this is a single simple POST — no need to add one.
3. **Route + controller action** — new `POST orders/{id}/whatsapp` route in
   `public/index.php`, alongside the existing `POST orders/{id}/status`,
   `POST orders/{id}/delete`, `POST orders/{id}/adjustStock` routes. Dispatches
   to a new `OrderController::sendWhatsApp($id)` action: load order + items
   (same calls as Phase 0), call `WhatsAppService::sendOrderSummary`, flash a
   success/failure message, redirect back to `orders/{id}`. Follows the
   existing convention on `OrderController` POST actions of `Auth::requireLogin()`
   only (no CSRF is currently enforced on any Order POST action, so this
   doesn't diverge from the established pattern).
4. **UI** — a "Send to WhatsApp Group" button on `orders/show.php`, posting to
   the new route, following the same form/JS pattern as the existing
   status-update button on that page.
5. **Deployment** — the Baileys bridge is a standalone Node.js process that
   must run continuously (systemd/PM2 or a small separate VPS), independent of
   the PHP app's own cPanel deployment flow (`deploymentSteps.md`). This is a
   separate piece of infrastructure to provision and monitor, not something
   `.cpanel.yml` will manage.

## Open questions (not resolved by this document)

- Which WhatsApp group(s) actually need this — one group for all orders, or
  different groups per status/team?
- Will staff accept the one-tap manual flow (Phase 0) long-term, or is fully
  automatic sending a hard requirement?
- If Phase 1 is built, who owns keeping the Baileys bridge process running and
  re-linked if the session drops?
