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
	return get_theme_mod( 'amarilla_phone', '+34 922 000 000' );
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
	return get_theme_mod( 'amarilla_email', 'info@autopujcovna-tenerife.cz' );
}

/**
 * Adresa (jeden řádek)
 */
function amarilla_get_address() {
	return get_theme_mod( 'amarilla_address', __( 'Letiště Tenerife Sur, Avenida Bruselas, 38660', 'amarilla' ) );
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
	$hours = get_theme_mod( 'amarilla_hours', '' );
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
