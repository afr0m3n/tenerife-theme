<?php
/**
 * Admin action for duplicating vehicles.
 *
 * @package Amarilla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the "Duplikovat" row action to vehicle posts.
 *
 * @param array<string, string> $actions Row actions.
 * @param WP_Post              $post    Current post.
 * @return array<string, string>
 */
function amarilla_vehicle_duplicate_row_action( $actions, $post ) {
	if ( ! $post instanceof WP_Post || $post->post_type !== 'vehicle' || $post->post_status === 'trash' ) {
		return $actions;
	}

	$post_type_object = get_post_type_object( 'vehicle' );
	if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->edit_posts ) || ! current_user_can( 'edit_post', $post->ID ) ) {
		return $actions;
	}

	$url = wp_nonce_url(
		add_query_arg(
			array(
				'action' => 'amarilla_duplicate_vehicle',
				'post'   => $post->ID,
			),
			admin_url( 'admin-post.php' )
		),
		'amarilla_duplicate_vehicle_' . $post->ID
	);

	$actions['amarilla_duplicate_vehicle'] = sprintf(
		'<a href="%s" aria-label="%s">%s</a>',
		esc_url( $url ),
		esc_attr( sprintf( __( 'Duplikovat vozidlo %s', 'amarilla' ), get_the_title( $post ) ) ),
		esc_html__( 'Duplikovat', 'amarilla' )
	);

	return $actions;
}
add_filter( 'post_row_actions', 'amarilla_vehicle_duplicate_row_action', 10, 2 );

/**
 * Handles vehicle duplication from admin-post.php.
 */
function amarilla_handle_duplicate_vehicle() {
	$source_id = 0;
	if ( isset( $_GET['post'] ) && ! is_array( $_GET['post'] ) ) {
		$source_id = absint( wp_unslash( $_GET['post'] ) );
	}

	if ( ! $source_id ) {
		wp_die(
			esc_html__( 'Chybí zdrojové vozidlo pro duplikaci.', 'amarilla' ),
			'',
			array( 'response' => 400 )
		);
	}

	check_admin_referer( 'amarilla_duplicate_vehicle_' . $source_id );

	$source_post = get_post( $source_id );
	if ( ! $source_post || $source_post->post_type !== 'vehicle' ) {
		wp_die(
			esc_html__( 'Zdrojové vozidlo neexistuje nebo má nesprávný typ obsahu.', 'amarilla' ),
			'',
			array( 'response' => 404 )
		);
	}

	if ( ! current_user_can( 'edit_post', $source_id ) ) {
		wp_die(
			esc_html__( 'Nemáte oprávnění duplikovat toto vozidlo.', 'amarilla' ),
			'',
			array( 'response' => 403 )
		);
	}

	$post_type_object = get_post_type_object( 'vehicle' );
	if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->edit_posts ) ) {
		wp_die(
			esc_html__( 'Nemáte oprávnění vytvářet vozidla.', 'amarilla' ),
			'',
			array( 'response' => 403 )
		);
	}

	$new_post_id = wp_insert_post(
		wp_slash(
			array(
				'post_type'             => 'vehicle',
				'post_status'           => 'draft',
				'post_title'            => sprintf( __( '%s - kopie', 'amarilla' ), $source_post->post_title ),
				'post_content'          => $source_post->post_content,
				'post_excerpt'          => $source_post->post_excerpt,
				'post_content_filtered' => $source_post->post_content_filtered,
				'post_parent'           => $source_post->post_parent,
				'menu_order'            => $source_post->menu_order,
				'comment_status'        => $source_post->comment_status,
				'ping_status'           => $source_post->ping_status,
				'post_password'         => $source_post->post_password,
			)
		),
		true
	);

	if ( is_wp_error( $new_post_id ) ) {
		wp_die(
			esc_html( $new_post_id->get_error_message() ),
			'',
			array( 'response' => 500 )
		);
	}

	amarilla_copy_vehicle_taxonomies( $source_id, $new_post_id );
	amarilla_copy_vehicle_meta( $source_id, $new_post_id );

	$redirect_url = add_query_arg(
		'amarilla_vehicle_duplicated',
		'1',
		admin_url( 'post.php?post=' . $new_post_id . '&action=edit' )
	);

	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'admin_post_amarilla_duplicate_vehicle', 'amarilla_handle_duplicate_vehicle' );

/**
 * Copies all taxonomies assigned to the source vehicle.
 *
 * @param int $source_id Source post ID.
 * @param int $new_id    New post ID.
 */
function amarilla_copy_vehicle_taxonomies( $source_id, $new_id ) {
	$taxonomies = get_object_taxonomies( 'vehicle' );
	foreach ( $taxonomies as $taxonomy ) {
		$term_ids = wp_get_object_terms(
			$source_id,
			$taxonomy,
			array(
				'fields' => 'ids',
			)
		);

		if ( is_wp_error( $term_ids ) || empty( $term_ids ) ) {
			continue;
		}

		wp_set_object_terms( $new_id, array_map( 'intval', $term_ids ), $taxonomy, false );
	}
}

/**
 * Copies vehicle meta except volatile system metadata.
 *
 * @param int $source_id Source post ID.
 * @param int $new_id    New post ID.
 */
function amarilla_copy_vehicle_meta( $source_id, $new_id ) {
	$excluded_meta = array(
		'_edit_lock',
		'_edit_last',
		'_wp_old_slug',
		'_wp_trash_meta_status',
		'_wp_trash_meta_time',
		'_wp_desired_post_slug',
		'_amarilla_home_featured',
		'_amarilla_home_featured_order',
	);

	$all_meta = get_post_meta( $source_id );
	foreach ( $all_meta as $meta_key => $meta_values ) {
		if ( in_array( $meta_key, $excluded_meta, true ) ) {
			continue;
		}

		foreach ( $meta_values as $meta_value ) {
			add_post_meta( $new_id, $meta_key, wp_slash( maybe_unserialize( $meta_value ) ) );
		}
	}
}

/**
 * Shows a short confirmation after opening the duplicate draft.
 */
function amarilla_vehicle_duplicate_admin_notice() {
	if ( empty( $_GET['amarilla_vehicle_duplicated'] ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'vehicle' ) {
		return;
	}

	printf(
		'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
		esc_html__( 'Kopie vozidla byla vytvořena jako koncept.', 'amarilla' )
	);
}
add_action( 'admin_notices', 'amarilla_vehicle_duplicate_admin_notice' );
