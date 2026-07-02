<?php
/**
 * Title: Vozový park (3 vozy)
 * Slug: amarilla/fleet-grid
 * Categories: amarilla
 * Description: Sekce s nejnovějšími 3 vozy, načítané dynamicky z CPT vehicle.
 */

$eyebrow = __( 'Vozový park', 'amarilla' );
$title   = __( 'Auto pro každý výlet po ostrově.', 'amarilla' );
$lead    = __( 'Od úsporných hatchbacků do města po prostorná SUV pro výpravu k Teide. Všechna auta jsou pravidelně servisovaná a klimatizovaná.', 'amarilla' );
$fleet_url = amarilla_get_fleet_url();
?>
<!-- wp:group {"align":"full","tagName":"section","className":"amarilla-fleet","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull amarilla-fleet" id="fleet">
<!-- wp:html -->
<div class="amarilla-container">
	<div class="amarilla-section-head">
		<div>
			<div class="amarilla-eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
			<h2><?php echo wp_kses_post( $title ); ?></h2>
		</div>
		<p><?php echo esc_html( $lead ); ?></p>
	</div>

	<div class="amarilla-fleet-grid">
		<?php
		$vehicles = new WP_Query( array(
			'post_type'      => 'vehicle',
			'posts_per_page' => 3,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		) );

		if ( $vehicles->have_posts() ) :
			while ( $vehicles->have_posts() ) :
				$vehicles->the_post();
				$post_id = get_the_ID();
				$tagline = get_post_meta( $post_id, '_vehicle_tagline', true );
				$label   = get_post_meta( $post_id, '_vehicle_label', true );
				$seats   = (int) get_post_meta( $post_id, '_vehicle_seats', true );
				$doors   = (int) get_post_meta( $post_id, '_vehicle_doors', true );
				$trans   = get_post_meta( $post_id, '_vehicle_transmission', true );
				$price   = get_post_meta( $post_id, '_vehicle_price', true );
				$category_terms = get_the_terms( $post_id, 'vehicle_category' );
				$category = $category_terms && ! is_wp_error( $category_terms ) ? $category_terms[0]->name : '';
				$tag_class = $label ? 'popular' : '';
				$display_label = $label ? $label : $category;
				?>
				<a href="<?php the_permalink(); ?>" class="vehicle-card-link">
					<div class="vehicle-card">
						<div class="vehicle-card-image">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'amarilla-vehicle-card' ); ?>
							<?php endif; ?>
							<?php if ( $display_label ) : ?>
								<div class="vehicle-card-tag <?php echo esc_attr( $tag_class ); ?>"><?php echo esc_html( $display_label ); ?></div>
							<?php endif; ?>
						</div>
						<div class="vehicle-card-info">
							<h3><?php the_title(); ?></h3>
							<?php if ( $tagline ) : ?>
								<div class="vehicle-card-tagline"><?php echo esc_html( $tagline ); ?></div>
							<?php endif; ?>
							<div class="vehicle-specs">
								<?php if ( $seats ) : ?>
									<span class="vehicle-spec">
										<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.38 8.38 0 0113 0"/></svg>
										<?php printf( _n( '%d osoba', '%d osob', $seats, 'amarilla' ), $seats ); ?>
									</span>
								<?php endif; ?>
								<?php if ( $trans ) : ?>
									<span class="vehicle-spec">
										<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
										<?php echo $trans === 'manual' ? esc_html__( 'Manuál', 'amarilla' ) : esc_html__( 'Automat', 'amarilla' ); ?>
									</span>
								<?php endif; ?>
								<?php if ( $doors ) : ?>
									<span class="vehicle-spec">
										<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M7 8h10M7 16h10"/></svg>
										<?php printf( _n( '%d dveře', '%d dveří', $doors, 'amarilla' ), $doors ); ?>
									</span>
								<?php endif; ?>
							</div>
							<div class="vehicle-card-footer">
								<div>
									<?php if ( $price ) : ?>
										<span class="vehicle-price-amount"><?php echo esc_html( $price ); ?></span>
										<span class="vehicle-price-label"><?php esc_html_e( '/ den · vč. pojištění', 'amarilla' ); ?></span>
									<?php else : ?>
										<span class="vehicle-price-inquire"><?php esc_html_e( 'Poptat termín', 'amarilla' ); ?></span>
									<?php endif; ?>
								</div>
								<div class="vehicle-card-arrow">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
								</div>
							</div>
						</div>
					</div>
				</a>
			<?php
			endwhile;
			wp_reset_postdata();
		else :
			?>
			<div class="amarilla-fleet-empty">
				<p><?php esc_html_e( 'Vozový park bude brzy doplněn. Pro zobrazení sekce přidejte v adminu několik vozidel.', 'amarilla' ); ?></p>
			</div>
			<?php
		endif;
		?>
	</div>

	<div class="amarilla-fleet-cta">
		<a href="<?php echo esc_url( $fleet_url ); ?>" class="amarilla-btn amarilla-btn--ghost">
			<?php esc_html_e( 'Zobrazit celý vozový park', 'amarilla' ); ?>
			<svg class="arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
		</a>
	</div>
</div>
<!-- /wp:html -->
</section>
<!-- /wp:group -->
