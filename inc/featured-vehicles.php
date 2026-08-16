<?php
/**
 * Dynamický blok doporučených vozidel pro úvodní stránku.
 *
 * @package Amarilla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const AMARILLA_HOME_FEATURED_META       = '_amarilla_home_featured';
const AMARILLA_HOME_FEATURED_ORDER_META = '_amarilla_home_featured_order';

/**
 * Sanitizuje pořadí doporučeného vozidla.
 *
 * @param mixed $value Hodnota meta pole.
 * @return int
 */
function amarilla_sanitize_home_featured_order( $value ): int {
	return intval( $value );
}

/**
 * Ověří oprávnění k úpravě interních meta polí vozidla.
 *
 * @param bool   $allowed  Aktuální výsledek autorizace.
 * @param string $meta_key Název meta pole.
 * @param int    $post_id  ID vozidla.
 * @return bool
 */
function amarilla_auth_home_featured_meta( $allowed, $meta_key, $post_id ): bool {
	return current_user_can( 'edit_post', $post_id );
}

/**
 * Registruje interní meta hodnoty pro ruční výběr vozidel na homepage.
 */
function amarilla_register_home_featured_meta(): void {
	register_post_meta(
		'vehicle',
		AMARILLA_HOME_FEATURED_META,
		array(
			'show_in_rest'      => false,
			'single'            => true,
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'auth_callback'     => 'amarilla_auth_home_featured_meta',
		)
	);

	register_post_meta(
		'vehicle',
		AMARILLA_HOME_FEATURED_ORDER_META,
		array(
			'show_in_rest'      => false,
			'single'            => true,
			'type'              => 'integer',
			'sanitize_callback' => 'amarilla_sanitize_home_featured_order',
			'auth_callback'     => 'amarilla_auth_home_featured_meta',
		)
	);
}
add_action( 'init', 'amarilla_register_home_featured_meta' );

/**
 * Porovná doporučená vozidla podle ručního pořadí a stabilních fallbacků.
 *
 * Vozidla bez ručně vyplněného pořadí jsou zařazena až za vozidla s pořadím.
 *
 * @param WP_Post $first  První vozidlo.
 * @param WP_Post $second Druhé vozidlo.
 * @return int
 */
function amarilla_compare_home_featured_vehicles( WP_Post $first, WP_Post $second ): int {
	$first_order_raw  = get_post_meta( $first->ID, AMARILLA_HOME_FEATURED_ORDER_META, true );
	$second_order_raw = get_post_meta( $second->ID, AMARILLA_HOME_FEATURED_ORDER_META, true );
	$first_order      = filter_var( $first_order_raw, FILTER_VALIDATE_INT );
	$second_order     = filter_var( $second_order_raw, FILTER_VALIDATE_INT );
	$first_has_order  = false !== $first_order;
	$second_has_order = false !== $second_order;

	if ( $first_has_order !== $second_has_order ) {
		return $first_has_order ? -1 : 1;
	}

	if ( $first_has_order && $first_order !== $second_order ) {
		return $first_order <=> $second_order;
	}

	if ( (int) $first->menu_order !== (int) $second->menu_order ) {
		return (int) $first->menu_order <=> (int) $second->menu_order;
	}

	$title_comparison = strnatcasecmp( $first->post_title, $second->post_title );
	if ( 0 !== $title_comparison ) {
		return $title_comparison;
	}

	return $first->ID <=> $second->ID;
}

/**
 * Vrátí ID vozidel pro dynamickou homepage sekci.
 *
 * Nejprve vybírá ručně označená publikovaná vozidla. Volná místa doplní
 * publikovanými vozidly podle menu_order, bez duplicit.
 *
 * @param int $limit Maximální počet vozidel.
 * @return int[]
 */
function amarilla_get_home_featured_vehicle_ids( int $limit = 3 ): array {
	$limit = absint( $limit );
	if ( 0 === $limit ) {
		return array();
	}

	$featured_query = new WP_Query(
		array(
			'post_type'           => 'vehicle',
			'post_status'         => 'publish',
			'posts_per_page'      => -1,
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'meta_query'          => array(
				array(
					'key'     => AMARILLA_HOME_FEATURED_META,
					'value'   => '1',
					'compare' => '=',
				),
			),
			'orderby'             => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
				'ID'         => 'ASC',
			),
		)
	);

	$featured = array_values(
		array_filter(
			$featured_query->posts,
			static function ( $post ) {
				return $post instanceof WP_Post;
			}
		)
	);
	usort( $featured, 'amarilla_compare_home_featured_vehicles' );

	$selected_ids = array_map(
		static function ( WP_Post $post ): int {
			return (int) $post->ID;
		},
		array_slice( $featured, 0, $limit )
	);

	$remaining = $limit - count( $selected_ids );
	if ( $remaining <= 0 ) {
		return $selected_ids;
	}

	$fallback_query = new WP_Query(
		array(
			'post_type'           => 'vehicle',
			'post_status'         => 'publish',
			'posts_per_page'      => $remaining,
			'post__not_in'        => $selected_ids,
			'fields'              => 'ids',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'orderby'             => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
				'ID'         => 'ASC',
			),
		)
	);

	return array_merge( $selected_ids, array_map( 'intval', $fallback_query->posts ) );
}

/**
 * Vykreslí celou homepage sekci doporučených vozidel.
 *
 * @return string
 */
function amarilla_render_featured_vehicles_block(): string {
	$eyebrow     = amarilla_get_theme_mod( 'amarilla_fleet_eyebrow' );
	$title       = amarilla_get_theme_mod( 'amarilla_fleet_title' );
	$lead        = amarilla_get_theme_mod( 'amarilla_fleet_lead' );
	$empty_text  = amarilla_get_theme_mod( 'amarilla_fleet_empty_text' );
	$button_text = amarilla_get_theme_mod( 'amarilla_fleet_button_text' );
	$fleet_url   = amarilla_get_fleet_url();
	$vehicle_ids = amarilla_get_home_featured_vehicle_ids( 3 );

	ob_start();
	?>
	<section class="wp-block-group alignfull amarilla-fleet" id="fleet">
		<div class="amarilla-container">
			<div class="amarilla-section-head">
				<div>
					<div class="amarilla-eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
					<h2><?php echo wp_kses_post( $title ); ?></h2>
				</div>
				<p><?php echo esc_html( $lead ); ?></p>
			</div>

			<div class="amarilla-fleet-grid">
				<?php if ( $vehicle_ids ) : ?>
					<?php foreach ( $vehicle_ids as $vehicle_id ) : ?>
						<?php echo amarilla_render_vehicle_card( $vehicle_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Renderer vrací escapované theme HTML. ?>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="amarilla-fleet-empty">
						<p><?php echo esc_html( $empty_text ); ?></p>
					</div>
				<?php endif; ?>
			</div>

			<div class="amarilla-fleet-cta">
				<a href="<?php echo esc_url( $fleet_url ); ?>" class="amarilla-btn amarilla-btn--ghost">
					<?php echo esc_html( $button_text ); ?>
					<svg class="arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
				</a>
			</div>
		</div>
	</section>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Registruje dynamický blok a jeho malý editorový náhled.
 */
function amarilla_register_featured_vehicles_block(): void {
	wp_register_script(
		'amarilla-featured-vehicles-editor',
		AMARILLA_URI . '/assets/js/featured-vehicles-block.js',
		array( 'wp-blocks', 'wp-block-editor', 'wp-element', 'wp-i18n', 'wp-server-side-render' ),
		AMARILLA_VERSION,
		true
	);

	register_block_type(
		'amarilla/featured-vehicles',
		array(
			'api_version'     => 2,
			'title'           => __( 'Doporučená vozidla', 'amarilla' ),
			'description'     => __( 'Dynamická sekce tří doporučených vozidel pro úvodní stránku.', 'amarilla' ),
			'category'        => 'theme',
			'icon'            => 'car',
			'editor_script'   => 'amarilla-featured-vehicles-editor',
			'render_callback' => 'amarilla_render_featured_vehicles_block',
			'supports'        => array(
				'html'  => false,
				'align' => false,
			),
		)
	);
}
add_action( 'init', 'amarilla_register_featured_vehicles_block' );

/**
 * Přidá klasický metabox pro výběr vozidla na úvodní stránku.
 */
function amarilla_add_home_featured_meta_box(): void {
	add_meta_box(
		'amarilla-home-featured',
		__( 'Úvodní stránka', 'amarilla' ),
		'amarilla_render_home_featured_meta_box',
		'vehicle',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes_vehicle', 'amarilla_add_home_featured_meta_box' );

/**
 * Vykreslí obsah metaboxu doporučeného vozidla.
 *
 * @param WP_Post $post Upravené vozidlo.
 */
function amarilla_render_home_featured_meta_box( WP_Post $post ): void {
	$featured = get_post_meta( $post->ID, AMARILLA_HOME_FEATURED_META, true );
	$order    = get_post_meta( $post->ID, AMARILLA_HOME_FEATURED_ORDER_META, true );

	wp_nonce_field( 'amarilla_save_home_featured', 'amarilla_home_featured_nonce' );
	?>
	<p>
		<label>
			<input type="checkbox" name="amarilla_home_featured" value="1" <?php checked( (bool) $featured ); ?>>
			<?php esc_html_e( 'Zobrazit mezi doporučenými vozidly', 'amarilla' ); ?>
		</label>
	</p>
	<p>
		<label for="amarilla-home-featured-order"><?php esc_html_e( 'Pořadí:', 'amarilla' ); ?></label><br>
		<input class="small-text" type="number" step="1" id="amarilla-home-featured-order" name="amarilla_home_featured_order" value="<?php echo esc_attr( $order ); ?>" placeholder="10">
	</p>
	<p class="description">
		<?php esc_html_e( 'Na úvodní stránce se zobrazí maximálně 3 doporučená vozidla.', 'amarilla' ); ?><br>
		<?php esc_html_e( 'Nižší číslo = vyšší pozice.', 'amarilla' ); ?>
	</p>
	<?php
}

/**
 * Uloží hodnoty z metaboxu doporučeného vozidla.
 *
 * @param int     $post_id ID vozidla.
 * @param WP_Post $post    Ukládané vozidlo.
 */
function amarilla_save_home_featured_meta( int $post_id, WP_Post $post ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || 'vehicle' !== $post->post_type ) {
		return;
	}

	if (
		! isset( $_POST['amarilla_home_featured_nonce'] )
		|| is_array( $_POST['amarilla_home_featured_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['amarilla_home_featured_nonce'] ) ), 'amarilla_save_home_featured' )
	) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if (
		isset( $_POST['amarilla_home_featured'] )
		&& ! is_array( $_POST['amarilla_home_featured'] )
		&& '1' === sanitize_text_field( wp_unslash( $_POST['amarilla_home_featured'] ) )
	) {
		update_post_meta( $post_id, AMARILLA_HOME_FEATURED_META, 1 );
	} else {
		delete_post_meta( $post_id, AMARILLA_HOME_FEATURED_META );
	}

	$order = '';
	if ( isset( $_POST['amarilla_home_featured_order'] ) && ! is_array( $_POST['amarilla_home_featured_order'] ) ) {
		$order = trim( sanitize_text_field( wp_unslash( $_POST['amarilla_home_featured_order'] ) ) );
	}

	if ( '' === $order ) {
		delete_post_meta( $post_id, AMARILLA_HOME_FEATURED_ORDER_META );
	} else {
		update_post_meta( $post_id, AMARILLA_HOME_FEATURED_ORDER_META, amarilla_sanitize_home_featured_order( $order ) );
	}
}
add_action( 'save_post_vehicle', 'amarilla_save_home_featured_meta', 10, 2 );
