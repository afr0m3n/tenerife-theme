# Amarilla Tenerife

A custom WordPress block theme for the Amarilla Car Hire website in Tenerife. The theme manages the vehicle catalogue, block-based homepage, Contact Form 7 inquiry flow, operational content, blog, and structured data.

This document describes the current state of theme version 1.2.2. Git retains the change history.

## Current environment and requirements

The metadata in `style.css` declares:

- theme version `1.2.2`,
- WordPress `6.4` or newer,
- tested up to the WordPress `7.0` series,
- PHP `8.0` or newer.

The current project runs on WordPress `7.0.1`. Contact Form 7 provides the main production inquiry form. Polylang is an optional integration for multilingual content.

The theme is a Full Site Editing/block theme: global styles are stored in `theme.json`, templates in `templates/`, template parts in `parts/`, and pre-built sections in `patterns/`.

## Main features

- vehicle catalogue implemented as the `vehicle` CPT with the `vehicle_category` taxonomy,
- vehicle detail pages at `/vozidla/{slug}/` and the archive at `/vozovy-park/`,
- a shared server-side card renderer and dynamic Gutenberg blocks,
- manual selection of up to three featured homepage vehicles with fallback logic,
- vehicle galleries using the WordPress core lightbox,
- base and seasonal prices plus internal blackout notes,
- the main inquiry workflow through CF7 at `/nezavazna-poptavka/`,
- homepage content and operational details managed through the Customizer,
- an optional Polylang language switcher,
- blog templates and related posts,
- JSON-LD `AutoRental`, `ItemList`, `Product`, `Offer`, `Article`, and `BreadcrumbList` nodes,
- self-hosted Fraunces and DM Sans variable fonts.

## Theme architecture

The theme combines standard block templates with PHP renderers. Dynamic CPT and Customizer data enters the HTML through custom dynamic blocks, shortcodes, and PHP patterns.

Custom dynamic blocks:

- `amarilla/vehicle-card` renders one card in a Query Loop context,
- `amarilla/featured-vehicles` renders the complete featured-vehicle section on the homepage.

Both use the shared `amarilla_render_vehicle_card()` renderer, so homepage and archive cards follow the same data and presentation flow.

Registered patterns form the default homepage: hero, trust strip, featured vehicles, “Why choose us”, Tenerife tips, locations, and the final CTA.

## Vehicles

### CPT, taxonomy, and URLs

- CPT: `vehicle`
- taxonomy: `vehicle_category`
- detail: `/vozidla/{slug}/`
- archive: `/vozovy-park/`

The CPT supports title, block editor, featured image, excerpt, ordering, and custom fields. The taxonomy is hierarchical. During initialization, the theme adds any missing default categories: `ekonomy`, `family`, `suv`, `cabrio`, `premium`, and `minivan`.

### Adding and editing vehicles

In the administration, open **Vozový park → Přidat nové** (Fleet → Add New). One record represents a vehicle model, not an individual physical car. Manual and automatic variants of the same model are stored in one record.

The main fields are:

| Field | Meta key | Usage |
|---|---|---|
| Tagline | `_vehicle_tagline` | card and detail |
| Seats | `_vehicle_seats` | card, detail, and schema |
| Doors | `_vehicle_doors` | card, detail, and schema |
| Available transmissions | `_vehicle_transmissions` | card, detail, schema, and CF7 |
| Fuel | `_vehicle_fuel` | detail and schema |
| Luggage | `_vehicle_luggage` | detail |
| Air conditioning | `_vehicle_ac` | detail |
| Base daily price | `_vehicle_price` | card and schema `Offer` |
| Label | `_vehicle_label` | card highlight |
| Rating | `_vehicle_rating` | optional `AggregateRating` |
| Rating count | `_vehicle_rating_count` | optional `AggregateRating` |

The vehicle title is used as both the option label and value in the CF7 select and as the key in the client-side transmission map. Published vehicle titles must therefore be unique.

Vehicle ordering uses `menu_order` from the post ordering panel; lower numbers are placed first.

### Transmissions

The current `_vehicle_transmissions` data model is an array with these allowed values:

- `manual`,
- `automatic`.

An administrator can select manual, automatic, or both variants. A regular save writes the new field and deletes the historical single-value `_vehicle_transmission` meta. The helper reads that legacy field only as a backward-compatible fallback for records that have not yet been migrated.

### Photos and gallery

The main photo is the standard featured image:

- on a card, it is displayed inside a 4:3 area with `object-fit: contain`, so CSS does not crop it,
- on the detail page, it preserves its natural aspect ratio, uses `object-fit: contain`, and has a maximum height.

A new `vehicle` record starts in the editor with an empty `core/gallery` block. Additional photos belong in this standard Gutenberg gallery.

The vehicle detail renderer:

- separates top-level `core/gallery` blocks from text content,
- for backward compatibility, also groups top-level `core/image` blocks without a custom link,
- excludes the featured image and duplicate attachment IDs,
- enables the WordPress core lightbox for images without a custom link,
- preserves images with a custom link in their original content position.

`assets/js/vehicle-gallery.js` only calculates natural aspect ratios for the gallery layout. WordPress core provides the lightbox itself.

### Seasonal pricing and availability

The **Sezónní ceny a dostupnost** (Seasonal pricing and availability) meta box stores:

- pricing periods as JSON in `_vehicle_pricing_periods` (`start`, `end`, `price`, and an internal `label`),
- blackout rows in `_vehicle_blackouts`.

If at least one pricing period exists, the card displays “od … €” (“from … €”) using the lowest numerically recognized value across the base price and all periods. The same lowest price is used in the detail-page schema.org `Offer`. Without seasonal periods, the card uses the original `_vehicle_price` text; if no price is set, it displays “Poptat termín” (“Ask about availability”).

Blackouts are not a booking engine. The current frontend and CF7 integration do not evaluate them automatically or include them in email; they serve as internal administration information for a future or custom integration.

### Duplication

The **Duplikovat** (Duplicate) action in the vehicle list creates a new draft named `{original title} - kopie` and opens its editor. It copies the content, excerpt, featured image and other regular metadata, ordering, and all assigned taxonomies.

It does not copy operational system metadata or the homepage featured state:

- `_amarilla_home_featured`,
- `_amarilla_home_featured_order`.

Review and rename the copy before publishing it.

### Homepage featured vehicles

The **Úvodní stránka** (Homepage) meta box uses:

- `_amarilla_home_featured` for inclusion,
- `_amarilla_home_featured_order` for manual ordering.

The `amarilla/featured-vehicles` block displays at most three published vehicles. It first takes selected vehicles in manual order, then orders by `menu_order`, title, and ID. If fewer than three vehicles are selected, it fills the remaining positions with other published vehicles ordered by `menu_order`, title, and ID, without duplicates.

The homepage `ItemList` schema uses the same resolver.

### Archive and cards

The default `templates/archive-vehicle.html` uses a Query Loop for `vehicle` and the `amarilla/vehicle-card` block. A filter sets `posts_per_page = -1` only for this frontend vehicle archive Query Loop, so `/vozovy-park/` displays all published vehicles without pagination and does not affect other Query Loops or taxonomy archives.

Depending on available data, a card displays the featured image, label or first category, title, tagline, seats, transmissions, doors, and price.

## Vehicle inquiry and Contact Form 7

The main production workflow uses:

```text
/nezavazna-poptavka/
```

The target CF7 form is not identified by a hardcoded production ID. The theme looks for it in this page's content as a `contact-form-7/contact-form-selector` block or a `[contact-form-7 ...]` shortcode. A fallback to a published form with the exact title `Nezávazná poptávka` is also retained.

The form must contain two selects:

```text
[select* select-auto "— Vyberte vůz —"]
[select* transmission "Je mi to jedno" "Manuál" "Automat"]
```

These are the actual Czech values used by the production form: “Je mi to jedno” means no transmission preference, “Manuál” means manual, and “Automat” means automatic. Their static options are only a minimal valid configuration. The theme replaces the options while rendering the target form:

- `select-auto` receives all published vehicles ordered by `menu_order` and title,
- the CPT title is used directly as both the option value and label,
- `transmission` is derived from `_vehicle_transmissions`,
- when only one variant is available, only that variant is offered,
- when two or no known variants are available, the universal options “Je mi to jedno”, “Manuál”, and “Automat” remain available.

The vehicle detail page links to:

```text
/nezavazna-poptavka/?requested_vehicle=<POST_ID>
```

The theme verifies that the ID belongs to a published `vehicle`, preselects its title, and prepares the matching transmission options. The `?vehicle=` parameter is not used because `vehicle` is the public WordPress query variable for this CPT and collides with the main query.

A small `amarilla-vehicle-transmission-data` JSON object is injected into the form HTML. `assets/js/theme.js` uses it to update the transmission options after a manual vehicle selection and after the form is reset. The integration uses neither AJAX nor jQuery.

The CF7 mail template must include:

```text
[select-auto]
[transmission]
```

The vehicle list is not maintained manually in the CF7 tag.

### CF7 on the homepage

The hero pattern renders `[amarilla_booking_widget]` only when the booking widget is enabled in the Customizer. If `amarilla_home_booking_form_shortcode` contains a valid standalone CF7 shortcode and Contact Form 7 is active, that form is rendered in a styled wrapper.

Without a valid CF7 shortcode, a legacy GET widget targeting the contact page is available. The source also still contains the legacy internal form/CPT in `inc/inquiry-form.php` and the default `page-contact.html` template. These are not the main production inquiry workflow; new operational configuration should use `/nezavazna-poptavka/` and CF7.

## Homepage and Site Editor

The theme-file homepage baseline is `templates/front-page.html`. It consists of registered patterns and the dynamic featured block. The header, topbar, and footer are template parts in `parts/`.

Block templates, template parts, navigation, and global styles from `theme.json` can be managed in **Appearance → Editor**. A template saved in the Site Editor becomes a `wp_template` database override and takes precedence over the same-named file in `templates/`.

The production homepage may legitimately have its own `front-page` override. Do not reset the entire homepage to the theme baseline without checking it first: a reset removes the database version and activates the current `templates/front-page.html` file. Before making changes, compare the live content, saved override, and theme-file baseline.

## Customizer

The theme registers an **Amarilla Tenerife** panel in the Customizer. With a block theme, the direct `/wp-admin/customize.php` URL may be the most reliable route if the menu item is not visible.

The Customizer manages data and content used by PHP patterns:

- branding: main logo width, light logo, and text fallback,
- topbar and language switcher,
- phone, email, address, opening hours, and WhatsApp,
- hero image, CTA, and optional homepage CF7 shortcode,
- trust strip,
- featured-vehicle section copy,
- “Why choose us” and “Tenerife tips” sections,
- final CTA,
- footer content and social profiles,
- up to six locations with address, hours, and GPS coordinates,
- blog header and the related-posts switch.

Set the standard custom logo through WordPress **Site Identity**. Colors, typography, spacing, and block layout belong in the Site Editor, not in the Amarilla panel.

Locations are rendered as a list and an OpenStreetMap iframe. The bounding box is derived from the configured coordinates; neither Google Maps nor custom map JavaScript is used.

## Multilingual support

The theme uses the `amarilla` text domain, registers selected operational strings with Polylang, and can use its API to render the topbar language switcher. Without active Polylang, only a static switcher fallback without real translation URLs is displayed.

Pages, posts, vehicles, and their taxonomies are translated as standard Polylang content. When `pll_get_post()` is available, the inquiry URL helper uses the translated `nezavazna-poptavka` page.

## SEO and schema.org

The theme injects one JSON-LD document with an `@graph` into `<head>`. It is not rendered in the administration or on 404 pages.

- `AutoRental` is the base node on frontend pages. It uses the site name, description, contact details, logo, social profiles, opening hours, and configured locations with address and GPS coordinates.
- `BreadcrumbList` is added to pages, vehicle detail pages, the vehicle archive, and individual blog posts.
- The homepage receives an `ItemList` with at most three vehicles from the same featured resolver as the homepage section.
- The vehicle archive receives an `ItemList` of all published vehicles ordered by `menu_order`.
- A vehicle detail page receives a `Product` with its image and available specifications. If a numeric price can be determined, an `Offer` with the lowest base/seasonal price is added. `AggregateRating` is added only when both a rating and a positive rating count are set.
- An individual blog post receives an `Article` with dates, author, and optional image.

Only enter genuine rating data.

## Blog

The blog uses the standard WordPress `post` post type:

- `templates/home.html` displays the nine latest posts per page in a three-column grid,
- `templates/archive.html` provides category, author, and date archives,
- `templates/single.html` displays the post, author, date, featured image, and estimated reading time,
- `[amarilla_reading_time]` calculates at least one minute at 200 words per minute,
- `[amarilla_related_posts]` can display up to three latest posts from the same categories below an article.

For a separate blog page, configure a static homepage and a posts page under the WordPress reading settings. The blog header and related posts are configured in the Customizer.

## Assets and fonts

The frontend loads `style.css`, `assets/css/theme.css`, and deferred `assets/js/theme.js` with the `AMARILLA_VERSION` cache-busting version. `assets/js/vehicle-gallery.js` is loaded only on vehicle detail pages.

Fraunces and DM Sans are variable WOFF2 fonts stored in `assets/fonts/` for latin and latin-ext. They are registered by `theme.json`; critical latin-ext faces are preloaded from the theme. The theme does not contact Google Fonts.

## Cache and operational notes

Production uses page caching (WP Fastest Cache). The dynamic vehicle list and transmission JSON map are part of the rendered CF7 form HTML, not a separate API request.

A page-cache purge may be needed after:

- publishing, hiding, or renaming a vehicle,
- changing `_vehicle_transmissions`,
- changing the CF7 tags or placement of the target form.

The same caution applies to homepage changes when a Site Editor template override exists. This README does not document configuration of the specific cache plugin.

## Development and deployment

Theme repository:

```text
/srv/apps/tenerife-theme
```

The DEV WordPress instance runs in the Docker stack at `/srv/stacks/tenerife-wp-dev`; the theme is mounted in the WordPress container at:

```text
/var/www/html/wp-content/themes/tenerife
```

After making changes, run at least:

```bash
git diff --check
git status --short --branch
```

Lint changed PHP files in the DEV container and verify the frontend on the DEV instance. Changed JavaScript can be syntax-checked with `node --check`.

The production deployment script defaults to a safe dry run:

```bash
./scripts/deploy-theme.sh
```

A production apply requires an explicit `--apply` and the confirmation `DEPLOY`. By default, it runs `scripts/backup-remote-theme.sh` before upload. Use `--delete` and `--no-backup` only after separate explicit approval.

## File structure

```text
amarilla-tenerife/
├── style.css                       metadata and entry stylesheet
├── theme.json                      global block-theme settings
├── functions.php                   bootstrap, assets, and includes
├── templates/                      theme-file block templates
├── parts/                          header, footer, and topbar
├── patterns/                       PHP homepage patterns
├── inc/
│   ├── vehicle-cpt.php             CPT, taxonomy, and vehicle metadata
│   ├── block-bindings.php          vehicle shortcodes, gallery, and cards
│   ├── featured-vehicles.php       homepage featured resolver and block
│   ├── vehicle-inquiry-cf7.php     main CF7 vehicle workflow
│   ├── seasonal-pricing.php        pricing periods and blackout metadata
│   ├── admin-vehicle-duplicate.php vehicle duplication in administration
│   ├── customizer.php              Amarilla panel and selective refresh
│   ├── content-shortcodes.php      dynamic header/footer components
│   ├── locations.php               locations and OpenStreetMap
│   ├── blog.php                    blog helpers and shortcodes
│   ├── polylang-compat.php         language switcher and strings
│   └── inquiry-form.php            legacy internal inquiry module
├── assets/
│   ├── css/                        frontend and editor styles
│   ├── js/                         frontend/editor scripts
│   ├── fonts/                      self-hosted WOFF2 fonts
│   └── images/                     static theme images
└── scripts/                        backup and SFTP deployment workflow
```

## License

GNU General Public License v2 or later.
