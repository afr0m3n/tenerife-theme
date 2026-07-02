<?php
/**
 * Polylang kompatibilita — registrace překladových řetězců
 * a podpora pro přepínač jazyků v hlavičce.
 *
 * @package Amarilla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registrace řetězců pro překlad v Polylangu
 */
function amarilla_register_polylang_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}

	$strings = array(
		'tagline'           => __( 'Autopůjčovna na Tenerife', 'amarilla' ),
		'cta_inquire'       => __( 'Nezávazná poptávka', 'amarilla' ),
		'cta_view_fleet'    => __( 'Prohlédnout vozy', 'amarilla' ),
		'cta_full_fleet'    => __( 'Zobrazit celý vozový park', 'amarilla' ),
		'cta_contact_us'    => __( 'Poptat termín', 'amarilla' ),
		'topbar_phone'      => __( 'Volejte 24/7', 'amarilla' ),
		'topbar_location'   => __( 'Letiště Tenerife Sur (TFS) i Norte (TFN)', 'amarilla' ),
		'fleet_eyebrow'     => __( 'Vozový park', 'amarilla' ),
		'fleet_subtitle'    => __( 'Auta pro každý výlet po ostrově', 'amarilla' ),
		'why_eyebrow'       => __( 'Proč si vybrat nás', 'amarilla' ),
		'tips_eyebrow'      => __( 'Tipy z ostrova', 'amarilla' ),
		'inquire_button'    => __( 'Poptat tento vůz', 'amarilla' ),
	);

	foreach ( $strings as $name => $value ) {
		pll_register_string( $name, $value, 'Amarilla', false );
	}
}
add_action( 'init', 'amarilla_register_polylang_strings', 20 );

/**
 * Přepínač jazyků pro hlavičku — vrací HTML s vlajkami/zkratkami
 *
 * Pokud Polylang není aktivní, vrátí prázdný string a v šabloně
 * se použije fallback statický seznam (zobrazí se jen CZ).
 */
function amarilla_language_switcher() {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return '';
	}

	$languages = pll_the_languages( array(
		'raw'                    => 1,
		'hide_if_empty'          => 0,
		'display_names_as'       => 'slug',
		'hide_current'           => 0,
	) );

	if ( empty( $languages ) ) {
		return '';
	}

	$output = '<div class="amarilla-lang-switcher">';
	foreach ( $languages as $lang ) {
		$active = ! empty( $lang['current_lang'] ) ? ' active' : '';
		$output .= sprintf(
			'<a href="%s" class="lang-link%s" hreflang="%s">%s</a>',
			esc_url( $lang['url'] ),
			esc_attr( $active ),
			esc_attr( $lang['slug'] ),
			esc_html( strtoupper( $lang['slug'] ) )
		);
	}
	$output .= '</div>';

	return $output;
}

/**
 * Shortcode pro přepínač jazyků — pro použití v block patternech
 */
function amarilla_language_switcher_shortcode() {
	$switcher = amarilla_language_switcher();

	if ( empty( $switcher ) ) {
		// Fallback bez Polylangu — statické zkratky
		return '<div class="amarilla-lang-switcher">
			<a href="#" class="lang-link active">CZ</a>
			<a href="#" class="lang-link">EN</a>
			<a href="#" class="lang-link">ES</a>
			<a href="#" class="lang-link">DE</a>
		</div>';
	}

	return $switcher;
}
add_shortcode( 'amarilla_languages', 'amarilla_language_switcher_shortcode' );

/**
 * Polylang detection — boolean pro šablony
 */
function amarilla_is_polylang_active() {
	return function_exists( 'pll_current_language' );
}
