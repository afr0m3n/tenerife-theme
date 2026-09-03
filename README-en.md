# Amarilla Tenerife — WordPress Theme

A modern block theme for Amarilla Car Hire, a car rental company in Tenerife. Built for WordPress 6.4+, PHP 8.0+, and ready for the Polylang plugin for multilingual content.

## What's in the package

- Full Site Editing block theme — everything is editable in the admin
- Custom post type **Vehicles** + taxonomy **Categories** (Economy, SUV, Cabrio…)
- Pre-built **block patterns** — hero, fleet, why-us, tips, CTA
- Custom page templates: Home, Fleet, Vehicle Detail, About, Contact, Services, 404
- Fully translatable strings + Polylang-ready (CZ / EN / ES / DE)
- **schema.org** structured data (AutoRental) for SEO
- Lazy-loaded images, optimised font loading

## Installation

1. Download `amarilla-tenerife.zip`
2. In WordPress admin, go to **Appearance → Themes → Add New → Upload Theme**
3. Pick the ZIP file and click **Install Now**
4. Once installed, click **Activate**
5. On first activation, default vehicle categories are created automatically (Economy, Family, SUV, Cabrio, Premium, Minivan)

## First-time setup

### 1. Permalink structure
Go to **Settings → Permalinks** and select **Post name** (`/%postname%/`). If this is already set, just click **Save Changes** to refresh the URL rules for vehicles.

### 2. Create base pages
Create these pages (**Pages → Add New**) and assign the matching template in the right sidebar:

| Page name | Slug (URL) | Template |
|---|---|---|
| Home | `/` | (default) |
| About | `/o-nas/` | Stránka: O nás |
| Contact | `/kontakt/` | Stránka: Kontakt |
| Services | `/sluzby/` | Stránka: Služby |

Then in **Settings → Reading**:
- Your homepage displays → **A static page** → "Home"

### 3. Contact details
**Appearance → Customize → Kontaktní údaje** (Contact details) — phone, e-mail, address, WhatsApp. These are used in the footer and in SEO structured data.

### 4. Logo
Once the logo is ready:
- **Appearance → Customize → Site Identity → Select logo**
- After uploading a logo, the "Amarilla" text placeholder is hidden automatically
- Recommended size: 240 × 80 px (PNG or SVG)

## Adding a vehicle

1. **Vozový park (Fleet) → Add New**
2. Fill in:
   - **Title** — e.g. "Fiat Panda"
   - **Featured image** (right sidebar) — ideally 1600 × 1100 px
   - **Editor** — longer description shown on the detail page
   - **Category** (right sidebar) — pick from Economy/SUV/…
   - **Vehicle specifications** (below editor):
     - **Tagline** — short line under the title on the card (e.g. "Small, agile, perfect for the city")
     - **Seats** — number (1–9)
     - **Doors** — number (2–5)
     - **Transmission** — Manual / Automatic
     - **Fuel** — Petrol / Diesel / Hybrid / Electric
     - **Luggage** — text (e.g. "350 l" or "2 large suitcases")
     - **Air conditioning** — Yes / No
     - **Price per day** — text (e.g. "€25"). **If left empty, an "Inquire" button is shown instead of a price.**
     - **Label** — optional (e.g. "Most popular", "New", "20% off"). Labels appear in yellow, categories in grey.
3. Click **Publish**

## Vehicle ordering

Vehicles in the listing are ordered by the **Order** field (Page Attributes), found in the right sidebar under Page. Lower number = higher in the list.

## Multilingual setup (Polylang)

1. Install the **Polylang** plugin (free): **Plugins → Add New → Polylang**
2. After activation add languages: **Languages → Languages → Add new** (Čeština, English, Español, Deutsch)
3. Set Czech as the default
4. **Languages → Settings → URL modifications** — recommended:
   - Hide the language code in the URL for the default language (clean `/o-nas/` instead of `/cs/o-nas/`)
5. Each page and vehicle now has flag icons in the editor for adding translations
6. **Languages → String translations** — theme strings (buttons, eyebrows, etc.) for translation

## Contact form

We recommend **Contact Form 7** (free):

1. **Plugins → Add New** → search "Contact Form 7" → Install → Activate
2. **Contact → Contact Forms → Add New**
3. Use this template (paste into the "Form" field):

```
<label>Your name *
[text* your-name] </label>

<label>E-mail *
[email* your-email] </label>

<label>Phone
[tel your-phone] </label>

<label>Rental period (from – to)
[text your-dates] </label>

<label>Vehicle (if you have a specific one in mind)
[text vehicle] </label>

<label>Message
[textarea your-message] </label>

[submit "Send inquiry"]
```

4. The vehicle detail CTA uses the safe `?requested_vehicle=<ID>` parameter. In the dedicated “Nezávazná poptávka” CF7 form, the theme fills the existing `select-auto` select from published vehicles, preselects the matching model, and synchronizes the `transmission` field from vehicle metadata.
5. Insert the form shortcode `[contact-form-7 id="XXX"]` into the **Contact** page editor

## Customizer (Appearance → Customize)

- **Site Identity** — Logo, site title, site icon (favicon)
- **Kontaktní údaje (Contact details)** — Phone, e-mail, address, WhatsApp (custom section added by the theme)
- **Menus** — Main navigation
- **Colors and typography** — Editable via **Appearance → Editor (Site Editor) → Styles**

## Editing colors and fonts

**Appearance → Editor → Styles** — clicking the brush icon top-right opens the styles panel. You can change:
- Global colors
- Fonts
- Font sizes
- Spacing

## Theme files

```
amarilla-tenerife/
├── style.css                — theme metadata
├── theme.json               — global settings (colors, fonts)
├── functions.php            — main functions
├── README.md                — Czech docs
├── README-en.md             — this file
├── inc/
│   ├── vehicle-cpt.php      — Vehicle CPT registration + meta box
│   ├── block-bindings.php   — shortcodes for rendering vehicle data
│   ├── helpers.php          — helper functions + Customizer
│   └── polylang-compat.php  — Polylang compatibility
├── templates/               — page templates
├── parts/                   — header, footer, topbar
├── patterns/                — pre-built sections
├── assets/
│   ├── css/                 — stylesheets
│   ├── js/                  — JavaScript
│   └── images/              — theme images
└── languages/               — translations (.po, .mo files)
```

## FAQ

**How do I change the images in "Tips from Tenerife"?**
Edit patterns: **Appearance → Editor → Patterns → Tipy z Tenerife**, or edit the `patterns/tenerife-tips.php` file directly (replace the `<img src="...">` URLs).

**How do I change the hero image?**
Edit `patterns/hero.php` — find the `<img src="...">` line. Best practice: upload your own image to the Media Library and use its URL.

**The fleet section doesn't show up on the homepage.**
You need to add at least one vehicle (Vozový park → Add New). When there are no vehicles, the section shows a prompt to add some.

**Vehicle URLs are broken after activation.**
Go to **Settings → Permalinks** and click **Save Changes** to refresh the URL rules.

## Developer contact

For customisations or implementation questions, contact us.

---

**Version:** 1.0.0
**License:** GNU GPL v2 or later

---

## What's new in 1.2.0

- **Self-hosted fonts** — Fraunces & DM Sans bundled in `assets/fonts/` (GDPR compliance for EU operators; no `fonts.googleapis.com` connection)
- **Inquiry / booking form** — compact 4-field widget in hero (pickup date → return → location → vehicle class) + full form on `/kontakt/`, both wired to a new "Poptávky" CPT and email to the operator. WP nonce, honeypot, 60 s/IP rate limit, server-side date validation.
- **Seasonal pricing per vehicle** — repeatable period table (from–to, daily price, label) + blackout days. Fleet cards display "from XX €" when seasonal periods exist, lowest price flows into schema.org `Offer`.
- **Extended schema.org** — `@graph` with `AutoRental` (with all branches as `location[].geo`), per-vehicle `Product` + `Offer` + `AggregateRating`, `Article` for blog posts, and `BreadcrumbList` site-wide.
- **Pickup locations / map** — Customizer panel for up to 6 branches with GPS coords, rendered as OpenStreetMap iframe embed (no Google Maps, no token). Locations flow into both the inquiry form and the schema.
- **Blog** — `home.html`, `single.html`, `archive.html` templates, related-posts shortcode, reading-time estimate, Customizer panel for the archive header.

### Logo upload
Inside the Site Editor, the `[amarilla_logo]` shortcode block looks empty — that's expected. Upload your logo in **Appearance → Customize → Site Identity → Select logo** (`/wp-admin/customize.php`). The shortcode picks it up via `has_custom_logo()`.
