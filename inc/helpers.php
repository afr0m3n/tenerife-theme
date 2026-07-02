<?php
/**
 * Pomocné funkce šablony + strukturovaná data (schema.org)
 *
 * @package Amarilla
 * @since 1.0.0 (rozšířeno v 1.1.0; schema.org Product/Offer/AggregateRating v 1.2.0)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sdílené výchozí hodnoty pro Customizer i frontend render.
 *
 * Klíče odpovídají existujícím theme_mod ID, aby se produkční nastavení
 * nemuselo migrovat ani přejmenovávat.
 */
function amarilla_get_theme_defaults() {
	static $defaults = null;

	if ( null !== $defaults ) {
		return $defaults;
	}

	$defaults = array(
		'amarilla_logo_width'              => 160,
		'amarilla_logo_light'              => '',
		'amarilla_show_text_logo_fallback' => true,

		'amarilla_topbar_enabled'     => true,
		'amarilla_topbar_phone'       => '+420 702 143 084',
		'amarilla_topbar_phone_link'  => '+420702143084',
		'amarilla_topbar_location'    => 'Letiště Tenerife Sur (TFS)',
		'amarilla_topbar_show_langs'  => true,

		'amarilla_phone'        => '+34 922 000 000',
		'amarilla_email'        => 'info@autopujcovna-tenerife.cz',
		'amarilla_address'      => 'Letiště Tenerife Sur, Avenida Bruselas, 38660',
		'amarilla_address_full' => "Letiště Tenerife Sur (TFS)\nAvenida Bruselas, 38660\nAdeje, Santa Cruz de Tenerife",
		'amarilla_hours'        => 'Po–Ne, 7:00–23:00',
		'amarilla_whatsapp'     => '',

		'amarilla_hero_eyebrow'      => 'Autopůjčovna na Tenerife',
		'amarilla_hero_title'        => 'Tenerife po vašem',
		'amarilla_hero_title_accent' => 'rytmu.',
		'amarilla_hero_lead'         => 'Půjčte si auto bez starostí. Vyzvednutí přímo na letišti, plné pojištění a žádné skryté poplatky. Místní tým, který ostrov zná.',
		'amarilla_hero_btn1_text'    => 'Prohlédnout vozy',
		'amarilla_hero_btn1_url'     => '/vozovy-park/',
		'amarilla_hero_btn2_text'    => 'Jak to funguje',
		'amarilla_hero_btn2_url'     => '/kontakt/',
		'amarilla_hero_show_booking' => true,
		'amarilla_hero_image'        => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?auto=format&fit=crop&w=900&q=80',
		'amarilla_hero_badge'        => 'K vyzvednutí dnes',
		'amarilla_hero_stat_value'   => 'od 25 €',
		'amarilla_hero_stat_label'   => 'Cena za den',

		'amarilla_trust_enabled' => true,

		'amarilla_why_enabled'      => true,
		'amarilla_why_eyebrow'      => 'Proč si vybrat nás',
		'amarilla_why_title'        => 'Místní tým, který ostrov',
		'amarilla_why_title_accent' => 'zná.',
		'amarilla_why_lead'         => 'Auto si nepůjčujete jenom kvůli kolům. Půjčujete si svobodu objevovat Tenerife podle sebe. Postaráme se o vše ostatní.',

		'amarilla_tips_enabled' => true,
		'amarilla_tips_eyebrow' => 'Tipy z ostrova',
		'amarilla_tips_title'   => 'Tenerife, které stojí za to.',
		'amarilla_tips_lead'    => 'Co navštívit s autem? Pár míst, kde to skutečně žije, sepsaných od lidí, co tady bydlí.',

		'amarilla_cta_enabled'      => true,
		'amarilla_cta_title'        => 'Připraveni vyrazit na',
		'amarilla_cta_title_accent' => 'cestu?',
		'amarilla_cta_lead'         => 'Napište nám termín a my připravíme nabídku na míru. Odpovídáme do hodiny.',
		'amarilla_cta_btn_text'     => 'Poptat termín',
		'amarilla_cta_btn_url'      => '/kontakt/',

		'amarilla_footer_about'      => 'Autopůjčovna provozovaná místními. Tenerife pro vás.',
		'amarilla_footer_col1_title' => 'Stránky',
		'amarilla_footer_col1_links' => "Domů|/\nVozový park|/vozovy-park/\nSlužby|/sluzby/\nO nás|/o-nas/\nKontakt|/kontakt/",
		'amarilla_footer_col2_title' => 'Pomoc',
		'amarilla_footer_col2_links' => "Časté dotazy|/faq/\nPojištění|/pojisteni/\nStorno podmínky|/storno-podminky/\nObchodní podmínky|/obchodni-podminky/",
		'amarilla_footer_copyright'  => '© ' . date( 'Y' ) . ' Amarilla Car Hire. Všechna práva vyhrazena.',

		'amarilla_locations_enabled' => true,
		'amarilla_locations_eyebrow' => 'Kde nás najdete',
		'amarilla_locations_title'   => 'Pobočky po celém ostrově.',
		'amarilla_locations_lead'    => 'Vozy si můžete vyzvednout přímo na letišti nebo si je přivezeme zdarma na váš hotel.',
	);

	foreach ( array( 'facebook', 'instagram', 'youtube', 'tripadvisor' ) as $social ) {
		$defaults[ "amarilla_social_{$social}" ] = '';
	}

	$trust_defaults = array(
		1 => array( 'check', 'Bez depozitu', 'Žádná blokace na kartě' ),
		2 => array( 'shield', 'Plné pojištění', 'V ceně všech vozů' ),
		3 => array( 'clock', '24/7 podpora', 'Česky, anglicky, španělsky' ),
		4 => array( 'location', 'Vyzvednutí na letišti', 'TFS i TFN, zdarma' ),
	);
	foreach ( $trust_defaults as $i => $item ) {
		$defaults[ "amarilla_trust_{$i}_icon" ]     = $item[0];
		$defaults[ "amarilla_trust_{$i}_title" ]    = $item[1];
		$defaults[ "amarilla_trust_{$i}_subtitle" ] = $item[2];
	}

	$why_defaults = array(
		1 => array( 'Cena', 'Žádné skryté poplatky', 'Cena, kterou vidíte, je cena, kterou zaplatíte. Pojištění, neomezené kilometry i druhý řidič v ceně.' ),
		2 => array( 'Servis', 'Auta v perfektním stavu', 'Pravidelná údržba a kontroly. Většina vozů je mladší tří let.' ),
		3 => array( 'Lidé', 'Mluvíme česky', 'Český servis přímo na ostrově. Komunikace, papírování i podpora bez jazykových bariér.' ),
		4 => array( 'Volnost', 'Jste v plánu', 'Bezplatné storno do 48 hodin před vyzvednutím. Změna termínu kdykoli.' ),
	);
	foreach ( $why_defaults as $i => $item ) {
		$defaults[ "amarilla_why_{$i}_category" ] = $item[0];
		$defaults[ "amarilla_why_{$i}_title" ]    = $item[1];
		$defaults[ "amarilla_why_{$i}_desc" ]     = $item[2];
	}

	$tips_defaults = array(
		1 => array( 'Příroda', 'Národní park Teide za úsvitu', 'https://images.unsplash.com/photo-1583425423320-1f1f0c8b4a3a?auto=format&fit=crop&w=900&q=80' ),
		2 => array( 'Trasy', 'Pohoří Anaga', 'https://images.unsplash.com/photo-1571893544028-06b07af6dade?auto=format&fit=crop&w=700&q=80' ),
		3 => array( 'Vesnice', 'Masca a Garachico', 'https://images.unsplash.com/photo-1535914254981-b5012eebbd15?auto=format&fit=crop&w=700&q=80' ),
	);
	foreach ( $tips_defaults as $i => $item ) {
		$defaults[ "amarilla_tip_{$i}_tag" ]   = $item[0];
		$defaults[ "amarilla_tip_{$i}_title" ] = $item[1];
		$defaults[ "amarilla_tip_{$i}_image" ] = $item[2];
		$defaults[ "amarilla_tip_{$i}_url" ]   = '';
	}

	$location_defaults = array(
		1 => array( 'Letiště Tenerife Sur (TFS)', "Avenida Bruselas\n38660 Adeje", '7:00 – 23:00', '28.0444', '-16.5727' ),
		2 => array( 'Letiště Tenerife Norte (TFN)', "Avenida Ángel Sanz Briz\n38297 La Laguna", '7:00 – 22:00', '28.4843', '-16.3415' ),
		3 => array( 'Los Cristianos — pobočka', "Calle General Franco 25\n38650 Arona", '9:00 – 19:00', '28.0476', '-16.7164' ),
	);
	for ( $i = 1; $i <= 6; $i++ ) {
		$item = isset( $location_defaults[ $i ] ) ? $location_defaults[ $i ] : array( '', '', '', '', '' );

		$defaults[ "amarilla_loc_{$i}_name" ]    = $item[0];
		$defaults[ "amarilla_loc_{$i}_address" ] = $item[1];
		$defaults[ "amarilla_loc_{$i}_hours" ]   = $item[2];
		$defaults[ "amarilla_loc_{$i}_lat" ]     = $item[3];
		$defaults[ "amarilla_loc_{$i}_lng" ]     = $item[4];
	}

	return $defaults;
}

/**
 * Vrátí výchozí hodnotu jednoho Customizer nastavení.
 */
function amarilla_get_theme_default( $setting, $fallback = '' ) {
	$defaults = amarilla_get_theme_defaults();

	return array_key_exists( $setting, $defaults ) ? $defaults[ $setting ] : $fallback;
}

/**
 * Wrapper nad get_theme_mod() se sdíleným fallbackem.
 */
function amarilla_get_theme_mod( $setting, $fallback = null ) {
	if ( null === $fallback ) {
		$fallback = amarilla_get_theme_default( $setting );
	}

	return get_theme_mod( $setting, $fallback );
}

/**
 * URL na kontaktní stránku
 */
function amarilla_get_contact_url() {
	$custom = get_theme_mod( 'amarilla_hero_btn2_url', '' );
	if ( $custom ) {
		return $custom;
	}
	$contact_page = get_page_by_path( 'kontakt' );
	if ( $contact_page ) {
		return get_permalink( $contact_page );
	}
	return home_url( '/kontakt/' );
}

/**
 * URL na stránku vozového parku
 */
function amarilla_get_fleet_url() {
	$archive = get_post_type_archive_link( 'vehicle' );
	return $archive ? $archive : home_url( '/vozovy-park/' );
}

/**
 * Telefon (hlavní, z Customizeru)
 */
function amarilla_get_phone() {
	return amarilla_get_theme_mod( 'amarilla_phone' );
}

/**
 * Telefon — formát vhodný pro tel: odkaz
 */
function amarilla_get_phone_link() {
	$phone = amarilla_get_phone();
	return preg_replace( '/[^\d+]/', '', $phone );
}

/**
 * E-mail
 */
function amarilla_get_email() {
	return amarilla_get_theme_mod( 'amarilla_email' );
}

/**
 * Adresa (jeden řádek)
 */
function amarilla_get_address() {
	return amarilla_get_theme_mod( 'amarilla_address' );
}

/* ============================================================
 * SCHEMA.ORG — strukturovaná data
 *
 * Strategie:
 *   - Na hlavní stránce a kontaktní stránce → AutoRental (firma)
 *     + ItemList všech vozů (Sitelinks pro Google).
 *   - Na detailu vozidla → Product (Car) + Offer (s nejnižší cenou
 *     ze sezónních období) + AggregateRating, pokud jsou hodnocení.
 *   - Na stránce blog post → Article s autorem a obrázkem.
 *   - Vždy: BreadcrumbList pro lepší navigaci v SERP.
 * ============================================================ */

/**
 * Vrátí pole reprezentující LocalBusiness/AutoRental — sdílí ho víc handlerů.
 */
function amarilla_get_business_schema_node() {
	$logo_url = '';
	if ( has_custom_logo() ) {
		$logo_id  = get_theme_mod( 'custom_logo' );
		$logo_src = wp_get_attachment_image_src( $logo_id, 'full' );
		if ( $logo_src ) {
			$logo_url = $logo_src[0];
		}
	}

	$node = array(
		'@type'        => 'AutoRental',
		'@id'          => home_url( '/#organization' ),
		'name'         => get_bloginfo( 'name' ),
		'description'  => get_bloginfo( 'description' ),
		'url'          => home_url(),
		'telephone'    => amarilla_get_phone(),
		'email'        => amarilla_get_email(),
		'priceRange'   => '€€',
		'address'      => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => amarilla_get_address(),
			'addressLocality' => 'Tenerife',
			'addressRegion'   => 'Canary Islands',
			'addressCountry'  => 'ES',
		),
		'areaServed'   => array(
			'@type' => 'Place',
			'name'  => 'Tenerife, Canary Islands, Spain',
		),
	);

	if ( $logo_url ) {
		$node['logo'] = $logo_url;
		$node['image'] = $logo_url;
	}

	// Sociální sítě → sameAs
	$social_keys = array( 'facebook', 'instagram', 'youtube', 'tripadvisor' );
	$same_as = array();
	foreach ( $social_keys as $key ) {
		$url = get_theme_mod( "amarilla_social_{$key}", '' );
		if ( $url ) {
			$same_as[] = $url;
		}
	}
	if ( $same_as ) {
		$node['sameAs'] = $same_as;
	}

	// Pobočky jako "location" — pokud máme nakonfigurované.
	if ( function_exists( 'amarilla_get_locations' ) ) {
		$locations = amarilla_get_locations();
		if ( $locations ) {
			$location_nodes = array();
			foreach ( $locations as $loc ) {
				$loc_node = array(
					'@type' => 'Place',
					'name'  => $loc['name'],
				);
				if ( $loc['address'] ) {
					$loc_node['address'] = array(
						'@type'          => 'PostalAddress',
						'streetAddress'  => str_replace( array( "\r\n", "\r", "\n" ), ', ', $loc['address'] ),
						'addressCountry' => 'ES',
					);
				}
				if ( is_numeric( $loc['lat'] ) && is_numeric( $loc['lng'] ) ) {
					$loc_node['geo'] = array(
						'@type'     => 'GeoCoordinates',
						'latitude'  => (float) $loc['lat'],
						'longitude' => (float) $loc['lng'],
					);
				}
				$location_nodes[] = $loc_node;
			}
			$node['location'] = $location_nodes;
		}
	}

	// Otevírací doba (jednoduchá normalizace z customizeru `amarilla_hours`)
	$hours = amarilla_get_theme_mod( 'amarilla_hours' );
	if ( $hours && preg_match( '/(\d{1,2})(?:[:.](\d{2}))?\s*[–—\-až]+\s*(\d{1,2})(?:[:.](\d{2}))?/u', $hours, $m ) ) {
		$node['openingHoursSpecification'] = array(
			array(
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ),
				'opens'     => sprintf( '%02d:%02d', (int) $m[1], (int) ( $m[2] ?? 0 ) ),
				'closes'    => sprintf( '%02d:%02d', (int) $m[3], (int) ( $m[4] ?? 0 ) ),
			),
		);
	}

	return $node;
}

/**
 * Vrátí Product/Car schema pro konkrétní vozidlo.
 */
function amarilla_get_vehicle_schema_node( $post_id ) {
	$title    = get_the_title( $post_id );
	$content  = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
	$desc     = $content ? wp_trim_words( $content, 40, '…' ) : get_post_meta( $post_id, '_vehicle_tagline', true );

	$node = array(
		'@type'       => 'Product',
		'@id'         => get_permalink( $post_id ) . '#product',
		'name'        => $title,
		'description' => $desc,
		'url'         => get_permalink( $post_id ),
		'category'    => __( 'Car rental', 'amarilla' ),
		'brand'       => array(
			'@type' => 'Brand',
			'name'  => get_bloginfo( 'name' ),
		),
	);

	// Obrázek
	if ( has_post_thumbnail( $post_id ) ) {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'amarilla-vehicle-detail' );
		if ( $img ) {
			$node['image'] = $img[0];
		}
	}

	// additionalProperty z meta polí
	$additional = array();
	$seats = (int) get_post_meta( $post_id, '_vehicle_seats', true );
	$doors = (int) get_post_meta( $post_id, '_vehicle_doors', true );
	$trans = get_post_meta( $post_id, '_vehicle_transmission', true );
	$fuel  = get_post_meta( $post_id, '_vehicle_fuel', true );

	if ( $seats ) {
		$additional[] = array(
			'@type' => 'PropertyValue',
			'name'  => __( 'Seats', 'amarilla' ),
			'value' => $seats,
		);
	}
	if ( $doors ) {
		$additional[] = array(
			'@type' => 'PropertyValue',
			'name'  => __( 'Doors', 'amarilla' ),
			'value' => $doors,
		);
	}
	if ( $trans ) {
		$additional[] = array(
			'@type' => 'PropertyValue',
			'name'  => __( 'Transmission', 'amarilla' ),
			'value' => $trans === 'manual' ? 'Manual' : 'Automatic',
		);
	}
	if ( $fuel ) {
		$fuel_map = array(
			'petrol' => 'Petrol', 'diesel' => 'Diesel',
			'hybrid' => 'Hybrid', 'electric' => 'Electric',
		);
		$additional[] = array(
			'@type' => 'PropertyValue',
			'name'  => __( 'Fuel type', 'amarilla' ),
			'value' => isset( $fuel_map[ $fuel ] ) ? $fuel_map[ $fuel ] : $fuel,
		);
	}
	if ( $additional ) {
		$node['additionalProperty'] = $additional;
	}

	// Offer — nejnižší cena přes sezónní období + základní cena.
	if ( function_exists( 'amarilla_get_vehicle_lowest_price' ) ) {
		$low = amarilla_get_vehicle_lowest_price( $post_id );
		if ( $low !== null ) {
			$node['offers'] = array(
				'@type'           => 'Offer',
				'price'           => $low,
				'priceCurrency'   => 'EUR',
				'availability'    => 'https://schema.org/InStock',
				'url'             => get_permalink( $post_id ),
				'priceSpecification' => array(
					'@type'         => 'UnitPriceSpecification',
					'price'         => $low,
					'priceCurrency' => 'EUR',
					'unitText'      => __( 'per day', 'amarilla' ),
				),
				'priceValidUntil' => wp_date( 'Y-12-31' ),
				'seller'          => array( '@id' => home_url( '/#organization' ) ),
			);
		}
	}

	// AggregateRating — pokud jsou ručně zadaná hodnocení.
	$rating_value = get_post_meta( $post_id, '_vehicle_rating', true );
	$rating_count = (int) get_post_meta( $post_id, '_vehicle_rating_count', true );
	if ( $rating_value && $rating_count > 0 ) {
		$node['aggregateRating'] = array(
			'@type'       => 'AggregateRating',
			'ratingValue' => (string) $rating_value,
			'reviewCount' => $rating_count,
			'bestRating'  => '5',
			'worstRating' => '1',
		);
	}

	return $node;
}

/**
 * Vrátí ItemList všech vozidel — vhodné pro hlavní stránku
 * a archiv vozového parku.
 */
function amarilla_get_vehicle_itemlist_node( $limit = 12 ) {
	$query = new WP_Query( array(
		'post_type'      => 'vehicle',
		'posts_per_page' => $limit,
		'post_status'    => 'publish',
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	) );

	if ( ! $query->have_posts() ) {
		return null;
	}

	$items = array();
	$i = 1;
	foreach ( $query->posts as $p ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $i++,
			'url'      => get_permalink( $p->ID ),
			'name'     => get_the_title( $p->ID ),
		);
	}
	wp_reset_postdata();

	return array(
		'@type'           => 'ItemList',
		'name'            => __( 'Vozový park', 'amarilla' ),
		'numberOfItems'   => count( $items ),
		'itemListElement' => $items,
	);
}

/**
 * Vrátí BreadcrumbList podle aktuální stránky.
 */
function amarilla_get_breadcrumb_schema_node() {
	$items = array(
		array(
			'@type'    => 'ListItem',
			'position' => 1,
			'name'     => __( 'Domů', 'amarilla' ),
			'item'     => home_url( '/' ),
		),
	);

	if ( is_singular( 'vehicle' ) ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => 2,
			'name'     => __( 'Vozový park', 'amarilla' ),
			'item'     => amarilla_get_fleet_url(),
		);
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => 3,
			'name'     => get_the_title(),
			'item'     => get_permalink(),
		);
	} elseif ( is_singular( 'post' ) ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => 2,
			'name'     => __( 'Blog', 'amarilla' ),
			'item'     => get_post_type_archive_link( 'post' ) ?: home_url( '/blog/' ),
		);
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => 3,
			'name'     => get_the_title(),
			'item'     => get_permalink(),
		);
	} elseif ( is_post_type_archive( 'vehicle' ) ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => 2,
			'name'     => __( 'Vozový park', 'amarilla' ),
			'item'     => amarilla_get_fleet_url(),
		);
	} elseif ( is_page() ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => 2,
			'name'     => get_the_title(),
			'item'     => get_permalink(),
		);
	} else {
		return null; // např. archív, search — breadcrumb se nehodí
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);
}

/**
 * Vrátí Article schema pro blog post.
 */
function amarilla_get_article_schema_node( $post_id ) {
	$node = array(
		'@type'         => 'Article',
		'headline'      => get_the_title( $post_id ),
		'description'   => wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 40, '…' ),
		'datePublished' => get_the_date( 'c', $post_id ),
		'dateModified'  => get_the_modified_date( 'c', $post_id ),
		'author'        => array(
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) ),
		),
		'publisher'     => array( '@id' => home_url( '/#organization' ) ),
		'mainEntityOfPage' => array(
			'@type' => 'WebPage',
			'@id'   => get_permalink( $post_id ),
		),
	);
	if ( has_post_thumbnail( $post_id ) ) {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'large' );
		if ( $img ) {
			$node['image'] = $img[0];
		}
	}
	return $node;
}

/**
 * Hlavní výstup — sestavuje @graph dle kontextu a vypíše do <head>.
 */
function amarilla_output_schema() {
	// Skipnout na adminu, REST, feedech a 404.
	if ( is_admin() || is_404() ) {
		return;
	}

	$graph = array();

	// Vždy: business node
	$graph[] = amarilla_get_business_schema_node();

	// Breadcrumb (skoro vždy)
	$breadcrumb = amarilla_get_breadcrumb_schema_node();
	if ( $breadcrumb ) {
		$graph[] = $breadcrumb;
	}

	// Stránka-specifické nody
	if ( is_front_page() ) {
		$list = amarilla_get_vehicle_itemlist_node( 12 );
		if ( $list ) {
			$graph[] = $list;
		}
	} elseif ( is_singular( 'vehicle' ) ) {
		$graph[] = amarilla_get_vehicle_schema_node( get_the_ID() );
	} elseif ( is_post_type_archive( 'vehicle' ) ) {
		$list = amarilla_get_vehicle_itemlist_node( 24 );
		if ( $list ) {
			$graph[] = $list;
		}
	} elseif ( is_singular( 'post' ) ) {
		$graph[] = amarilla_get_article_schema_node( get_the_ID() );
	}

	if ( empty( $graph ) ) {
		return;
	}

	$schema = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}
add_action( 'wp_head', 'amarilla_output_schema', 5 );

/**
 * Admin notice — odkaz na Customizer při první aktivaci
 */
function amarilla_activation_notice() {
	$screen = get_current_screen();
	if ( ! $screen || $screen->id !== 'themes' ) {
		return;
	}
	if ( get_option( 'amarilla_notice_dismissed' ) ) {
		return;
	}
	?>
	<div class="notice notice-info is-dismissible">
		<p>
			<strong><?php esc_html_e( 'Amarilla Tenerife je aktivní.', 'amarilla' ); ?></strong>
			<?php
			printf(
				/* translators: %s: link to customizer */
				esc_html__( 'Nastavte logo, kontakty a obsah jednotlivých sekcí v %s.', 'amarilla' ),
				'<a href="' . esc_url( admin_url( 'customize.php' ) ) . '">' . esc_html__( 'Vzhled → Přizpůsobit', 'amarilla' ) . '</a>'
			);
			?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'amarilla_activation_notice' );
