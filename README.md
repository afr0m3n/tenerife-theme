# Amarilla Tenerife

Vlastní WordPress block theme pro web autopůjčovny Amarilla Car Hire na Tenerife. Theme spravuje katalog vozidel, blokovou homepage, poptávkový tok přes Contact Form 7, provozní obsah, blog a strukturovaná data.

Tento dokument popisuje aktuální stav theme ve verzi 1.2.2. Historii změn uchovává Git.

## Aktuální prostředí a požadavky

Metadata v `style.css` deklarují:

- theme version `1.2.2`,
- WordPress `6.4` nebo novější,
- testováno do řady WordPress `7.0`,
- PHP `8.0` nebo novější.

Aktuální projekt je provozován na WordPress `7.0.1`. Contact Form 7 zajišťuje hlavní produkční poptávkový formulář. Polylang je volitelná integrace pro vícejazyčný obsah.

Theme je Full Site Editing/block theme: globální styly jsou v `theme.json`, šablony v `templates/`, template parts v `parts/` a předpřipravené sekce v `patterns/`.

## Hlavní funkce

- katalog vozidel jako CPT `vehicle` s taxonomií `vehicle_category`,
- detail vozidla na `/vozidla/{slug}/` a archiv na `/vozovy-park/`,
- společný serverový renderer karet a dynamické Gutenberg bloky,
- ruční výběr maximálně tří doporučených vozidel na homepage s fallbackem,
- galerie vozidel s WordPress core lightboxem,
- základní a sezónní ceny plus interní blackout poznámky,
- hlavní poptávkový workflow přes CF7 na `/nezavazna-poptavka/`,
- obsah homepage a provozní údaje spravované přes Customizer,
- volitelný Polylang přepínač jazyků,
- blogové šablony a související články,
- JSON-LD `AutoRental`, `ItemList`, `Product`, `Offer`, `Article` a `BreadcrumbList`,
- self-hostované variable fonty Fraunces a DM Sans.

## Architektura theme

Theme kombinuje standardní blokové šablony s PHP renderery. Dynamická data z CPT a Customizeru vstupují do HTML přes vlastní dynamické bloky, shortcodes a PHP patterns.

Vlastní dynamické bloky:

- `amarilla/vehicle-card` vykresluje jednu kartu v kontextu Query Loopu,
- `amarilla/featured-vehicles` vykresluje celou homepage sekci doporučených vozidel.

Oba používají společný renderer `amarilla_render_vehicle_card()`, takže karta na homepage a v archivu má stejný datový a prezentační tok.

Registrované patterns tvoří výchozí homepage: hero, trust strip, doporučená vozidla, „Proč si vybrat nás“, tipy, pobočky a závěrečné CTA.

## Vozidla

### CPT, taxonomie a URL

- CPT: `vehicle`
- taxonomie: `vehicle_category`
- detail: `/vozidla/{slug}/`
- archiv: `/vozovy-park/`

CPT podporuje název, blokový editor, náhledový obrázek, excerpt, pořadí a custom fields. Taxonomie je hierarchická. Theme při inicializaci doplňuje chybějící výchozí kategorie `ekonomy`, `family`, `suv`, `cabrio`, `premium` a `minivan`.

### Přidání a editace

V administraci otevřete **Vozový park → Přidat nové**. Jeden záznam představuje model auta, nikoli jednotlivý fyzický kus. Manuální a automatická varianta stejného modelu se evidují na jednom záznamu.

Vyplňují se zejména:

| Pole | Meta key | Použití |
|---|---|---|
| Krátký popis | `_vehicle_tagline` | karta a detail |
| Počet míst | `_vehicle_seats` | karta, detail, schema |
| Počet dveří | `_vehicle_doors` | karta, detail, schema |
| Dostupné převodovky | `_vehicle_transmissions` | karta, detail, schema a CF7 |
| Palivo | `_vehicle_fuel` | detail a schema |
| Kufr | `_vehicle_luggage` | detail |
| Klimatizace | `_vehicle_ac` | detail |
| Základní cena za den | `_vehicle_price` | karta a schema `Offer` |
| Štítek | `_vehicle_label` | zvýraznění karty |
| Hodnocení | `_vehicle_rating` | volitelné `AggregateRating` |
| Počet hodnocení | `_vehicle_rating_count` | volitelné `AggregateRating` |

Název vozidla se používá jako label i value v CF7 selectu a jako klíč klientské mapy převodovek. Názvy publikovaných vozidel proto musí být unikátní.

Pořadí vozidel určuje `menu_order` z panelu pořadí příspěvku; nižší číslo se řadí dříve.

### Převodovky

Aktuální datový model `_vehicle_transmissions` je pole s povolenými hodnotami:

- `manual`,
- `automatic`.

Správce může zaškrtnout manuál, automat nebo obě varianty. Při běžném uložení se zapisuje nové pole a historická single meta `_vehicle_transmission` se odstraní. Helper ji čte pouze jako backward-compatible fallback u dosud nemigrovaných záznamů.

### Fotografie a galerie

Hlavní fotografie je standardní featured image:

- na kartě je uvnitř poměru 4:3 zobrazena přes `object-fit: contain`, tedy bez CSS ořezu,
- na detailu zachovává přirozený poměr, používá `object-fit: contain` a má omezenou maximální výšku.

Nový záznam `vehicle` začíná v editoru prázdným blokem `core/gallery`. Další fotografie patří do této standardní Gutenberg galerie.

Detail vozidla:

- oddělí top-level `core/gallery` od textového obsahu,
- kvůli backward compatibility seskupí i top-level `core/image`, pokud nemá vlastní odkaz,
- vynechá featured image a opakované attachment ID,
- u obrázků bez vlastního odkazu zapne WordPress core lightbox,
- zachová obrázek s vlastním odkazem v původním obsahu.

`assets/js/vehicle-gallery.js` pouze dopočítává přirozené poměry stran pro layout galerie. Samotný lightbox zajišťuje WordPress core.

### Sezónní ceny a dostupnost

Meta box **Sezónní ceny a dostupnost** ukládá:

- cenová období jako JSON v `_vehicle_pricing_periods` (`start`, `end`, `price`, interní `label`),
- blackout řádky v `_vehicle_blackouts`.

Pokud existuje alespoň jedno cenové období, karta zobrazí „od … €“ podle nejnižší číselně rozpoznané ceny ze základní ceny a všech období. Stejná nejnižší cena vstupuje na detailu do schema.org `Offer`. Bez sezónních období karta používá původní text `_vehicle_price`; pokud není cena vyplněna, zobrazí „Poptat termín“.

Blackouty nejsou rezervační engine. Aktuální frontend ani CF7 je automaticky nevyhodnocují a neposílají do e-mailu; slouží jako interní admin informace pro budoucí nebo vlastní integraci.

### Duplikace

Akce **Duplikovat** v seznamu vozidel vytvoří nový koncept s názvem `{původní název} - kopie` a otevře jeho editor. Kopíruje obsah, excerpt, featured image a ostatní běžná metadata, pořadí a všechny přiřazené taxonomie.

Nekopíruje provozní systémová metadata ani homepage featured stav:

- `_amarilla_home_featured`,
- `_amarilla_home_featured_order`.

Kopii je nutné zkontrolovat, přejmenovat a teprve potom publikovat.

### Homepage featured vehicles

Meta box **Úvodní stránka** používá:

- `_amarilla_home_featured` pro zařazení,
- `_amarilla_home_featured_order` pro ruční pořadí.

Blok `amarilla/featured-vehicles` zobrazuje maximálně tři publikovaná vozidla. Nejprve vezme označená vozidla podle ručního pořadí, poté podle `menu_order`, názvu a ID. Pokud jsou označena méně než tři, volná místa doplní dalšími publikovanými vozidly podle `menu_order`, názvu a ID bez duplicit.

Stejný resolver poskytuje vozidla pro homepage `ItemList` schema.

### Archiv a karty

Výchozí `templates/archive-vehicle.html` používá Query Loop pro `vehicle` a blok `amarilla/vehicle-card`. Filter pro hlavní frontendový archiv nastavuje pouze tomuto vehicle Query Loopu `posts_per_page = -1`, takže `/vozovy-park/` zobrazuje všechna publikovaná vozidla bez stránkování a neovlivňuje jiné Query Loopy ani taxonomické archivy.

Karta zobrazuje podle dostupných dat featured image, štítek nebo první kategorii, název, tagline, počet míst, převodovky, dveře a cenu.

## Nezávazná poptávka a Contact Form 7

Hlavní produkční workflow používá stránku:

```text
/nezavazna-poptavka/
```

Cílový CF7 formulář se neurčuje hardcoded produkčním ID. Theme jej hledá v obsahu této stránky jako blok `contact-form-7/contact-form-selector` nebo shortcode `[contact-form-7 ...]`; zachován je také fallback na publikovaný formulář s přesným názvem `Nezávazná poptávka`.

Formulář musí obsahovat dva selecty:

```text
[select* select-auto "— Vyberte vůz —"]
[select* transmission "Je mi to jedno" "Manuál" "Automat"]
```

Jejich statické options jsou jen minimální validní konfigurace. Theme je při renderu cílového formuláře nahradí:

- `select-auto` dostane všechna publikovaná vozidla seřazená podle `menu_order` a názvu,
- value i label option jsou přímo název CPT záznamu,
- `transmission` se odvodí z `_vehicle_transmissions`,
- při jediné dostupné variantě se nabídne pouze tato varianta,
- při dvou nebo žádné známé variantě zůstanou univerzální možnosti „Je mi to jedno“, „Manuál“ a „Automat“.

Detail vozidla odkazuje na:

```text
/nezavazna-poptavka/?requested_vehicle=<POST_ID>
```

Theme ověří, že ID patří publikovanému `vehicle`, předvybere jeho název a připraví odpovídající transmission options. Parametr `?vehicle=` se nepoužívá, protože `vehicle` je veřejný WordPress query var tohoto CPT a koliduje s main query.

Do HTML formuláře se vloží malý JSON objekt `amarilla-vehicle-transmission-data`. `assets/js/theme.js` podle něj aktualizuje transmission options při ruční změně auta a po resetu formuláře. Integrace nepoužívá AJAX ani jQuery.

Mail template CF7 musí obsahovat:

```text
[select-auto]
[transmission]
```

Seznam vozidel se v CF7 tagu ručně neudržuje.

### CF7 na homepage

Hero pattern vykreslí `[amarilla_booking_widget]` pouze tehdy, když je v Customizeru zapnutý booking widget. Je-li v nastavení `amarilla_home_booking_form_shortcode` validní samostatný CF7 shortcode a Contact Form 7 je aktivní, použije se tento formulář ve styled wrapperu.

Bez validního CF7 shortcodu existuje legacy GET widget směřující na kontaktní stránku. Stejně tak source stále obsahuje legacy interní formulář/CPT v `inc/inquiry-form.php` a ve výchozí šabloně `page-contact.html`. Nejde o hlavní produkční poptávkový workflow; nové provozní nastavení má používat `/nezavazna-poptavka/` a CF7.

## Homepage a Site Editor

Theme-file baseline homepage je `templates/front-page.html`. Skládá se z registrovaných patterns a dynamického featured bloku. Hlavička, topbar a patička jsou template parts v `parts/`.

V **Vzhled → Editor** lze spravovat blokové templates, template parts, navigaci a globální styly z `theme.json`. Uložená úprava template v Site Editoru vzniká v databázi jako `wp_template` override a má přednost před stejnojmenným souborem v `templates/`.

Produkční homepage může mít legitimní vlastní `front-page` override. Neresetujte bez kontroly celou homepage na theme baseline: reset odstraní databázovou variantu a aktivuje aktuální soubor `templates/front-page.html`. Při úpravě nejdřív porovnejte živý obsah, uložený override a theme-file baseline.

## Customizer

Theme registruje panel **Amarilla Tenerife** v Customizeru. U block theme může být nejspolehlivější přímá cesta `/wp-admin/customize.php`, pokud položka není viditelná v menu.

Customizer spravuje data a obsah PHP patterns:

- branding: šířka hlavního loga, světlé logo a textový fallback,
- horní lištu a jazykový přepínač,
- telefon, e-mail, adresu, otevírací dobu a WhatsApp,
- hero včetně obrázku, CTA a volitelného homepage CF7 shortcodu,
- trust strip,
- texty sekce doporučených vozidel,
- sekce „Proč si vybrat nás“ a „Tipy z Tenerife“,
- závěrečné CTA,
- obsah patičky a sociální sítě,
- až šest poboček s adresou, hodinami a GPS,
- hlavičku blogu a zapnutí souvisejících článků.

Standardní custom logo se nastavuje přes WordPress **Identitu webu**. Barvy, typografie, rozestupy a blokové rozvržení patří do Site Editoru, nikoli do panelu Amarilla.

Pobočky se vykreslují jako seznam a OpenStreetMap iframe. Bounding box se odvozuje z vyplněných souřadnic; není použit Google Maps ani vlastní mapový JavaScript.

## Vícejazyčnost

Theme používá text domain `amarilla`, registruje vybrané provozní řetězce pro Polylang a umí z jeho API vykreslit jazykový přepínač v topbaru. Bez aktivního Polylangu se zobrazí pouze statický fallback přepínače bez reálných překladových URL.

Stránky, články, vozidla a jejich taxonomie se překládají standardním obsahem Polylangu. Helper poptávkové URL při dostupném `pll_get_post()` použije překlad stránky `nezavazna-poptavka`.

## SEO a schema.org

Theme vkládá do `<head>` jeden JSON-LD dokument s `@graph`. Na adminu a 404 se nevykresluje.

- `AutoRental` je základní node na frontendových stránkách. Používá název webu, popis, kontakty, logo, sociální profily, otevírací dobu a nakonfigurované pobočky s adresou a GPS.
- `BreadcrumbList` se přidává na stránky, detail vozidla, vehicle archiv a jednotlivý blogový článek.
- Homepage dostává `ItemList` maximálně tří vozidel ze stejného featured resolveru jako homepage sekce.
- Vehicle archiv dostává `ItemList` všech publikovaných vozidel v pořadí `menu_order`.
- Detail vozidla dostává `Product` s obrázkem a dostupnými specifikacemi. Pokud lze určit číselnou cenu, přidá se `Offer` s nejnižší základní/sezónní cenou. `AggregateRating` se přidá jen při současně vyplněném hodnocení a kladném počtu hodnocení.
- Jednotlivý blogový příspěvek dostává `Article` s daty, autorem a volitelným obrázkem.

Do hodnocení zadávejte pouze skutečná data.

## Blog

Blog používá standardní WordPress post type `post`:

- `templates/home.html` zobrazuje devět nejnovějších článků na stránku v třísloupcové mřížce,
- `templates/archive.html` zajišťuje archivy kategorií, autorů a datumů,
- `templates/single.html` zobrazuje článek, autora, datum, featured image a odhad čtení,
- `[amarilla_reading_time]` počítá nejméně jednu minutu při rychlosti 200 slov za minutu,
- `[amarilla_related_posts]` může pod článkem zobrazit až tři nejnovější příspěvky ze stejných kategorií.

Pro samostatnou blogovou stránku se v nastavení čtení použije statická homepage a samostatná stránka příspěvků. Hlavička blogu a související články se nastavují v Customizeru.

## Assety a fonty

Frontend načítá `style.css`, `assets/css/theme.css` a deferred `assets/js/theme.js` s cache-busting verzí `AMARILLA_VERSION`. `assets/js/vehicle-gallery.js` se načítá pouze na detailu vozidla.

Fraunces a DM Sans jsou variable WOFF2 fonty uložené v `assets/fonts/` pro latin a latin-ext. Registruje je `theme.json`; kritické latin-ext řezy se preloadují z theme. Theme nekontaktuje Google Fonts.

## Cache a provozní poznámky

Produkce používá page cache (WP Fastest Cache). Dynamický seznam vozidel a JSON mapa převodovek jsou součástí renderovaného HTML CF7 formuláře, nikoli samostatného API requestu.

Po změně následujících dat může být potřeba purge page cache:

- publikování, skrytí nebo přejmenování vozidla,
- změna `_vehicle_transmissions`,
- změna CF7 tagů nebo vložení cílového formuláře.

Stejnou opatrnost vyžadují změny homepage, pokud existuje Site Editor template override. README nepopisuje konfiguraci konkrétního cache pluginu.

## Vývoj a deploy

Repozitář theme:

```text
/srv/apps/tenerife-theme
```

DEV WordPress běží v Docker stacku `/srv/stacks/tenerife-wp-dev`; theme je ve WordPress kontejneru připojena jako:

```text
/var/www/html/wp-content/themes/tenerife
```

Po změnách proveďte minimálně:

```bash
git diff --check
git status --short --branch
```

Změněné PHP soubory lintujte v DEV kontejneru a frontend ověřte na DEV instanci. Změněný JavaScript lze syntakticky ověřit přes `node --check`.

Produkční deploy skript je bezpečně výchozí v dry-run režimu:

```bash
./scripts/deploy-theme.sh
```

Ostrý apply vyžaduje explicitní `--apply` a potvrzení `DEPLOY`. Standardně před uploadem spustí `scripts/backup-remote-theme.sh`. Přepínače `--delete` a `--no-backup` používejte pouze po samostatném výslovném schválení.

## Struktura souborů

```text
amarilla-tenerife/
├── style.css                       metadata a vstupní stylesheet
├── theme.json                      globální block-theme nastavení
├── functions.php                   bootstrap, assety a includes
├── templates/                      theme-file block templates
├── parts/                          header, footer a topbar
├── patterns/                       PHP patterns homepage
├── inc/
│   ├── vehicle-cpt.php             CPT, taxonomie a vehicle metadata
│   ├── block-bindings.php          vehicle shortcodes, galerie a karty
│   ├── featured-vehicles.php       homepage featured resolver a blok
│   ├── vehicle-inquiry-cf7.php     hlavní CF7 vehicle workflow
│   ├── seasonal-pricing.php        cenová období a blackout metadata
│   ├── admin-vehicle-duplicate.php admin duplikace vozidel
│   ├── customizer.php              panel Amarilla a selective refresh
│   ├── content-shortcodes.php      dynamické části headeru/footeru
│   ├── locations.php               pobočky a OpenStreetMap
│   ├── blog.php                    blog helpery a shortcodes
│   ├── polylang-compat.php         jazykový přepínač a řetězce
│   └── inquiry-form.php            legacy interní inquiry modul
├── assets/
│   ├── css/                        frontend a editor styly
│   ├── js/                         frontend/editor skripty
│   ├── fonts/                      self-hostované WOFF2 fonty
│   └── images/                     statické obrázky theme
└── scripts/                        backup a SFTP deploy workflow
```

## Licence

GNU General Public License v2 nebo novější.
