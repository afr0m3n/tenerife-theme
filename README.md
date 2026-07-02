# Amarilla Tenerife — WordPress šablona

Block theme pro autopůjčovnu Amarilla Car Hire na Tenerife. Připraveno pro WordPress 6.4+, PHP 8.0+ a plugin Polylang pro vícejazyčné stránky.

## Obsah balíčku

- Plnohodnotný block theme (Full Site Editing) – vše se dá upravovat přímo v adminu
- Custom post type **Vozidla** + taxonomie **Kategorie** (Ekonomy, SUV, Cabrio…)
- Předpřipravené **block patterns** (sekce) – hero, vozový park, proč my, tipy, CTA
- Custom šablony pro stránky: Domů, Vozový park, Detail vozidla, O nás, Kontakt, Služby, 404
- Plně přeložitelné texty + připraveno pro Polylang (CZ/EN/ES/DE)
- Strukturovaná data **schema.org** (AutoRental) pro SEO
- Lazy loading obrázků, optimalizované načítání fontů

## Instalace

1. Stáhněte si soubor `amarilla-tenerife.zip`
2. V administraci WordPressu jděte do **Vzhled → Šablony → Nahrát šablonu**
3. Vyberte ZIP a klikněte na **Nainstalovat**
4. Po instalaci klikněte na **Aktivovat**
5. Při první aktivaci se automaticky vytvoří kategorie vozidel (Ekonomy, Family, SUV, Kabriolet, Premium, Minivan)

## První kroky po instalaci

### 1. Nastavení trvalých odkazů
Jděte do **Nastavení → Trvalé odkazy** a vyberte **Název příspěvku** (`/%postname%/`). Pokud už toto máte, jen klikněte na **Uložit změny** – tím se obnoví URL pravidla pro vozidla.

### 2. Vytvoření základních stránek
Vytvořte tyto stránky (**Stránky → Přidat novou**) a u každé v pravém panelu vyberte odpovídající **Šablona**:

| Název stránky | Slug (URL) | Šablona |
|---|---|---|
| Domů | `/` | (výchozí) |
| O nás | `/o-nas/` | Stránka: O nás |
| Kontakt | `/kontakt/` | Stránka: Kontakt |
| Služby | `/sluzby/` | Stránka: Služby |

Poté v **Nastavení → Zobrazení** nastavte:
- Hlavní strana → **Statická stránka** → "Domů"

### 3. Kontaktní údaje
**Vzhled → Customizovat → Kontaktní údaje** – telefon, e-mail, adresa, WhatsApp. Tyto údaje se používají v patičce a structured datech pro SEO.

### 4. Logo
Až budete mít hotové logo:
- **Vzhled → Customizovat → Identita stránky → Vybrat logo**
- Po nahrání loga se automaticky skryje text placeholder "Amarilla"
- Doporučená velikost: 240 × 80 px (PNG nebo SVG)

## Přidání vozidla

1. **Vozový park → Přidat nové**
2. Vyplňte:
   - **Název** – např. "Fiat Panda"
   - **Hlavní fotka** (vpravo dole) – ideálně 1600 × 1100 px
   - **Editor** – delší popis vozidla pro detail stránku
   - **Kategorie** (vpravo) – vyberte z Ekonomy/SUV/…
   - **Specifikace vozidla** (pod editorem):
     - **Krátký popis** – krátká věta pod název na kartě (např. "Malý, mrštný, ideální do města")
     - **Počet míst** – číslo (1–9)
     - **Počet dveří** – číslo (2–5)
     - **Převodovka** – Manuál / Automat
     - **Palivo** – Benzín / Nafta / Hybrid / Elektro
     - **Kufr** – text (např. "350 l" nebo "2 velké kufry")
     - **Klimatizace** – Ano / Ne
     - **Cena za den** – text (např. "25 €"). **Pokud necháte prázdné, místo ceny se zobrazí tlačítko "Poptat termín".**
     - **Štítek** – volitelný (např. "Nejoblíbenější", "Novinka", "Sleva 20 %"). Štítky se zobrazí žlutě, kategorie šedě.
3. Klikněte **Publikovat**

## Pořadí vozidel

Vozidla v přehledu se řadí podle pole **Pořadí** (Page Attributes). Najdete ho v pravém panelu pod sekcí Stránka. Nižší číslo = výše v seznamu.

## Vícejazyčnost (Polylang)

1. Nainstalujte plugin **Polylang** (zdarma): **Pluginy → Přidat nový → Polylang**
2. Po aktivaci přidejte jazyky: **Languages → Languages → Add new** (Čeština, English, Español, Deutsch)
3. Nastavte český jako výchozí
4. **Languages → Settings → URL modifications** doporučujeme:
   - Pro výchozí jazyk skrýt jazykový kód v URL (čisté `/o-nas/` místo `/cs/o-nas/`)
5. Nyní můžete u každé stránky a vozidla přidávat překlady přes ikony vlajek v editoru
6. **Languages → String translations** – zde najdete texty šablony (tlačítka, eyebrowy atd.) pro překlad

## Kontaktní formulář

Doporučujeme **Contact Form 7** (zdarma):

1. **Pluginy → Přidat nový** → vyhledejte "Contact Form 7" → Instalovat → Aktivovat
2. **Kontakt → Kontaktní formuláře → Add New**
3. Použijte tuto šablonu (zkopírujte do pole "Form"):

```
<label>Vaše jméno *
[text* your-name] </label>

<label>E-mail *
[email* your-email] </label>

<label>Telefon
[tel your-phone] </label>

<label>Termín pronájmu (od – do)
[text your-dates] </label>

<label>Vozidlo (pokud máte na mysli konkrétní)
[text vehicle] </label>

<label>Zpráva
[textarea your-message] </label>

[submit "Odeslat poptávku"]
```

4. Šablona automaticky předvyplní pole "vehicle", pokud uživatel přijde z detailu konkrétního vozu (přes URL parametr `?vehicle=Fiat Panda`)
5. Vložte shortcode formuláře `[contact-form-7 id="XXX"]` do stránky **Kontakt** v editoru

## Customizer (Vzhled → Customizovat)

- **Identita stránky** – Logo, název webu, ikona webu (favicon)
- **Kontaktní údaje** – Telefon, e-mail, adresa, WhatsApp (vlastní sekce přidaná šablonou)
- **Menu** – Hlavní navigace
- **Barvy a typografie** – Lze upravit přes **Vzhled → Editor (Site Editor) → Styly**

## Úprava barev a fontů

**Vzhled → Editor → Styly** – kliknutím na štětku v pravém horním rohu se otevře panel se styly. Lze měnit:
- Globální barvy
- Fonty
- Velikosti písma
- Mezery

## Soubory šablony

```
amarilla-tenerife/
├── style.css                — metadata šablony
├── theme.json               — globální nastavení (barvy, fonty)
├── functions.php            — hlavní funkce
├── README.md                — tento soubor
├── README-en.md             — anglická verze
├── inc/
│   ├── vehicle-cpt.php      — registrace CPT Vozidla + meta box
│   ├── block-bindings.php   — shortcodes pro vykreslování dat
│   ├── helpers.php          — pomocné funkce + Customizer
│   └── polylang-compat.php  — kompatibilita s Polylang
├── templates/               — šablony stránek
├── parts/                   — header, footer, topbar
├── patterns/                — předpřipravené sekce
├── assets/
│   ├── css/                 — stylesheety
│   ├── js/                  — JavaScript
│   └── images/              — obrázky šablony
└── languages/               — překlady (.po, .mo soubory)
```

## Často kladené otázky

**Jak vyměním obrázky v sekci "Tipy z Tenerife"?**
Editor patterns: **Vzhled → Editor → Patterns → Tipy z Tenerife** nebo upravte soubor `patterns/tenerife-tips.php` (vyměňte URL obrázků).

**Jak změním obrázek v hero sekci?**
Soubor `patterns/hero.php` – řádek s `<img src="...">`. Doporučujeme nahrát vlastní obrázek do mediální knihovny a použít jeho URL.

**Vozový park se nezobrazuje na hlavní stránce.**
Musíte přidat alespoň jedno vozidlo (Vozový park → Přidat nové). Pokud nejsou vozy, sekce zobrazí výzvu k jejich přidání.

**Po aktivaci jsou rozbité odkazy na vozidla.**
Jděte do **Nastavení → Trvalé odkazy** a klikněte na **Uložit změny**. Tím se obnoví URL pravidla.

## Kontakt na vývojáře

Pokud potřebujete úpravy nebo máte dotazy k implementaci, kontaktujte nás.

---

**Verze:** 1.0.0
**Licence:** GNU GPL v2 nebo novější

---

## Novinky v 1.2.0

### Self-hostované fonty (GDPR-friendly)
Fonty Fraunces a DM Sans jsou nyní bundlované přímo v šabloně (`assets/fonts/`).
Žádné spojení s `fonts.googleapis.com` ani `fonts.gstatic.com` — důležité pro
EU autopůjčovny po rozhodnutí německého soudu (LG München I, 2022), které
přenosy IP do USA přes Google Fonts CDN označilo za porušení GDPR.

Soubory jsou variable fonts (`.woff2`, latin + latin-ext), `font-display: swap`
a kritické tváře jsou preloadované přes `<link rel="preload">` v `<head>`.
Celková velikost ~270 KB, načítá se jen co je potřeba (přes `unicode-range`).

### Poptávkový / rezervační formulář
Plně funkční tok bez externího pluginu:

1. **V hero** je teď compact widget (4 pole: datum vyzvednutí → vrácení → místo
   → třída vozu). Lze vypnout v *Customizer → Hero → Zobrazit poptávkový widget*.
2. **Na /kontakt/** je velký formulář (jméno, e-mail, telefon, věk řidiče,
   poznámka, GDPR souhlas). Hero widget jeho hodnoty předvyplní.
3. **U každého vozu** je tlačítko *Poptat tento vůz* — předvyplní jméno vozu
   i jeho třídu.
4. Po odeslání:
   - se uloží do **CPT „Poptávky"** (v adminu, sloupec u Vozidla)
   - **e-mail provozovateli** (na adresu z Customizeru → Kontakt) s odpověděním
     na e-mail žadatele přes `Reply-To`
   - **potvrzovací e-mail žadateli** se shrnutím
5. Bezpečnost: WP nonce, honeypot (`amarilla_website`), rate limit 1/60 s/IP,
   serverová validace dat (nikdy minulost, vrácení > vyzvednutí).

Hook pro CRM integraci:
```php
add_action( 'amarilla_inquiry_received', function( $post_id, $data ) {
    // poslat do HubSpotu / Slacku / vlastního API
}, 10, 2 );
```

### Sezónní ceny u vozidel
Na editaci vozidla je nový meta box **Sezónní ceny a dostupnost** s opakující
se tabulkou (od–do, cena/den, štítek). Logika:
- pokud datum vyzvednutí spadá do nějakého období → použije se jeho cena
- jinak fallback na **základní cenu** (`_vehicle_price`) — zpětně kompatibilní
- v kartě vozu se zobrazí **„od XX €"** automaticky, když existuje období
  s nižší cenou než základní (vizuální signál pro návštěvníky)
- nejnižší cena se propíše do `schema.org/Offer` pro rich snippets

Volitelně i **blackout dny** (textarea, jedno datum nebo rozsah na řádek) —
pro vlastní přehled. Formulář na ně neupozorňuje automaticky, jen se zobrazí
v admin detailu poptávky pokud termín do blackoutu spadá.

### Rozšířené schema.org
JSON-LD se teď sestavuje jako `@graph` se třemi a víc nody dle stránky:

- **Vždy:** `AutoRental` (jméno, telefon, e-mail, adresa, otevírací doba,
  sociální sítě jako `sameAs`, všechny pobočky jako `location` s `geo`).
- **Hlavní stránka / archiv:** `ItemList` všech vozů (sitelinks v Googlu).
- **Detail vozu:** `Product` (kategorie, počet míst, palivo…) +
  `Offer` (nejnižší cena ze sezónních období) +
  `AggregateRating` (pokud jsou vyplněny `_vehicle_rating` a `_vehicle_rating_count`).
- **Blog post:** `Article` (autor, datum, headline, obrázek).
- **Skoro vždy:** `BreadcrumbList` pro lepší navigaci v SERP.

Hodnocení vozidel zadáte v meta boxu *Specifikace vozidla* (nová pole **Hodnocení (1–5)**
a **Počet hodnocení**). Pokud nemáte reálná hodnocení, **nechte prázdné** — Google
fake hodnocení penalizuje.

### Mapa míst vyzvednutí
Nový panel v Customizeru: **Pobočky / místa vyzvednutí** (až 6 lokací).
U každé: název, adresa, otevírací doba, GPS souřadnice (lat/lng).

Mapa je **OpenStreetMap embed iframe** — žádné Google Maps, žádný Mapbox token,
GDPR-friendly. Bounding box se dopočítává automaticky podle vyplněných souřadnic.
U každé pobočky je odkaz "Otevřít v mapě" (mlat/mlon URL).

Souřadnice najdete na [openstreetmap.org](https://www.openstreetmap.org/) —
pravým tlačítkem → "Zobrazit adresu", nebo `View → Show address`.

Sekce se objeví na hlavní stránce (mezi tipy a CTA). Lze vypnout
v Customizeru. Pobočky se zároveň objeví jako možnosti v poptávkovém formuláři.

### Blog pro SEO
Plnohodnotný blog (post type `post`):
- `templates/home.html` — archiv blogu (3-sloupcová mřížka s featured image)
- `templates/single.html` — článek s 720px obsahem, kategorie, datum, čas čtení
- `templates/archive.html` — kategorie, autoři, datumy
- **Související články** pod každým článkem (3 nejnovější ze stejných kategorií)
- Shortcode `[amarilla_reading_time]` — odhad doby čtení (200 slov/min)
- Customizer panel **Blog — Tipy z Tenerife** pro nastavení hlavičky archivu

Pro plnohodnotný blog vytvořte v **Nastavení → Čtení**:
- Hlavní stránka → Statická stránka „Domů"
- Stránka pro příspěvky → vytvořte stránku „Blog" a vyberte ji

URL bude `/blog/`, jednotlivé články `/blog/nazev-clanku/`.

Existující sekce *Tipy z Tenerife* (3 ručně zadané karty v Customizeru)
zůstává pro hlavní stránku — funguje jako *highlights*, blog je pak hluboký
obsah pro SEO.

---

## Logo — jak ho nahrát

V edit šabloně (Site Editor) blok `[amarilla_logo]` vypadá jako prázdný shortcode
— **to je v pořádku**. Logo se nenahrává odsud.

Nahrajte ho v: **Vzhled → Přizpůsobit → Identita webu → Vybrat logo**
(`/wp-admin/customize.php`).

Doplňková nastavení tamtéž:
- **Šířka loga v hlavičce (px)** — 120–220 px
- **Logo pro tmavé pozadí (patička)** — samostatný soubor (jinak se použije text)
- **Zobrazit textovou variantu** — vypněte, pokud nechcete během načítání
  vidět žlutý kruh s písmenem

Doporučená velikost: 240 × 80 px (PNG nebo SVG, SVG je lepší — ostré
na všech zařízeních a menší).
