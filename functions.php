<?php
/**
 * Amarilla Tenerife — funkce šablony
 *
 * @package Amarilla
 * @since 1.0.0 (1.1.0 — rozšířený Customizer; 1.2.0 — self-hosted fonty,
 *               poptávkový formulář, sezónní ceny, pobočky, blog, rozšířené schema.org)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AMARILLA_VERSION', '1.2.1' );
define( 'AMARILLA_DIR', get_template_directory() );
define( 'AMARILLA_URI', get_template_directory_uri() );

/**
 * Theme setup — podpora WP funkcí
 */
function amarilla_setup() {
	// Překlady
	load_theme_textdomain( 'amarilla', AMARILLA_DIR . '/languages' );

	// Standardní podpora
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	// Velikosti náhledů pro vozidla
	add_image_size( 'amarilla-vehicle-card', 800, 560, true );
	add_image_size( 'amarilla-vehicle-detail', 1600, 1100, true );
	add_image_size( 'amarilla-hero', 1920, 1200, true );

	// Editor styles — načteme i hlavní theme.css, aby editor odpovídal frontendu
	add_editor_style( array( 'assets/css/theme.css', 'assets/css/editor.css' ) );
}
add_action( 'after_setup_theme', 'amarilla_setup' );

/**
 * Načtení frontend stylů a scriptů
 *
 * Fonty Fraunces a DM Sans jsou self-hostované — registrace přes
 * theme.json (fontFace → file:./assets/fonts/...). Žádné spojení
 * s fonts.googleapis.com / fonts.gstatic.com (GDPR-friendly).
 */
function amarilla_enqueue_assets() {
	wp_enqueue_style(
		'amarilla-style',
		get_stylesheet_uri(),
		array(),
		AMARILLA_VERSION
	);

	wp_enqueue_style(
		'amarilla-main',
		AMARILLA_URI . '/assets/css/theme.css',
		array(),
		AMARILLA_VERSION
	);

	wp_enqueue_script(
		'amarilla-main',
		AMARILLA_URI . '/assets/js/theme.js',
		array(),
		AMARILLA_VERSION,
		array( 'in_footer' => true, 'strategy' => 'defer' )
	);

	if ( is_singular( 'vehicle' ) ) {
		wp_enqueue_script(
			'amarilla-vehicle-gallery',
			AMARILLA_URI . '/assets/js/vehicle-gallery.js',
			array(),
			AMARILLA_VERSION,
			array( 'in_footer' => true, 'strategy' => 'defer' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'amarilla_enqueue_assets' );

/**
 * Preload kritických tváří fontů — minimalizuje FOIT a zlepší LCP.
 * Fonty jsou self-hostované, takže preload je „zdarma" (žádné cross-origin),
 * ale crossorigin="anonymous" je u woff2 stále povinné, jinak prohlížeč
 * preload zahodí. Latin-ext varianta pokrývá i Latin (díky unicode-range).
 */
function amarilla_preload_fonts() {
	$preload_fonts = array(
		'/assets/fonts/dm-sans-latin-ext-wght.woff2',
		'/assets/fonts/fraunces-latin-ext-wght.woff2',
	);
	foreach ( $preload_fonts as $font_path ) {
		printf(
			'<link rel="preload" as="font" type="font/woff2" href="%s" crossorigin="anonymous">' . "\n",
			esc_url( AMARILLA_URI . $font_path )
		);
	}
}
add_action( 'wp_head', 'amarilla_preload_fonts', 2 );

/**
 * Includes
 */
require_once AMARILLA_DIR . '/inc/vehicle-cpt.php';
require_once AMARILLA_DIR . '/inc/seasonal-pricing.php';     // NEW v1.2.0
require_once AMARILLA_DIR . '/inc/block-bindings.php';
require_once AMARILLA_DIR . '/inc/helpers.php';
require_once AMARILLA_DIR . '/inc/customizer.php';
require_once AMARILLA_DIR . '/inc/content-shortcodes.php';
require_once AMARILLA_DIR . '/inc/inquiry-form.php';         // NEW v1.2.0
require_once AMARILLA_DIR . '/inc/locations.php';            // NEW v1.2.0
require_once AMARILLA_DIR . '/inc/blog.php';                 // NEW v1.2.0
require_once AMARILLA_DIR . '/inc/polylang-compat.php';

if ( is_admin() ) {
	require_once AMARILLA_DIR . '/inc/admin-vehicle-duplicate.php';
}

/**
 * Registrace block patterns kategorií
 */
function amarilla_register_pattern_categories() {
	register_block_pattern_category( 'amarilla', array(
		'label'       => __( 'Amarilla — sekce', 'amarilla' ),
		'description' => __( 'Předpřipravené sekce pro autopůjčovnu', 'amarilla' ),
	) );
}
add_action( 'init', 'amarilla_register_pattern_categories' );

/**
 * Bezpečnostní úpravy
 */
function amarilla_security_headers() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
}
add_action( 'init', 'amarilla_security_headers' );

/**
 * Lazy loading do obrázků v obsahu
 */
function amarilla_add_lazy_loading( $content ) {
	return str_replace( '<img ', '<img loading="lazy" decoding="async" ', $content );
}
add_filter( 'the_content', 'amarilla_add_lazy_loading' );
