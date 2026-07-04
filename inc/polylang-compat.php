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
 * Vykresli sdílený markup jazykového přepínače.
 *
 * Desktop používá plný seznam odkazů, mobil kompaktní tlačítko
 * se stejnými odkazy v rozbalovacím seznamu.
 *
 * @param array $languages Pole jazyků z Polylangu nebo fallback.
 * @return string
 */
function amarilla_render_language_switcher( $languages ) {
	$current_label = '';

	foreach ( $languages as $lang ) {
		$slug = isset( $lang['slug'] ) ? (string) $lang['slug'] : '';
		if ( '' === $slug ) {
			continue;
		}

		$label = strtoupper( $slug );
		if ( '' === $current_label || ! empty( $lang['current_lang'] ) ) {
			$current_label = $label;
		}
	}

	if ( '' === $current_label ) {
		$current_label = 'CZ';
	}

	$output  = sprintf(
		'<nav class="amarilla-lang-switcher" aria-label="%s">',
		esc_attr__( 'Výběr jazyka', 'amarilla' )
	);
	$output .= '<div class="amarilla-lang-mobile">';
	$output .= sprintf(
		'<button type="button" class="amarilla-lang-toggle" aria-expanded="false" aria-label="%s"><span class="amarilla-lang-current">%s</span></button>',
		esc_attr__( 'Vybrat jazyk', 'amarilla' ),
		esc_html( $current_label )
	);
	$output .= '</div>';
	$output .= '<div class="amarilla-lang-list">';

	foreach ( $languages as $lang ) {
		$slug = isset( $lang['slug'] ) ? (string) $lang['slug'] : '';
		if ( '' === $slug ) {
			continue;
		}

		$active       = ! empty( $lang['current_lang'] ) ? ' active' : '';
		$aria_current = ! empty( $lang['current_lang'] ) ? ' aria-current="true"' : '';
		$url          = isset( $lang['url'] ) ? $lang['url'] : '#';

		$output .= sprintf(
			'<a href="%s" class="lang-link%s" hreflang="%s"%s>%s</a>',
			esc_url( $url ),
			esc_attr( $active ),
			esc_attr( $slug ),
			$aria_current,
			esc_html( strtoupper( $slug ) )
		);
	}

	$output .= '</div>';
	$output .= '</nav>';

	return $output;
}

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

	return amarilla_render_language_switcher( $languages );
}

/**
 * Shortcode pro přepínač jazyků — pro použití v block patternech
 */
function amarilla_language_switcher_shortcode() {
	$switcher = amarilla_language_switcher();

	if ( empty( $switcher ) ) {
		// Fallback bez Polylangu — statické zkratky.
		return amarilla_render_language_switcher(
			array(
				array(
					'slug'         => 'cz',
					'url'          => '#',
					'current_lang' => true,
				),
				array(
					'slug'         => 'en',
					'url'          => '#',
					'current_lang' => false,
				),
				array(
					'slug'         => 'es',
					'url'          => '#',
					'current_lang' => false,
				),
				array(
					'slug'         => 'de',
					'url'          => '#',
					'current_lang' => false,
				),
			)
		);
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
