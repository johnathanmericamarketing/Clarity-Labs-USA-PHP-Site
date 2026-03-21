# Storefront ↔ clarity-ops Integration Plan

## Context

The Clarity Labs USA PHP storefront (vanilla PHP, no framework) needs to evolve from a static product catalog into a full e-commerce site by connecting to the existing **clarity-ops** Laravel/Filament admin platform, which already has a public REST API, Stripe payments, EasyPost shipping, inventory management, and customer/order models.

**Current state:** Products are hardcoded in a PHP array. Orders just send confirmation emails — no payment, no cart, no auth, no database.

**Target state:** A real e-commerce flow where the PHP storefront calls the clarity-ops API for everything transactional (products, pricing, stock, auth, checkout, orders) while keeping its clean vanilla PHP frontend.

---

## Architecture

```
┌─────────────────────┐
│  claritylabsusa.com  │   Informational site (home, about, FAQ, contact)
│  (main site)         │   Links to shop.claritylabsusa.com for "Shop" nav
└─────────────────────┘

┌──────────────────────────────┐       HTTPS/JSON        ┌──────────────────────────────┐
│  shop.claritylabsusa.com     │  ←─── API Client ───→   │   clarity-ops (Laravel)      │
│  (storefront)                │    (server-side cURL)    │   ops.claritylabsbio.com     │
│                              │                          │                              │
│  • Product listing & detail  │   X-API-Key header       │  • /api/v1/products          │
│  • Session-based cart        │   Bearer token (auth)    │  • /api/v1/auth/*            │
│  • Checkout UI               │                          │  • /api/v1/orders            │
│  • Customer account pages    │                          │  • /api/v1/shipping/rates    │
│  • Stripe.js (client-side)   │                          │  • StripeGateway service     │
│                              │                          │  • EasyPost service          │
│  Served by nginx + php-fpm   │                          │  • Transactional emails      │
│  Same VPS                    │                          │  • Filament admin panel      │
└──────────────────────────────┘                          └──────────────────────────────┘
```

**Three sites on one VPS (187.124.228.193), managed by CloudPanel:**
1. `claritylabsusa.com` — informational (home, about, FAQ, contact)
2. `shop.claritylabsusa.com` — storefront (products, cart, checkout, account)
3. `ops.claritylabsbio.com` — admin panel (Filament)

**Key decisions:**
- **Server-side API calls** (PHP cURL) — API key never reaches the browser
- **Session-based cart** — no API cart endpoint needed; validated against API at checkout
- **Payment TBD** — will be ACH, Zelle, Venmo (NOT Stripe cards). Payment integration left open until method is finalized. Checkout page has placeholder for payment step.
- **Hybrid product data** — API provides catalog/pricing/stock; local file retains rich marketing content (why_cards, research_apps, etc.)
- **Site split** — main site keeps informational pages, shop subdomain gets all e-commerce pages
- **Shared nav** — both sites share the same header/footer design; "Shop" link on main site points to `shop.claritylabsusa.com`

---

## Infrastructure

- **VPS:** 187.124.228.193
- **Server Management:** CloudPanel (handles nginx, PHP-FPM, SSL, site creation via UI)
- **Database:** MySQL (managed by CloudPanel)
- **Auth (clarity-ops):** Laravel Jetstream (login, register, 2FA/TOTP, profile) + Socialstream (Google OAuth — stubbed)
- **clarity-ops domain:** ops.claritylabsbio.com (already configured)
- **Storefront domain:** claritylabsusa.com (GoDaddy — needs DNS pointed to VPS)
- **File/Image Storage:** Cloudflare R2 (S3-compatible, bucket: `claritylabs-assets`)
- **R2 Public URL:** `pub-ff60dc038f7644d1afd85fa7910382f3.r2.dev`

## Git & Deployment

- **Website repo:** `github.com/johnathanmericamarketing/Clarity-Labs-USA-PHP-Site.git`
- **clarity-ops repo:** `github.com/johnathanmericamarketing/clarity-ops.git`
- **Deploy workflow:** Push to GitHub → SSH into VPS → `git pull`
- Both repos follow this same flow: commit locally, push, SSH pull on server

## Domain & SSL Setup

### Setup Steps
1. **Cloudflare (free tier):** Add `claritylabsusa.com` to Cloudflare. Domain stays registered at GoDaddy — just change nameservers in GoDaddy to Cloudflare's (2-minute change). Cloudflare provides CDN, DDoS protection, SSL, and DNS management.
2. **Cloudflare DNS:** Add A records for `claritylabsusa.com`, `www`, and `shop` → `187.124.228.193` (orange-clouded/proxied)
3. **CloudPanel:** Add two sites:
   - `claritylabsusa.com` — main informational site (document root: repo root `/`)
   - `shop.claritylabsusa.com` — storefront (document root: repo subfolder `/shop/`)
4. **SSL:** Cloudflare handles edge SSL. CloudPanel Let's Encrypt for origin cert (Cloudflare ↔ VPS encryption).
5. **Deploy:** Clone the website repo on VPS, git pull to deploy

---

## Phase 1: Foundation (must have before anything works)

### 1.1 Config & API Client Layer
**New files:**
- `config/config.php` — site constants (API base URL, Stripe publishable key, site URL). Reads secrets from `.env` file outside document root
- `config/.env` — API key, Stripe keys (gitignored, never committed)
- `includes/api-client.php` — `ClarityApiClient` class (single-file, cURL, no dependencies). Methods: `getProducts()`, `getProduct()`, `getProductAvailability()`, `getCategories()`, `registerCustomer()`, `loginCustomer()`, `createOrder()`, etc.
- `includes/session.php` — secure session init + cart helper functions (`cart_add()`, `cart_remove()`, `cart_count()`, `cart_total()`, `is_logged_in()`, `set_customer()`)
- `includes/csrf.php` — CSRF token generation/validation for all POST forms

### 1.2 Access Gate (required before ANY product browsing)

The shop is **fully gated** — no anonymous browsing. Every visitor must pass two checks:

**Gate 1: Age Verification Modal**
- Full-screen modal overlay on first visit to `shop.claritylabsusa.com`
- Clarity Labs USA logo + "Confirm Your Age"
- "Confirm that you are 21 years old or over."
- Research-only disclaimer: "All products are sold in powder (lyophilized) form and require reconstitution with a suitable diluent for research purposes only. Research supplies (e.g., syringes, bacteriostatic water) are not included. No dosing instructions are provided. We adhere to all local and state laws around Research Only Chemical sales. We are not a pharmacy, nor do we promote or provide any advice for human or animal consumption. Please review our terms and conditions carefully before making a purchase on our website."
- "Enter Website" button → sets `age_verified` cookie (long-lived, e.g. 30 days)
- No way to dismiss without confirming

**Gate 2: Sign-In Required**
- After age verification, if not logged in → "Sign-In Required to Access Clarity Labs USA" page
- "To ensure compliance with evolving regulations and continue providing high-quality research products, Clarity Labs USA now requires all professional researchers to log in to browse or purchase."
- "Why The Change?" callout: regulatory compliance, researcher verification, responsible product access
- **First-time visitors:** Registration form (first name, last name, email, date of birth [month/year → ClpCustomer.birth_month/birth_year], research acknowledgment checkbox). DOB auto-validates 21+ requirement. Customer does NOT choose a password.
- **After registration:**
  1. API creates ClpCustomer with status `pending` and generates a temporary password
  2. System sends **welcome/verification email** from `support@claritylabsusa.com` with temp password + email verification link
  3. Customer clicks verification link → email confirmed, status changes to `verified`
  4. Customer logs in with temp password → **forced password change** on first login (clarity-ops already has `must_change_password` field + `force-change-password.blade.php` view)
  5. After setting new password → full shop access
- **Returning users:** Login form (email + password)
- After successful auth → proceed to shop

**New files:**
- `shop/gate/age-verify.php` — age verification modal/page
- `shop/gate/sign-in.php` — combined login + register page (login by default, "Sign up Here" for new users)
- `includes/access-guard.php` — checks both age cookie AND auth session; redirects to appropriate gate if either fails. Included at top of ALL shop pages.

### 1.3 Product Catalog from API

**Product data is fully driven by clarity-ops:**
- **Images & COAs:** Pulled from ops via API `mediaPackage` (stored in Cloudflare R2 at `pub-ff60dc038f7644d1afd85fa7910382f3.r2.dev`). No local image files — all product images, gallery photos, and COA PDFs come from ProductMedia records in ops.
- **Size variants:** Each compound can have multiple ClpProduct rows (one per size: 10mg, 30mg, 50mg, etc.). The product page dynamically shows ONLY the sizes that exist in ops. If a compound only has a 10mg record, only 10mg shows. If it has 10mg/30mg/50mg, all three appear as selectable options with their respective pricing.
- **Pricing:** Retail price, sale price, hard cost all from ClpProduct fields per size variant.
- **Stock:** `vials_on_hand` from ClpProduct drives availability per size.

**Admin notification for unlisted products (in clarity-ops):**
- New Filament notification/widget on the ops dashboard: alerts admin when a ClpProduct has `vials_on_hand > 0` but `website_live = false` (or equivalent flag)
- "You have X products with inventory that are not published on the website" — links to those products for quick publishing
- This ensures no sellable inventory sits hidden from the storefront

**Modified files:**
- `includes/product-data.php` — replace hardcoded array with API call via `ClarityApiClient::getProducts()`. Merge with local content file for marketing copy. Keep static fallback if API is down.
- `includes/product-data-static.php` — renamed copy of current hardcoded array (fallback)
- `includes/product-content.php` — rich marketing content (why_cards, research_apps, protocol_context) keyed by slug, kept locally since this isn't in the API
- Product listing page — shows products from API with R2 image URLs, stock badges
- Product detail page — shows all available sizes from API, images/COA from `mediaPackage`, "Add to Cart" per selected size
- `includes/header.php` — add cart icon with count badge, login/account link

### 1.4 Cart System
**New files:**
- `cart.php` — cart page (line items, qty selector, subtotal, checkout button)
- `php/cart-actions.php` — AJAX endpoint for add/remove/update (modifies `$_SESSION['cart']`, validates via API)

**Modified files:**
- `products/product-template.php` — "Add to Cart" replaces the 3-step order modal
- `js/main.js` — replace order modal code (~lines 150-340) with cart AJAX handlers

### 1.5 Customer Auth
Auth is handled by the Gate (1.2) for shop access. Additional account management pages:
**New files:**
- `shop/account/forgot-password.php` — password reset request
- `shop/php/auth-actions.php` — AJAX handler for login/register/logout (calls clarity-ops API, manages session)

### 1.6 Checkout Flow
**New files:**
- `checkout.php` — multi-step: Contact → Shipping (EasyPost rates) → Payment (Stripe Elements) → Confirmation
- `php/checkout-actions.php` — AJAX handler for address validation, shipping rates, tax calc, order placement
- `js/checkout.js` — Stripe Elements integration + checkout step logic
- `order-confirmation.php` — post-purchase confirmation page

**Deprecated:**
- `php/order-mailer.php` — replaced by API order creation (clarity-ops sends emails)

### 1.7 API Gaps (endpoints to add in clarity-ops)
| Endpoint | Purpose |
|---|---|
| `POST /api/v1/shipping/rates` | Get EasyPost rates for address + items |
| `POST /api/v1/tax/calculate` | Calculate tax for address + subtotal |
| `POST /api/v1/address/verify` | Verify/correct shipping address |
| `GET /api/v1/products/{slug}` | Slug-based lookup (currently SKU-only) |
| `GET /api/v1/auth/verify/{token}` | Email verification link handler |
| `POST /api/v1/auth/change-password` | Forced password change on first login |
| `POST /api/v1/auth/forgot-password` | Send password reset email |
| `POST /api/v1/auth/reset-password` | Reset password with token |

**Filament changes needed in clarity-ops:**
- Dashboard widget: "Unlisted Products with Inventory" — shows ClpProducts where `vials_on_hand > 0` AND `website_live = false`, with quick-publish action
- Ensure `ClpProduct.website_live` flag exists and is togglable in ClpProductResource
- API product endpoints should only return products where `website_live = true` and `is_active = true`

---

## Phase 2: Customer Experience (needed for launch)

### 2.1 Customer Dashboard
**New files:**
- `account/index.php` — dashboard (recent orders, quick links)
- `account/orders.php` — full order history from API
- `account/order-detail.php` — single order with tracking links
- `account/addresses.php` — saved shipping/billing addresses

### 2.2 Transactional Emails
No PHP site changes — handled entirely by clarity-ops:

**Email sender addresses (Zoho SMTP):**
- `support@claritylabsusa.com` — Welcome/verification emails, password resets, support replies, account notifications
- `orders@claritylabsusa.com` — Order confirmation, shipping confirmation + tracking, delivery confirmation

**Emails:**
- Order confirmation from `orders@claritylabsusa.com` (triggered on `POST /orders`)
- Shipping confirmation + tracking from `orders@claritylabsusa.com` (triggered by EasyPost webhook)
- Delivery confirmation from `orders@claritylabsusa.com`
- Welcome/verification from `support@claritylabsusa.com` (triggered on registration)
- Password reset from `support@claritylabsusa.com`

**clarity-ops config:** Already supports multiple mailers in `config/mail.php` — `MAIL_ORDERS_*` and `MAIL_SUPPORT_*` env vars for separate Zoho SMTP credentials per sender address.

### 2.3 Pricing Integration
- API response already includes retail_price, sale_price on ClpProduct
- Display tiered/volume pricing on product detail pages
- Show "From $X.XX" on product cards

### 2.4 Inventory Guardrails
- Stock badges on shop.php (In Stock / Low Stock / Out of Stock)
- Block add-to-cart for out-of-stock items
- Re-validate stock at checkout before order placement
- Handle race conditions (API locks stock during order creation)

---

## Phase 3: Growth & Compliance (post-launch)

### 3.1 Discount/Promo Codes
- Coupon input on checkout page
- New API endpoint: `POST /api/v1/coupons/validate`
- CouponService already exists in clarity-ops

### 3.2 SEO & Performance
- OG/Twitter meta tags in `includes/head.php`
- JSON-LD Product schema on detail pages
- `sitemap.php` generating XML from API product list
- Image optimization (R2 CDN URLs)

### 3.3 Compliance
- FDA disclaimer on product pages and footer
- Terms of service, privacy policy, refund policy pages
- Age verification gate (already partially exists)
- Supplement Facts display format

### 3.4 Analytics & Tracking (Microsoft Clarity + GA4)

**Microsoft Clarity (free — heatmaps, session recordings, behavior):**
- Add Clarity tracking script to `includes/head.php` (one JS snippet)
- Heatmaps: see where users click, scroll, and engage on every page
- Session recordings: replay individual customer journeys through the shop
- AI insights: auto-surfaces dead clicks, rage clicks, checkout friction
- Zero cost, unlimited traffic

**Google Analytics 4 (free — conversions, funnels, attribution):**
- GA4 tag in `includes/head.php`
- E-commerce events: `view_item`, `add_to_cart`, `begin_checkout`, `add_shipping_info`, `add_payment_info`, `purchase`
- Funnel analysis: see where customers drop off in the checkout flow
- Revenue tracking: total sales, average order value, top products

**Facebook Pixel / Meta CAPI (if running ads):**
- Track purchase conversions for ad optimization
- Server-side CAPI via clarity-ops API for reliability

**What gets tracked:**
- Every page view, product view, search
- Cart additions/removals
- Checkout steps completed vs. abandoned
- Successful purchases with order value
- User scroll depth, click patterns, time on page
- Registration funnel (age gate → sign-up → verification → first purchase)

### 3.5 Affiliate Program (built but disabled until ops support is ready)

**On the shop site (build now, disable until ops integration exists):**
- `shop/affiliate/` — affiliate signup page: name, email, website/social, how they plan to promote
- `shop/affiliate/dashboard.php` — affiliate dashboard (hidden/disabled): referral link, click stats, commission earned, payout history
- Affiliate tracking: `?ref=AFFILIATE_CODE` URL parameter → stored in session/cookie (30-day attribution window)
- At checkout, `affiliate_code` is passed with the order (ClpCustomerOrder already has an `affiliate_code` field)
- All affiliate pages show "Coming Soon" or are hidden from nav until ops backend is ready

**Ops backend needed later:**
- Affiliate management panel in Filament (approve/reject applications, view performance)
- Commission calculation (clarity.php config already has `affiliate_commission = 10%`)
- Payout tracking and reporting
- API endpoints: `POST /api/v1/affiliate/apply`, `GET /api/v1/affiliate/stats`, etc.

### 3.6 Support Ticket System (Customer-Facing)

clarity-ops already has a full ticket system (`ClpTicket`, `ClpTicketComment`, `PublicTicketController`). Bring it to the storefront:

**New pages on shop site:**
- `shop/support/index.php` — "Need Help?" page with ticket submission form
- `shop/support/ticket.php` — View/reply to an existing ticket (via token link from email)

**Ticket submission form fields:**
- Subject, Description, Type dropdown (bug/question/other), Priority auto-set to `normal`
- If logged in: auto-fills name/email from customer session
- If not logged in: name + email fields required
- Optional: screenshot upload (stored in R2)

**Flow:**
1. Customer submits ticket → `POST /api/v1/support/ticket` → creates `ClpTicket` with `source='website'`
2. clarity-ops sends confirmation email from `support@claritylabsusa.com` with a token-based link
3. Customer clicks link → `shop/support/ticket.php?token=xxx` → sees ticket status + conversation
4. Customer can reply → `POST /api/v1/support/ticket/{token}/reply`
5. Admin responds in Filament → customer gets email notification with reply + link back

**Also useful for:**
- Customer reports an error during checkout → suggest "Submit a ticket" with pre-filled context
- Customer has a product idea or feedback → ticket with type `feature`
- Any error page (500, payment failed) → "Having trouble? Contact support" link to ticket form

**API endpoints needed:**
- `POST /api/v1/support/ticket` — already exists
- `GET /api/v1/support/ticket/{token}` — view ticket by public token (needs to be added)
- `POST /api/v1/support/ticket/{token}/reply` — reply to ticket (needs to be added)

### 3.7 Product Request Form ("Can't Find What You're Looking For?")

The shop only lists a curated selection — not everything the supplier can provide. This form lets customers request compounds not currently on the site.

**Placement:**
- Bottom of the shop/product listing page — below the product grid
- Search results page when no results found — "We don't have that listed yet, but we may be able to source it"
- Optional link in the shop nav or footer: "Request a Product"

**Form fields:**
- Compound/product name (required)
- Desired quantity/size (optional)
- Intended research application (optional — helps prioritize)
- Customer name + email (auto-filled if logged in)
- Additional notes (optional)

**Flow:**
1. Customer submits form → `POST /api/v1/support/ticket` with `type='product_request'`
2. Creates a `ClpTicket` in ops with `source='website'`, type `product_request`
3. Admin sees it in Filament ticket queue — can check with supplier, respond to customer
4. If product gets added to the catalog later, admin can notify the requesting customer

**Why this matters:**
- Keeps the shop clean and curated (not overwhelming)
- Captures demand signals — know what customers actually want
- Drives repeat visits ("we sourced what you asked for!")
- Builds customer trust ("they actually listen")

**New in clarity-ops:**
- Add `product_request` to the `ClpTicket` type enum
- Optional: Filament widget showing "X product requests this month" with most-requested compounds

### 3.8 Shipping Rules
- Free shipping threshold display (handled by API response)
- Restricted zone enforcement (API rejects invalid addresses)

---

## Repo File Structure (after Phase 1)

Same repo, two document roots. CloudPanel points:
- `claritylabsusa.com` → repo root `/`
- `shop.claritylabsusa.com` → repo subfolder `/shop/`

```
Clarity-Labs-USA-PHP-Site/
│
│── (MAIN SITE — claritylabsusa.com document root)
├── index.php                      # Homepage (unchanged)
├── about.php                      # About (unchanged)
├── contact.php                    # Contact (unchanged)
├── faq.php                        # FAQ (unchanged)
├── css/
│   └── styles.css                 # EXTENDED: shared design system + cart/checkout/account styles
├── js/
│   └── main.js                    # MODIFIED: shared interactions
├── images/                        # Product images (unchanged, also served via R2)
├── includes/
│   ├── head.php                   # MODIFIED: add shared meta, scripts
│   ├── header.php                 # MODIFIED: "Shop" links to shop.claritylabsusa.com
│   ├── footer.php                 # MODIFIED: legal links
│   ├── api-client.php             # NEW: ClarityApiClient class
│   ├── session.php                # NEW: Session init + cart helpers
│   ├── auth-guard.php             # NEW: Auth redirect guard
│   ├── csrf.php                   # NEW: CSRF token helpers
│   ├── product-data.php           # MODIFIED: API-sourced product data
│   ├── product-data-static.php    # RENAMED: fallback hardcoded data
│   └── product-content.php        # NEW: rich marketing copy by slug
├── config/
│   ├── config.php                 # NEW: Site configuration (reads .env)
│   └── .env                       # NEW: API keys (gitignored)
│
│── (SHOP SITE — shop.claritylabsusa.com document root)
├── shop/
│   ├── index.php                  # Product listing (moved from shop.php)
│   ├── product.php                # Product detail (moved from products/)
│   ├── cart.php                    # NEW: Shopping cart page
│   ├── checkout.php               # NEW: Multi-step checkout
│   ├── order-confirmation.php     # NEW: Post-purchase confirmation
│   ├── js/
│   │   └── checkout.js            # NEW: Stripe Elements + checkout logic
│   ├── php/
│   │   ├── cart-actions.php       # NEW: Cart AJAX handler
│   │   ├── auth-actions.php       # NEW: Login/register/logout handler
│   │   └── checkout-actions.php   # NEW: Checkout AJAX handler
│   └── account/
│       ├── index.php              # NEW: Customer dashboard
│       ├── login.php              # NEW: Login page
│       ├── register.php           # NEW: Registration page
│       ├── forgot-password.php    # NEW: Password reset
│       ├── orders.php             # NEW: Order history
│       ├── order-detail.php       # NEW: Single order + tracking
│       └── addresses.php          # NEW: Saved addresses
```

Shop pages reference shared assets from the main site using relative paths (e.g., `../css/styles.css`, `../includes/header.php`).

---

## Centralized Company Config (clarity-ops `config/clarity.php`)

All outbound communications (emails, receipts, footers, legal text) MUST pull from the centralized config — never hardcode company info:

```php
// config/clarity.php (already exists in clarity-ops)
'company' => [
    'name' => 'Clarity Labs USA',
    'address_line1' => '5441 South Macadam Avenue #5835',
    'city' => 'Portland',
    'state' => 'Oregon',
    'zip' => '97239',
    'country' => 'United States',
    'email_orders' => 'orders@claritylabsusa.com',
    'email_support' => 'support@claritylabsusa.com',
    'website' => 'https://claritylabsusa.com',
],
'disclaimer' => 'Research Use Only. All products sold by ClarityLabsUSA are intended exclusively
for in vitro research and laboratory use by qualified professionals. They are not for human or
veterinary consumption, are not evaluated by the Food and Drug Administration, and are not
intended to diagnose, treat, cure, or prevent any disease or condition. By completing a purchase,
the buyer confirms they are 18 years of age or older and a qualified research professional
acting within applicable laws and regulations.',
```

**Usage rules:**
- Every email footer: `config('clarity.company.name')`, address, disclaimer
- Every email "from": `config('clarity.company.email_orders')` or `config('clarity.company.email_support')`
- Order confirmation, shipping notification, welcome email — all reference this config
- Product pages, checkout, footer — disclaimer text pulled from config (or mirrored on the PHP side)
- The PHP storefront should have a matching config file that mirrors these values for use in its own templates

---

## Security

- API key in `.env` outside document root, never in git
- CSRF tokens on all POST forms
- Secure session cookies (httponly, secure, samesite=Strict)
- Rate limiting on auth endpoints (5 attempts / 15 min per IP)
- Stripe.js only — PHP never touches card numbers
- Input sanitization on all user-facing forms
- HTTPS enforced via nginx redirect + HSTS header

---

## Infrastructure Status (Completed)

- ✅ CloudPanel: `claritylabsusa.com` (site user: `claritylabsusa`) — created
- ✅ CloudPanel: `shop.claritylabsusa.com` (site user: `claritylabsusa-shop`) — created
- ✅ CloudPanel: `ops.claritylabsbio.com` (site user: `clarityops`) — existing
- ✅ Cloudflare: `claritylabsusa.com` added, DNS active, proxied (orange cloud)
- ✅ DNS: A records for `@`, `www`, `shop` → `187.124.228.193`
- ✅ SSL: Let's Encrypt on both sites in CloudPanel + Cloudflare Full (Strict)
- ✅ Zoho email records preserved (MX, SPF, DKIM, DMARC)
- ✅ Website repo cloned to VPS: `/home/claritylabsusa/htdocs/claritylabsusa.com/` (master branch)
- ✅ Main site live at `https://claritylabsusa.com`
- ⬜ Shop site: `/home/claritylabsusa-shop/htdocs/shop.claritylabsusa.com/` — needs setup

**VPS Document Roots:**
- Main site: `/home/claritylabsusa/htdocs/claritylabsusa.com/`
- Shop site: `/home/claritylabsusa-shop/htdocs/shop.claritylabsusa.com/`
- Ops admin: `/home/clarityops/htdocs/ops.claritylabsbio.com/`

## Existing V1 API (Already Built in clarity-ops)

The clarity-ops app already has a public REST API — this is the bridge between the storefront and backend:

**Product Endpoints (X-API-Key header):**
- `GET /api/v1/products` — Paginated list (filters: category, search, per_page)
- `GET /api/v1/products/{sku}` — Product detail with media, tags, related
- `GET /api/v1/products/{sku}/availability` — Stock check
- `GET /api/v1/categories` — Category list with product counts

**Customer Auth Endpoints (X-API-Key header):**
- `POST /api/v1/auth/register` — Create customer (age 18+, research confirmation)
- `POST /api/v1/auth/verify` — Email verification
- `POST /api/v1/auth/login` — Login → returns 30-day encrypted Bearer token
- `GET /api/v1/auth/me` — Get current customer (Bearer token)
- `POST /api/v1/auth/logout` — Logout (Bearer token)

**Order Endpoints:**
- `POST /api/v1/orders/validate` — Validate items + pricing + stock
- `POST /api/v1/orders` — Create order
- `GET /api/v1/orders` — Customer order history (Bearer token)
- `GET /api/v1/orders/{id}` — Order detail (Bearer token)

**Support:**
- `POST /api/v1/support/contact` — Contact form
- `POST /api/v1/support/ticket` — Create support ticket

**Webhook:**
- `POST /api/webhooks/easypost` — Tracking updates (HMAC validated)

**API Auth Mechanism:**
- Public endpoints: `X-API-Key` header (stored in `api_keys` table)
- Customer endpoints: `Authorization: Bearer {token}` (encrypted with customer_id + expiry)
- Middleware: `api.key` + `customer.token`

## Local Development Setup

**For Claude Code to work on both projects, have two project windows:**

1. **Website project:** Open Claude Code in `H:\OneDrive\OneDrive - Evo Tech\Websites\Clarity Labs USA\Clarity-Labs-USA-PHP-Site`
2. **clarity-ops project:** Open Claude Code in `H:\Projects\clarity-ops`

**Or** use a single workspace with both folders. When asking Claude to work on something, specify which project (e.g., "in the website repo, add..." or "in clarity-ops, add an endpoint for...").

**Key paths in clarity-ops:**
- Models: `app/Models/` (ClpProduct, ClpCustomer, ClpCustomerOrder, etc.)
- API Controllers: `app/Http/Controllers/Api/V1/`
- Services: `app/Services/` (ShippingRateService, TaxCalculator, CouponService, etc.)
- API Routes: `routes/api.php`
- Config: `config/clarity.php`, `config/easypost.php`
- Middleware: `app/Http/Middleware/VerifyApiKey.php`, `VerifyCustomerToken.php`

**Key paths in website:**
- Pages: root level (`index.php`, `shop.php`, `about.php`, etc.)
- Product detail: `products/index.php` → `products/product-template.php`
- Shared includes: `includes/` (header, footer, head, product-data)
- Styles: `css/styles.css`
- Scripts: `js/main.js`
- Email handlers: `php/order-mailer.php`, `php/contact-mailer.php`

---

## Pre-Build Checklist (Do Before Coding Starts)

- [ ] **Zoho App Passwords** — Generate app-specific passwords in Zoho Mail for SMTP sending:
  - `orders@claritylabsusa.com` — used for order confirmation, shipping notifications, delivery confirmation
  - `support@claritylabsusa.com` — used for welcome/verification emails, password resets, support ticket replies
  - Go to: Zoho Mail → Settings → Security → App Passwords → Generate new password for each
  - These go into the clarity-ops `.env` file as `MAIL_ORDERS_PASSWORD` and `MAIL_SUPPORT_PASSWORD`
- [ ] **API Key** — Generate an API key in clarity-ops Filament admin (or via tinker) for the storefront to use. Store in the website's `.env` as `CLARITY_API_KEY`
- [ ] **Payment Method** — TBD (ACH / Zelle / Venmo — not Stripe cards). Checkout payment step left as placeholder until decided.
- [ ] **Clone shop site on VPS** — Set up `/home/claritylabsusa-shop/htdocs/shop.claritylabsusa.com/` with the shop files
- [ ] **Verify Cloudflare DNS** — Confirm `shop.claritylabsusa.com` resolves and loads (currently shows default CloudPanel page)

---

## Build Log & Handoff Document

**Single plan file rule:** This plan lives in ONE file only. When the plan changes, we update this file — no duplicates, no versioned copies. When implementation starts, copy this file to the project repo at `.claude/STOREFRONT-PLAN.md` so any Claude instance on either project can find it. Keep both in sync — but there is only ONE plan, ONE build log.

Maintain a **living build log** section (at the bottom of this same file) that any Claude instance can read to understand:

1. **What has been built so far** — every feature, file created, file modified, with dates
2. **Deployment flow** — exact steps to push changes live:
   - Commit and push to GitHub from local
   - SSH into VPS (`ssh root@187.124.228.193`)
   - Website: `cd /home/claritylabsusa/htdocs/claritylabsusa.com && git pull`
   - Shop: `cd /home/claritylabsusa-shop/htdocs/shop.claritylabsusa.com && git pull`
   - Ops: `cd /home/clarityops/htdocs/ops.claritylabsbio.com && git pull && php artisan optimize:clear`
3. **Errors encountered and how they were fixed** — with context so the same mistake isn't repeated
4. **Current state** — what's working, what's in progress, what's next
5. **Key decisions made** — architecture choices and why
6. **API keys / env vars needed** — not the values, but which ones and where they go

**Format:** Chronological entries, newest at top. Each entry includes:
```
### [Date] — [What was done]
**Files changed:** list
**Errors hit:** description + fix
**Deployed:** yes/no
**Notes:** anything the next Claude needs to know
```

This file is the single source of truth. Any new Claude session should read it first before making changes.

---

## Verification (Phase 1 end-to-end test)

1. `shop.claritylabsusa.com` loads with SSL in CloudPanel
2. Shop page loads products from API (compare with Filament admin)
3. Product detail page shows API pricing + local marketing content
4. Add to cart → cart page shows item → update qty → remove
5. Register new customer → verify in clarity-ops admin
6. Login → session established → protected pages accessible
7. Checkout: enter address → get shipping rates → select rate → enter card (Stripe test) → place order
8. Order appears in clarity-ops Filament admin
9. Customer receives confirmation email
10. Order history shows in customer dashboard
11. Mobile responsive test
12. Security: CSRF rejection, no API key in browser, auth-guarded pages redirect

---

## Build Log

### 2026-03-21 — Infrastructure Setup & Domain Configuration
**What was done:**
- Created `claritylabsusa.com` PHP site in CloudPanel (site user: `claritylabsusa`)
- Created `shop.claritylabsusa.com` PHP site in CloudPanel (site user: `claritylabsusa-shop`)
- Added `claritylabsusa.com` to Cloudflare (same account as R2 bucket)
- Updated GoDaddy nameservers to Cloudflare
- Set up DNS A records: `@`, `www`, `shop` → `187.124.228.193` (all proxied/orange cloud)
- Removed old GoDaddy A record (`160.153.0.106`)
- Preserved Zoho email records (MX, SPF, DKIM, DMARC, Zoho verification)
- Removed unused `_domainconnect` CNAME
- Installed Let's Encrypt SSL on both sites in CloudPanel
- Set Cloudflare SSL to Full (Strict)
- Cloned website repo to VPS at `/home/claritylabsusa/htdocs/claritylabsusa.com/`
- Switched to `master` branch (default was `main` which only had README)
- Fixed file ownership: `chown -R claritylabsusa:claritylabsusa`
- Main site now live at `https://claritylabsusa.com`

**Errors hit:**
1. `git clone` path typo — used `claritylabusa` instead of `claritylabsusa`. Fix: checked `/home/` with `ls | grep clarity`
2. "destination path not empty" — `.well-known` folder from Let's Encrypt cert validation. Fix: `rm -rf .well-known` before clone
3. Let's Encrypt cert validation failed — Cloudflare proxy was blocking ACME challenge (error 526). Fix: temporarily set DNS records to "DNS only" (grey cloud), issued cert, then re-enabled proxy (orange cloud)
4. Cloned `main` branch (only README) instead of `master` (actual code). Fix: `git checkout master`
5. 403 Forbidden after clone — files owned by root, nginx runs as `claritylabsusa`. Fix: `chown -R claritylabsusa:claritylabsusa /home/claritylabsusa/htdocs/claritylabsusa.com/`
6. Git "dubious ownership" error — root running git in claritylabsusa-owned dir. Fix: `git config --global --add safe.directory /home/claritylabsusa/htdocs/claritylabsusa.com`

**Deployed:** Yes — main site live
**Notes:**
- PHP 8.3 selected (matches clarity-ops)
- CloudPanel "Generic" PHP app type (not Laravel/WordPress)
- Repo is public on GitHub (easier VPS deploys, no secrets in repo)
- `git pull` deploy flow: SSH → cd to document root → `git pull`
- Shop site still has default CloudPanel index.php — needs setup next
