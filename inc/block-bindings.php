<?php
/**
 * Shortcodes pro vykreslování dat vozidel v block templatech.
 * Block templates nemohou obsahovat PHP, používáme tedy shortcodes.
 *
 * @package Amarilla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * [amarilla_vehicle_image] — hlavní obrázek vozidla
 */
function amarilla_sc_vehicle_image() {
	$post_id = get_the_ID();
	if ( ! $post_id || get_post_type( $post_id ) !== 'vehicle' ) {
		return '';
	}
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail(
			$post_id,
			'large',
			array(
				'loading'  => 'lazy',
				'decoding' => 'async',
				'sizes'    => '(max-width: 1024px) calc(100vw - 40px), min(55vw, 760px)',
			)
		);
	}
	return '<div class="vehicle-detail-media-placeholder" aria-hidden="true"></div>';
}
add_shortcode( 'amarilla_vehicle_image', 'amarilla_sc_vehicle_image' );

/**
 * [amarilla_vehicle_title] — název vozidla
 */
function amarilla_sc_vehicle_title() {
	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return '';
	}
	return esc_html( get_the_title( $post_id ) );
}
add_shortcode( 'amarilla_vehicle_title', 'amarilla_sc_vehicle_title' );

/**
 * [amarilla_vehicle_tagline] — krátký popis pod název
 */
function amarilla_sc_vehicle_tagline() {
	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return '';
	}
	$tagline = get_post_meta( $post_id, '_vehicle_tagline', true );
	return esc_html( $tagline );
}
add_shortcode( 'amarilla_vehicle_tagline', 'amarilla_sc_vehicle_tagline' );

/**
 * [amarilla_vehicle_category] — kategorie vozidla
 */
function amarilla_sc_vehicle_category() {
	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return '';
	}
	$terms = get_the_terms( $post_id, 'vehicle_category' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		return esc_html( $terms[0]->name );
	}
	return esc_html__( 'Vozidlo', 'amarilla' );
}
add_shortcode( 'amarilla_vehicle_category', 'amarilla_sc_vehicle_category' );

/**
 * [amarilla_vehicle_specs_grid] — mřížka se specifikacemi vozidla pro detail
 */
function amarilla_sc_vehicle_specs_grid() {
	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return '';
	}

	$seats   = (int) get_post_meta( $post_id, '_vehicle_seats', true );
	$doors   = (int) get_post_meta( $post_id, '_vehicle_doors', true );
	$trans   = get_post_meta( $post_id, '_vehicle_transmission', true );
	$fuel    = get_post_meta( $post_id, '_vehicle_fuel', true );
	$luggage = get_post_meta( $post_id, '_vehicle_luggage', true );
	$ac      = get_post_meta( $post_id, '_vehicle_ac', true );

	$trans_labels = array(
		'manual'    => __( 'Manuál', 'amarilla' ),
		'automatic' => __( 'Automat', 'amarilla' ),
	);
	$fuel_labels = array(
		'petrol'   => __( 'Benzín', 'amarilla' ),
		'diesel'   => __( 'Nafta', 'amarilla' ),
		'hybrid'   => __( 'Hybrid', 'amarilla' ),
		'electric' => __( 'Elektro', 'amarilla' ),
	);

	$rows = array();
	if ( $seats )   { $rows[] = array( __( 'Počet míst', 'amarilla' ), $seats ); }
	if ( $doors )   { $rows[] = array( __( 'Počet dveří', 'amarilla' ), $doors ); }
	if ( $trans && isset( $trans_labels[ $trans ] ) ) {
		$rows[] = array( __( 'Převodovka', 'amarilla' ), $trans_labels[ $trans ] );
	}
	if ( $fuel && isset( $fuel_labels[ $fuel ] ) ) {
		$rows[] = array( __( 'Palivo', 'amarilla' ), $fuel_labels[ $fuel ] );
	}
	if ( $luggage ) { $rows[] = array( __( 'Kufr', 'amarilla' ), $luggage ); }
	$rows[] = array( __( 'Klimatizace', 'amarilla' ), $ac === '1' || $ac === 1 ? __( 'Ano', 'amarilla' ) : __( 'Ne', 'amarilla' ) );

	if ( empty( $rows ) ) {
		return '';
	}

	$html = '<div class="vehicle-detail-specs">';
	foreach ( $rows as $row ) {
		$html .= sprintf(
			'<div class="vehicle-detail-spec"><span class="label">%s</span><span class="value">%s</span></div>',
			esc_html( $row[0] ),
			esc_html( $row[1] )
		);
	}
	$html .= '</div>';

	return $html;
}
add_shortcode( 'amarilla_vehicle_specs_grid', 'amarilla_sc_vehicle_specs_grid' );

/**
 * [amarilla_vehicle_content] — obsah příspěvku (popis vozidla)
 */
function amarilla_sc_vehicle_content() {
	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return '';
	}

	$parts = amarilla_get_vehicle_content_parts( $post_id );
	if ( $parts['content'] === '' ) {
		return '';
	}

	return apply_filters( 'the_content', $parts['content'] );
}
add_shortcode( 'amarilla_vehicle_content', 'amarilla_sc_vehicle_content' );

/**
 * Zjistí, zda má Image blok vlastní odkaz, který musí zůstat zachovaný.
 *
 * @param array<string,mixed> $block Image blok.
 * @return bool
 */
function amarilla_vehicle_image_has_custom_link( $block ) {
	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();

	return ! empty( $attrs['href'] )
		|| ( array_key_exists( 'linkDestination', $attrs ) && 'none' !== $attrs['linkDestination'] )
		|| ( ! empty( $block['innerHTML'] ) && preg_match( '/<a\b[^>]*\bhref\s*=/i', $block['innerHTML'] ) );
}

/**
 * Rozdělí pouze top-level obsah vozidla na běžný obsah a core galerie.
 *
 * @param int $post_id ID vozidla.
 * @return array{content:string,gallery:string}
 */
function amarilla_get_vehicle_content_parts( $post_id ) {
	static $cache = array();

	$post_id = absint( $post_id );
	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}

	$post = $post_id ? get_post( $post_id ) : null;
	if ( ! $post || 'vehicle' !== $post->post_type ) {
		return array( 'content' => '', 'gallery' => '' );
	}

	$content_blocks    = array();
	$gallery_blocks    = array();
	$standalone_images = array();
	$featured_id       = get_post_thumbnail_id( $post_id );
	$seen_ids          = array();

	$prepare_image = static function ( $image ) use ( $featured_id, &$seen_ids ) {
		$image_id = ! empty( $image['attrs']['id'] ) ? absint( $image['attrs']['id'] ) : 0;
		if ( $image_id && ( $image_id === $featured_id || isset( $seen_ids[ $image_id ] ) ) ) {
			return null;
		}
		if ( $image_id ) {
			$seen_ids[ $image_id ] = true;
		}

		if ( ! amarilla_vehicle_image_has_custom_link( $image ) ) {
			$image['attrs'] = isset( $image['attrs'] ) && is_array( $image['attrs'] ) ? $image['attrs'] : array();
			$image['attrs']['lightbox'] = isset( $image['attrs']['lightbox'] ) && is_array( $image['attrs']['lightbox'] ) ? $image['attrs']['lightbox'] : array();
			$image['attrs']['lightbox']['enabled'] = true;
			$image['attrs']['linkDestination']      = 'none';
		}

		return $image;
	};

	foreach ( parse_blocks( $post->post_content ) as $block ) {
		if ( 'core/image' === $block['blockName'] ) {
			if ( amarilla_vehicle_image_has_custom_link( $block ) ) {
				$content_blocks[] = $block;
				continue;
			}

			$image = $prepare_image( $block );
			if ( $image ) {
				$standalone_images[] = $image;
			}
			continue;
		}

		if ( 'core/gallery' !== $block['blockName'] ) {
			$content_blocks[] = $block;
			continue;
		}

		$children    = array();
		$child_map   = array();
		$image_count = 0;
		foreach ( $block['innerBlocks'] as $index => $child ) {
			$prepared = 'core/image' === $child['blockName'] ? $prepare_image( $child ) : $child;
			$child_map[ $index ] = null !== $prepared;
			if ( null !== $prepared ) {
				$children[] = $prepared;
				$image_count += 'core/image' === $child['blockName'] ? 1 : 0;
			}
		}

		if ( $image_count || $children ) {
			$inner_content = array();
			$child_index   = 0;
			foreach ( $block['innerContent'] as $chunk ) {
				if ( null !== $chunk || ! empty( $child_map[ $child_index ] ) ) {
					$inner_content[] = $chunk;
				}
				$child_index += null === $chunk ? 1 : 0;
			}
			$block['innerBlocks']  = $children;
			$block['innerContent'] = $inner_content;
			if ( $image_count ) {
				$gallery_blocks[] = $block;
			} else {
				$content_blocks[] = $block;
			}
		}
	}

	if ( $standalone_images ) {
		$inner_content = array( '<figure class="wp-block-gallery has-nested-images columns-3 vehicle-detail-thumbnails">' );
		$inner_content = array_merge( $inner_content, array_fill( 0, count( $standalone_images ), null ), array( '</figure>' ) );
		array_unshift(
			$gallery_blocks,
			array(
				'blockName'    => 'core/gallery',
				'attrs'        => array(
					'columns'     => 3,
					'imageCrop'   => false,
					'fixedHeight' => false,
					'aspectRatio' => 'auto',
					'linkTo'      => 'none',
					'className'   => 'vehicle-detail-thumbnails',
				),
				'innerBlocks'  => $standalone_images,
				'innerHTML'    => '',
				'innerContent' => $inner_content,
			)
		);
	}

	$cache[ $post_id ] = array(
		'content' => trim( serialize_blocks( $content_blocks ) ),
		'gallery' => trim( serialize_blocks( $gallery_blocks ) ),
	);

	return $cache[ $post_id ];
}

/**
 * [amarilla_vehicle_gallery] — samostatná galerie dalších fotografií.
 */
function amarilla_sc_vehicle_gallery() {
	$post_id = get_the_ID();
	if ( ! $post_id || 'vehicle' !== get_post_type( $post_id ) ) {
		return '';
	}

	$gallery = amarilla_get_vehicle_content_parts( $post_id )['gallery'];
	if ( '' === $gallery ) {
		return '';
	}

	$heading_id = 'vehicle-gallery-title-' . $post_id;
	return sprintf(
		'<section class="vehicle-detail-more" aria-labelledby="%1$s"><div class="vehicle-detail-gallery-head"><div class="amarilla-eyebrow">%2$s</div><h2 id="%1$s">%3$s</h2></div>%4$s</section>',
		esc_attr( $heading_id ),
		esc_html__( 'Galerie vozidla', 'amarilla' ),
		esc_html__( 'Další fotografie', 'amarilla' ),
		do_blocks( $gallery )
	);
}
add_shortcode( 'amarilla_vehicle_gallery', 'amarilla_sc_vehicle_gallery' );

/**
 * [amarilla_vehicle_cta] — tlačítko pro poptávku s předvyplněným vozem
 */
function amarilla_sc_vehicle_cta() {
	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return '';
	}
	$title = get_the_title( $post_id );
	$contact_url = amarilla_get_contact_url();
	$args = array( 'vehicle' => rawurlencode( $title ) );

	// Předvyplnění třídy vozu z taxonomie
	$cats = get_the_terms( $post_id, 'vehicle_category' );
	if ( $cats && ! is_wp_error( $cats ) ) {
		$args['vehicle_class'] = $cats[0]->slug;
	}

	$url = add_query_arg( $args, $contact_url );

	return sprintf(
		'<a href="%s" class="amarilla-btn amarilla-btn--primary">%s <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px;"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>',
		esc_url( $url ),
		esc_html__( 'Poptat tento vůz', 'amarilla' )
	);
}
add_shortcode( 'amarilla_vehicle_cta', 'amarilla_sc_vehicle_cta' );

/**
 * Vykreslí kartu vozidla pro výpisy.
 */
function amarilla_render_vehicle_card( int $post_id ): string {
	$post_id = absint( $post_id );
	$post    = $post_id ? get_post( $post_id ) : null;

	if ( ! $post || $post->post_type !== 'vehicle' ) {
		return '';
	}

	$tagline = get_post_meta( $post_id, '_vehicle_tagline', true );
	$label   = get_post_meta( $post_id, '_vehicle_label', true );
	$seats   = (int) get_post_meta( $post_id, '_vehicle_seats', true );
	$doors   = (int) get_post_meta( $post_id, '_vehicle_doors', true );
	$trans   = get_post_meta( $post_id, '_vehicle_transmission', true );
	$price   = get_post_meta( $post_id, '_vehicle_price', true );

	// V1.2: pokud existují sezónní období s nižší cenou než základní,
	// zobrazíme "od XX €" — vizuální signál, že existuje varianta.
	$has_seasonal = false;
	if ( function_exists( 'amarilla_get_vehicle_pricing_periods' ) ) {
		$periods = amarilla_get_vehicle_pricing_periods( $post_id );
		$has_seasonal = ! empty( $periods );
		if ( $has_seasonal && function_exists( 'amarilla_get_vehicle_lowest_price' ) ) {
			$low = amarilla_get_vehicle_lowest_price( $post_id );
			if ( $low !== null ) {
				$price = sprintf( __( 'od %s €', 'amarilla' ), number_format_i18n( $low, ( fmod( $low, 1.0 ) === 0.0 ) ? 0 : 2 ) );
			}
		}
	}
	$cats    = get_the_terms( $post_id, 'vehicle_category' );
	$category = $cats && ! is_wp_error( $cats ) ? $cats[0]->name : '';
	$tag_class = $label ? 'popular' : '';
	$display_label = $label ? $label : $category;
	$thumb = has_post_thumbnail( $post_id ) ? get_the_post_thumbnail(
		$post_id,
		'large',
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
			'sizes'    => '(max-width: 640px) calc(100vw - 40px), (max-width: 1024px) calc(50vw - 44px), 424px',
		)
	) : '';

	$html = '<a href="' . esc_url( get_permalink( $post_id ) ) . '" class="vehicle-card-link" style="text-decoration:none;color:inherit;">';
	$html .= '<div class="vehicle-card">';
	$html .= '<div class="vehicle-card-image">';
	$html .= $thumb;
	if ( $display_label ) {
		$html .= '<div class="vehicle-card-tag ' . esc_attr( $tag_class ) . '">' . esc_html( $display_label ) . '</div>';
	}
	$html .= '</div><div class="vehicle-card-info">';
	$html .= '<h3>' . esc_html( get_the_title( $post_id ) ) . '</h3>';
	if ( $tagline ) {
		$html .= '<div class="vehicle-card-tagline">' . esc_html( $tagline ) . '</div>';
	}
	$html .= '<div class="vehicle-specs">';
	if ( $seats ) {
		$html .= '<span class="vehicle-spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.38 8.38 0 0113 0"/></svg>' . sprintf( _n( '%d osoba', '%d osob', $seats, 'amarilla' ), $seats ) . '</span>';
	}
	if ( $trans ) {
		$html .= '<span class="vehicle-spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>' . ( $trans === 'manual' ? esc_html__( 'Manuál', 'amarilla' ) : esc_html__( 'Automat', 'amarilla' ) ) . '</span>';
	}
	if ( $doors ) {
		$html .= '<span class="vehicle-spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M7 8h10M7 16h10"/></svg>' . sprintf( _n( '%d dveře', '%d dveří', $doors, 'amarilla' ), $doors ) . '</span>';
	}
	$html .= '</div><div class="vehicle-card-footer"><div>';
	if ( $price ) {
		$html .= '<span class="vehicle-price-amount">' . esc_html( $price ) . '</span><span class="vehicle-price-label">' . esc_html__( '/ den · vč. pojištění', 'amarilla' ) . '</span>';
	} else {
		$html .= '<span class="vehicle-price-inquire">' . esc_html__( 'Poptat termín', 'amarilla' ) . '</span>';
	}
	$html .= '</div><div class="vehicle-card-arrow"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div></div></div></div></a>';

	return $html;
}

/**
 * [amarilla_vehicle_card_inline] — karta vozidla pro archiv (uvnitř Query Loopu)
 */
function amarilla_sc_vehicle_card_inline() {
	return amarilla_render_vehicle_card( (int) get_the_ID() );
}
add_shortcode( 'amarilla_vehicle_card_inline', 'amarilla_sc_vehicle_card_inline' );

/**
 * Registruje dynamický blok karty vozidla pro Query Loop.
 */
function amarilla_register_vehicle_card_block(): void {
	register_block_type(
		'amarilla/vehicle-card',
		array(
			'api_version'     => 2,
			'uses_context'    => array( 'postId' ),
			'render_callback' => 'amarilla_render_vehicle_card_block',
		)
	);
}
add_action( 'init', 'amarilla_register_vehicle_card_block' );

/**
 * Vykreslí kartu vozidla z aktuální položky Query Loopu.
 */
function amarilla_render_vehicle_card_block( $attributes, $content, $block ): string {
	if ( ! is_object( $block ) || empty( $block->context['postId'] ) ) {
		return '';
	}

	return amarilla_render_vehicle_card( (int) $block->context['postId'] );
}
