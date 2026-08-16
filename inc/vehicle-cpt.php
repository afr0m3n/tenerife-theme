<?php
/**
 * Custom post type Vozidlo + taxonomie Kategorie vozidla
 *
 * @package Amarilla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registrace CPT vehicle
 */
function amarilla_register_vehicle_cpt() {
	$labels = array(
		'name'                  => __( 'Vozidla', 'amarilla' ),
		'singular_name'         => __( 'Vozidlo', 'amarilla' ),
		'menu_name'             => __( 'Vozový park', 'amarilla' ),
		'name_admin_bar'        => __( 'Vozidlo', 'amarilla' ),
		'add_new'               => __( 'Přidat nové', 'amarilla' ),
		'add_new_item'          => __( 'Přidat nové vozidlo', 'amarilla' ),
		'new_item'              => __( 'Nové vozidlo', 'amarilla' ),
		'edit_item'             => __( 'Upravit vozidlo', 'amarilla' ),
		'view_item'             => __( 'Zobrazit vozidlo', 'amarilla' ),
		'all_items'             => __( 'Všechna vozidla', 'amarilla' ),
		'search_items'          => __( 'Hledat vozidla', 'amarilla' ),
		'not_found'             => __( 'Žádná vozidla nenalezena', 'amarilla' ),
		'not_found_in_trash'    => __( 'V koši nejsou žádná vozidla', 'amarilla' ),
		'featured_image'        => __( 'Hlavní fotka vozidla', 'amarilla' ),
		'set_featured_image'    => __( 'Nastavit hlavní fotku', 'amarilla' ),
		'remove_featured_image' => __( 'Odstranit hlavní fotku', 'amarilla' ),
		'archives'              => __( 'Vozový park', 'amarilla' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'vozidla' ),
		'capability_type'    => 'post',
		'has_archive'        => 'vozovy-park',
		'hierarchical'       => false,
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-car',
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'custom-fields' ),
		'taxonomies'         => array( 'vehicle_category' ),
		'template'           => array(
			array(
				'core/gallery',
				array(
					'className'   => 'amarilla-vehicle-gallery',
					'columns'     => 3,
					'imageCrop'   => false,
					'fixedHeight' => false,
					'aspectRatio' => 'auto',
					'linkTo'      => 'none',
					'sizeSlug'    => 'large',
				),
			),
		),
		'template_lock'      => false,
	);

	register_post_type( 'vehicle', $args );
}
add_action( 'init', 'amarilla_register_vehicle_cpt' );

/**
 * Zobrazí na hlavním archivu celý vozový park bez stránkování.
 *
 * Query Loop neumí hodnotu perPage -1 (převádí ji přes absint), proto se
 * neomezený počet nastavuje až ve výsledných argumentech jeho WP_Query.
 */
function amarilla_vehicle_archive_query_loop_args( $query, $block ) {
	if ( is_admin() || is_tax() || ! is_post_type_archive( 'vehicle' ) || ! is_object( $block ) ) {
		return $query;
	}

	$block_query = isset( $block->context['query'] ) && is_array( $block->context['query'] )
		? $block->context['query']
		: array();

	if ( 'vehicle' !== ( $block_query['postType'] ?? '' ) ) {
		return $query;
	}

	$query['posts_per_page'] = -1;

	return $query;
}
add_filter( 'query_loop_block_query_vars', 'amarilla_vehicle_archive_query_loop_args', 10, 2 );

/**
 * Registrace taxonomie Kategorie vozidla
 */
function amarilla_register_vehicle_taxonomy() {
	$labels = array(
		'name'              => __( 'Kategorie', 'amarilla' ),
		'singular_name'     => __( 'Kategorie', 'amarilla' ),
		'search_items'      => __( 'Hledat kategorie', 'amarilla' ),
		'all_items'         => __( 'Všechny kategorie', 'amarilla' ),
		'edit_item'         => __( 'Upravit kategorii', 'amarilla' ),
		'update_item'       => __( 'Aktualizovat kategorii', 'amarilla' ),
		'add_new_item'      => __( 'Přidat novou kategorii', 'amarilla' ),
		'new_item_name'     => __( 'Název nové kategorie', 'amarilla' ),
		'menu_name'         => __( 'Kategorie', 'amarilla' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'kategorie-vozidel' ),
	);

	register_taxonomy( 'vehicle_category', array( 'vehicle' ), $args );

	// Předvyplnění základních kategorií při aktivaci šablony
	$default_categories = array(
		'ekonomy'     => __( 'Ekonomy', 'amarilla' ),
		'family'      => __( 'Family', 'amarilla' ),
		'suv'         => __( 'SUV', 'amarilla' ),
		'cabrio'      => __( 'Kabriolet', 'amarilla' ),
		'premium'     => __( 'Premium', 'amarilla' ),
		'minivan'     => __( 'Minivan', 'amarilla' ),
	);

	foreach ( $default_categories as $slug => $name ) {
		if ( ! term_exists( $slug, 'vehicle_category' ) ) {
			wp_insert_term( $name, 'vehicle_category', array( 'slug' => $slug ) );
		}
	}
}
add_action( 'init', 'amarilla_register_vehicle_taxonomy' );

/**
 * Registrace meta polí pro vozidlo (REST API ready, kompatibilní s block bindings)
 */
function amarilla_register_vehicle_meta() {
	$meta_fields = array(
		'_vehicle_seats'        => array( 'type' => 'integer', 'description' => __( 'Počet míst', 'amarilla' ) ),
		'_vehicle_doors'        => array( 'type' => 'integer', 'description' => __( 'Počet dveří', 'amarilla' ) ),
		'_vehicle_transmission' => array( 'type' => 'string', 'description' => __( 'Převodovka (manuál/automat)', 'amarilla' ) ),
		'_vehicle_fuel'         => array( 'type' => 'string', 'description' => __( 'Palivo', 'amarilla' ) ),
		'_vehicle_luggage'      => array( 'type' => 'string', 'description' => __( 'Kufr', 'amarilla' ) ),
		'_vehicle_ac'           => array( 'type' => 'boolean', 'description' => __( 'Klimatizace', 'amarilla' ) ),
		'_vehicle_price'        => array( 'type' => 'string', 'description' => __( 'Cena za den', 'amarilla' ) ),
		'_vehicle_tagline'      => array( 'type' => 'string', 'description' => __( 'Krátký popis pod název', 'amarilla' ) ),
		'_vehicle_label'        => array( 'type' => 'string', 'description' => __( 'Štítek (např. Nejoblíbenější)', 'amarilla' ) ),
		'_vehicle_rating'       => array( 'type' => 'string', 'description' => __( 'Hodnocení (např. 4.8) — pro schema.org', 'amarilla' ) ),
		'_vehicle_rating_count' => array( 'type' => 'integer', 'description' => __( 'Počet hodnocení — pro schema.org', 'amarilla' ) ),
	);

	foreach ( $meta_fields as $key => $args ) {
		$sanitize = 'sanitize_text_field';
		if ( $args['type'] === 'integer' ) {
			$sanitize = 'absint';
		} elseif ( $args['type'] === 'boolean' ) {
			$sanitize = 'rest_sanitize_boolean';
		}
		register_post_meta( 'vehicle', $key, array(
			'show_in_rest'      => true,
			'single'            => true,
			'type'              => $args['type'],
			'description'       => $args['description'],
			'sanitize_callback' => $sanitize,
			'auth_callback'     => function() {
				return current_user_can( 'edit_posts' );
			},
		) );
	}
}
add_action( 'init', 'amarilla_register_vehicle_meta' );

/**
 * Meta box pro správu vozidlových polí v adminu
 */
function amarilla_add_vehicle_meta_box() {
	add_meta_box(
		'amarilla_vehicle_details',
		__( 'Specifikace vozidla', 'amarilla' ),
		'amarilla_render_vehicle_meta_box',
		'vehicle',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'amarilla_add_vehicle_meta_box' );

/**
 * Vykreslení meta boxu
 */
function amarilla_render_vehicle_meta_box( $post ) {
	wp_nonce_field( 'amarilla_save_vehicle_meta', 'amarilla_vehicle_nonce' );

	$seats        = get_post_meta( $post->ID, '_vehicle_seats', true );
	$doors        = get_post_meta( $post->ID, '_vehicle_doors', true );
	$transmission = get_post_meta( $post->ID, '_vehicle_transmission', true );
	$fuel         = get_post_meta( $post->ID, '_vehicle_fuel', true );
	$luggage      = get_post_meta( $post->ID, '_vehicle_luggage', true );
	$ac           = get_post_meta( $post->ID, '_vehicle_ac', true );
	$price        = get_post_meta( $post->ID, '_vehicle_price', true );
	$tagline      = get_post_meta( $post->ID, '_vehicle_tagline', true );
	$label        = get_post_meta( $post->ID, '_vehicle_label', true );
	$rating       = get_post_meta( $post->ID, '_vehicle_rating', true );
	$rating_count = get_post_meta( $post->ID, '_vehicle_rating_count', true );
	?>
	<style>
		.amarilla-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
		.amarilla-meta-grid label { display: block; font-weight: 600; margin-bottom: 6px; }
		.amarilla-meta-grid input[type=text], .amarilla-meta-grid input[type=number], .amarilla-meta-grid select { width: 100%; }
		.amarilla-meta-full { grid-column: 1 / -1; }
		.amarilla-help { color: #757575; font-size: 12px; margin-top: 4px; }
	</style>
	<div class="amarilla-meta-grid">
		<div class="amarilla-meta-full">
			<label for="vehicle_tagline"><?php _e( 'Krátký popis (pod název)', 'amarilla' ); ?></label>
			<input type="text" id="vehicle_tagline" name="vehicle_tagline" value="<?php echo esc_attr( $tagline ); ?>" placeholder="<?php esc_attr_e( 'např. Malý, mrštný, ideální do města', 'amarilla' ); ?>">
		</div>

		<div>
			<label for="vehicle_seats"><?php _e( 'Počet míst', 'amarilla' ); ?></label>
			<input type="number" id="vehicle_seats" name="vehicle_seats" value="<?php echo esc_attr( $seats ); ?>" min="1" max="9">
		</div>

		<div>
			<label for="vehicle_doors"><?php _e( 'Počet dveří', 'amarilla' ); ?></label>
			<input type="number" id="vehicle_doors" name="vehicle_doors" value="<?php echo esc_attr( $doors ); ?>" min="2" max="5">
		</div>

		<div>
			<label for="vehicle_transmission"><?php _e( 'Převodovka', 'amarilla' ); ?></label>
			<select id="vehicle_transmission" name="vehicle_transmission">
				<option value=""><?php _e( '— vyberte —', 'amarilla' ); ?></option>
				<option value="manual" <?php selected( $transmission, 'manual' ); ?>><?php _e( 'Manuál', 'amarilla' ); ?></option>
				<option value="automatic" <?php selected( $transmission, 'automatic' ); ?>><?php _e( 'Automat', 'amarilla' ); ?></option>
			</select>
		</div>

		<div>
			<label for="vehicle_fuel"><?php _e( 'Palivo', 'amarilla' ); ?></label>
			<select id="vehicle_fuel" name="vehicle_fuel">
				<option value=""><?php _e( '— vyberte —', 'amarilla' ); ?></option>
				<option value="petrol" <?php selected( $fuel, 'petrol' ); ?>><?php _e( 'Benzín', 'amarilla' ); ?></option>
				<option value="diesel" <?php selected( $fuel, 'diesel' ); ?>><?php _e( 'Nafta', 'amarilla' ); ?></option>
				<option value="hybrid" <?php selected( $fuel, 'hybrid' ); ?>><?php _e( 'Hybrid', 'amarilla' ); ?></option>
				<option value="electric" <?php selected( $fuel, 'electric' ); ?>><?php _e( 'Elektro', 'amarilla' ); ?></option>
			</select>
		</div>

		<div>
			<label for="vehicle_luggage"><?php _e( 'Kufr (litry nebo počet)', 'amarilla' ); ?></label>
			<input type="text" id="vehicle_luggage" name="vehicle_luggage" value="<?php echo esc_attr( $luggage ); ?>" placeholder="<?php esc_attr_e( 'např. 350 l nebo 2 velké kufry', 'amarilla' ); ?>">
		</div>

		<div>
			<label for="vehicle_ac"><?php _e( 'Klimatizace', 'amarilla' ); ?></label>
			<select id="vehicle_ac" name="vehicle_ac">
				<option value="1" <?php selected( $ac, '1' ); ?>><?php _e( 'Ano', 'amarilla' ); ?></option>
				<option value="0" <?php selected( $ac, '0' ); ?>><?php _e( 'Ne', 'amarilla' ); ?></option>
			</select>
		</div>

		<div class="amarilla-meta-full">
			<label for="vehicle_price"><?php _e( 'Cena za den', 'amarilla' ); ?></label>
			<input type="text" id="vehicle_price" name="vehicle_price" value="<?php echo esc_attr( $price ); ?>" placeholder="<?php esc_attr_e( 'např. 25 € nebo nechte prázdné pro „Poptat"', 'amarilla' ); ?>">
			<p class="amarilla-help"><?php _e( 'Pokud necháte pole prázdné, zobrazí se místo ceny tlačítko „Poptat termín".', 'amarilla' ); ?></p>
		</div>

		<div class="amarilla-meta-full">
			<label for="vehicle_label"><?php _e( 'Štítek (volitelný)', 'amarilla' ); ?></label>
			<input type="text" id="vehicle_label" name="vehicle_label" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php esc_attr_e( 'např. Nejoblíbenější, Novinka, Sleva 20 %', 'amarilla' ); ?>">
		</div>

		<div>
			<label for="vehicle_rating"><?php _e( 'Hodnocení (1–5)', 'amarilla' ); ?></label>
			<input type="text" id="vehicle_rating" name="vehicle_rating" value="<?php echo esc_attr( $rating ); ?>" placeholder="4.8">
			<p class="amarilla-help"><?php _e( 'Pro rich snippet ve Googlu. Necháte-li prázdné, hvězdičky se v SERP nezobrazí.', 'amarilla' ); ?></p>
		</div>

		<div>
			<label for="vehicle_rating_count"><?php _e( 'Počet hodnocení', 'amarilla' ); ?></label>
			<input type="number" id="vehicle_rating_count" name="vehicle_rating_count" value="<?php echo esc_attr( $rating_count ); ?>" min="0" placeholder="24">
			<p class="amarilla-help"><?php _e( 'Reálný počet — Google fake hodnocení penalizuje.', 'amarilla' ); ?></p>
		</div>
	</div>
	<?php
}

/**
 * Uložení meta dat
 */
function amarilla_save_vehicle_meta( $post_id ) {
	if ( ! isset( $_POST['amarilla_vehicle_nonce'] ) || ! wp_verify_nonce( $_POST['amarilla_vehicle_nonce'], 'amarilla_save_vehicle_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'vehicle_seats'        => array( 'meta' => '_vehicle_seats',        'sanitize' => 'absint' ),
		'vehicle_doors'        => array( 'meta' => '_vehicle_doors',        'sanitize' => 'absint' ),
		'vehicle_transmission' => array( 'meta' => '_vehicle_transmission', 'sanitize' => 'sanitize_text_field' ),
		'vehicle_fuel'         => array( 'meta' => '_vehicle_fuel',         'sanitize' => 'sanitize_text_field' ),
		'vehicle_luggage'      => array( 'meta' => '_vehicle_luggage',      'sanitize' => 'sanitize_text_field' ),
		'vehicle_ac'           => array( 'meta' => '_vehicle_ac',           'sanitize' => 'sanitize_text_field' ),
		'vehicle_price'        => array( 'meta' => '_vehicle_price',        'sanitize' => 'sanitize_text_field' ),
		'vehicle_tagline'      => array( 'meta' => '_vehicle_tagline',      'sanitize' => 'sanitize_text_field' ),
		'vehicle_label'        => array( 'meta' => '_vehicle_label',        'sanitize' => 'sanitize_text_field' ),
		'vehicle_rating'       => array( 'meta' => '_vehicle_rating',       'sanitize' => 'sanitize_text_field' ),
		'vehicle_rating_count' => array( 'meta' => '_vehicle_rating_count', 'sanitize' => 'absint' ),
	);

	foreach ( $fields as $key => $config ) {
		if ( isset( $_POST[ $key ] ) ) {
			$value = call_user_func( $config['sanitize'], wp_unslash( $_POST[ $key ] ) );
			update_post_meta( $post_id, $config['meta'], $value );
		}
	}
}
add_action( 'save_post_vehicle', 'amarilla_save_vehicle_meta' );

/**
 * Při aktivaci tématu — flush rewrite rules
 */
function amarilla_after_switch_theme() {
	amarilla_register_vehicle_cpt();
	amarilla_register_vehicle_taxonomy();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'amarilla_after_switch_theme' );
