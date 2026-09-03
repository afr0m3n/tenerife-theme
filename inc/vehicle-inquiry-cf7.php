<?php
/**
 * Propojení detailu vozidla s formulářem Contact Form 7.
 *
 * @package Amarilla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vrátí validní publikované vozidlo z query parametru requested_vehicle.
 *
 * @return WP_Post|null
 */
function amarilla_get_requested_vehicle() {
	if ( ! isset( $_GET['requested_vehicle'] ) ) {
		return null;
	}

	$value = wp_unslash( $_GET['requested_vehicle'] );
	if ( ! is_scalar( $value ) ) {
		return null;
	}

	$post_id = absint( $value );
	$post    = $post_id ? get_post( $post_id ) : null;

	if ( ! $post || 'vehicle' !== $post->post_type || 'publish' !== $post->post_status ) {
		return null;
	}

	return $post;
}

/**
 * Najde ID formulářů vložených na stránce poptávky nebo formulář s jejím názvem.
 *
 * Scope podle ID funguje i při REST odeslání CF7, kdy už není k dispozici hlavní
 * page query. Seznam lze upravit filtrem bez natvrdo uloženého DEV/produkčního ID.
 *
 * @return array
 */
function amarilla_get_inquiry_cf7_form_ids() {
	static $cached_form_ids = null;

	if ( null !== $cached_form_ids ) {
		return $cached_form_ids;
	}

	$form_ids     = array();
	$inquiry_page = get_page_by_path( 'nezavazna-poptavka' );

	if ( $inquiry_page && function_exists( 'parse_blocks' ) ) {
		$collect_block_form_ids = static function ( $blocks ) use ( &$collect_block_form_ids, &$form_ids ) {
			foreach ( $blocks as $block ) {
				$block_form_id = absint( $block['attrs']['id'] ?? 0 );

				if (
					'contact-form-7/contact-form-selector' === ( $block['blockName'] ?? '' )
					&& $block_form_id
				) {
					$form_ids[] = (string) $block_form_id;
				}

				if ( ! empty( $block['innerBlocks'] ) ) {
					$collect_block_form_ids( $block['innerBlocks'] );
				}
			}
		};

		$collect_block_form_ids( parse_blocks( $inquiry_page->post_content ) );
	}

	if ( $inquiry_page && has_shortcode( $inquiry_page->post_content, 'contact-form-7' ) ) {
		$pattern = get_shortcode_regex( array( 'contact-form-7' ) );
		if ( preg_match_all( '/' . $pattern . '/s', $inquiry_page->post_content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$attributes = shortcode_parse_atts( $match[3] );
				if ( is_array( $attributes ) && isset( $attributes['id'] ) ) {
					$form_ids[] = (string) $attributes['id'];
				}
			}
		}
	}

	$named_forms = get_posts(
		array(
			'post_type'        => 'wpcf7_contact_form',
			'post_status'      => 'publish',
			'posts_per_page'   => -1,
			'fields'           => 'ids',
			'no_found_rows'    => true,
			'suppress_filters' => false,
		)
	);

	foreach ( $named_forms as $form_id ) {
		if ( 'Nezávazná poptávka' === get_the_title( $form_id ) ) {
			$form_ids[] = (string) $form_id;
		}
	}

	/**
	 * Filtruje ID CF7 formulářů používaných pro nezávaznou poptávku.
	 *
	 * @param array $form_ids ID formulářů jako řetězce.
	 */
	$form_ids = apply_filters( 'amarilla_inquiry_cf7_form_ids', $form_ids );

	$cached_form_ids = array_values( array_unique( array_map( 'strval', (array) $form_ids ) ) );

	return $cached_form_ids;
}

/**
 * Ověří, že CF7 právě zpracovává formulář nezávazné poptávky.
 *
 * @return bool
 */
function amarilla_is_inquiry_cf7_form() {
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		return false;
	}

	$contact_form = WPCF7_ContactForm::get_current();

	return $contact_form
		&& in_array( (string) $contact_form->id(), amarilla_get_inquiry_cf7_form_ids(), true );
}

/**
 * Vrátí publikovaná vozidla pro poptávkový formulář.
 *
 * @return WP_Post[]
 */
function amarilla_get_inquiry_vehicles() {
	static $vehicles = null;

	if ( null === $vehicles ) {
		$vehicles = get_posts(
			array(
				'post_type'        => 'vehicle',
				'post_status'      => 'publish',
				'posts_per_page'   => -1,
				'orderby'          => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
				'no_found_rows'    => true,
				'suppress_filters' => false,
			)
		);
	}

	return $vehicles;
}

/**
 * Přepíše možnosti CF7 selectu a volitelně nastaví výchozí hodnotu.
 *
 * Hodnota a label jsou záměrně stejné. CF7 tedy validuje i odesílá čitelný
 * název bez pipe transformace a běžný i _raw_ mail-tag vrátí stejný text.
 *
 * @param array  $tag      Parsovaný CF7 form-tag.
 * @param array  $choices  Seznam hodnot selectu.
 * @param string $selected      Výchozí hodnota.
 * @param bool   $first_as_label Použít první hodnotu jako neodesílatelný popisek.
 * @return array
 */
function amarilla_set_cf7_select_choices( $tag, $choices, $selected = '', $first_as_label = false ) {
	$values = array_values( array_unique( array_map( 'strval', $choices ) ) );
	$options = array_filter(
		(array) $tag['options'],
		static function ( $option ) {
			return ! preg_match( '/^(?:default:|include_blank$|first_as_label$)/', $option );
		}
	);
	if ( $first_as_label ) {
		$options[] = 'first_as_label';
	}

	$selected_index = array_search( $selected, $values, true );
	if ( false !== $selected_index ) {
		$options[] = 'default:' . ( $selected_index + 1 );
	}

	$tag['options']    = array_values( $options );
	$tag['raw_values'] = $values;
	$tag['values']     = $values;
	$tag['labels']     = $values;

	if ( class_exists( 'WPCF7_Pipes' ) ) {
		$tag['pipes'] = new WPCF7_Pipes();
	}

	return $tag;
}

/**
 * Dynamicky naplní selecty vehicle a transmission v cílovém CF7 formuláři.
 *
 * @param array $tag Parsovaný CF7 form-tag.
 * @return array
 */
function amarilla_filter_vehicle_inquiry_cf7_tag( $tag ) {
	if (
		! is_array( $tag )
		|| ! amarilla_is_inquiry_cf7_form()
		|| ! in_array( $tag['basetype'] ?? '', array( 'select' ), true )
	) {
		return $tag;
	}

	$requested_vehicle = amarilla_get_requested_vehicle();

	if ( 'select-auto' === ( $tag['name'] ?? '' ) ) {
		$choices = array( __( '— Vyberte vůz —', 'amarilla' ) );

		foreach ( amarilla_get_inquiry_vehicles() as $vehicle ) {
			$choices[] = get_the_title( $vehicle );
		}

		$selected = $requested_vehicle ? get_the_title( $requested_vehicle ) : '';

		return amarilla_set_cf7_select_choices( $tag, $choices, $selected, true );
	}

	if ( 'transmission' === ( $tag['name'] ?? '' ) ) {
		$choices = array(
			__( 'Je mi to jedno', 'amarilla' ),
			__( 'Manuál', 'amarilla' ),
			__( 'Automat', 'amarilla' ),
		);

		if ( $requested_vehicle ) {
			$transmissions = amarilla_get_vehicle_transmissions( $requested_vehicle->ID );
			if ( 1 === count( $transmissions ) ) {
				$label   = 'manual' === $transmissions[0] ? __( 'Manuál', 'amarilla' ) : __( 'Automat', 'amarilla' );
				$choices = array( $label );
			}
		}

		return amarilla_set_cf7_select_choices( $tag, $choices, (string) reset( $choices ) );
	}

	return $tag;
}
add_filter( 'wpcf7_form_tag', 'amarilla_filter_vehicle_inquiry_cf7_tag' );

/**
 * Přidá do cílového CF7 formuláře malou mapu vozidlo => převodovky pro JS.
 *
 * @param string $html Vyrenderované prvky formuláře.
 * @return string
 */
function amarilla_add_vehicle_transmission_data_to_cf7( $html ) {
	if (
		! amarilla_is_inquiry_cf7_form()
		|| false === strpos( $html, 'name="select-auto"' )
		|| false === strpos( $html, 'name="transmission"' )
	) {
		return $html;
	}

	$vehicles = array();
	foreach ( amarilla_get_inquiry_vehicles() as $vehicle ) {
		$vehicles[ get_the_title( $vehicle ) ] = amarilla_get_vehicle_transmissions( $vehicle->ID );
	}

	$data = array(
		'vehicles' => $vehicles,
		'labels'   => array(
			'any'       => __( 'Je mi to jedno', 'amarilla' ),
			'manual'    => __( 'Manuál', 'amarilla' ),
			'automatic' => __( 'Automat', 'amarilla' ),
		),
	);
	$json = wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );

	if ( false === $json ) {
		return $html;
	}

	return $html . sprintf(
		'<script type="application/json" class="amarilla-vehicle-transmission-data">%s</script>',
		$json
	);
}
add_filter( 'wpcf7_form_elements', 'amarilla_add_vehicle_transmission_data_to_cf7', 20 );
