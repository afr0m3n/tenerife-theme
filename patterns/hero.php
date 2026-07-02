<?php
/**
 * Title: Hero — úvodní sekce
 * Slug: amarilla/hero
 * Categories: amarilla, featured
 * Description: Úvodní hero sekce. Veškerý obsah lze upravit v Customizeru (Vzhled → Přizpůsobit → Amarilla → Hero).
 */

$eyebrow      = amarilla_get_theme_mod( 'amarilla_hero_eyebrow' );
$title        = amarilla_get_theme_mod( 'amarilla_hero_title' );
$title_accent = amarilla_get_theme_mod( 'amarilla_hero_title_accent' );
$lead         = amarilla_get_theme_mod( 'amarilla_hero_lead' );
$btn1_text    = amarilla_get_theme_mod( 'amarilla_hero_btn1_text' );
$btn1_url     = amarilla_get_theme_mod( 'amarilla_hero_btn1_url' );
$btn2_text    = amarilla_get_theme_mod( 'amarilla_hero_btn2_text' );
$btn2_url     = amarilla_get_theme_mod( 'amarilla_hero_btn2_url' );
$hero_image   = amarilla_get_theme_mod( 'amarilla_hero_image' );
$badge        = amarilla_get_theme_mod( 'amarilla_hero_badge' );
$stat_value   = amarilla_get_theme_mod( 'amarilla_hero_stat_value' );
$stat_label   = amarilla_get_theme_mod( 'amarilla_hero_stat_label' );
$show_booking = amarilla_get_theme_mod( 'amarilla_hero_show_booking' );
?>
<!-- wp:group {"align":"full","tagName":"section","className":"amarilla-hero","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull amarilla-hero">
<!-- wp:html -->
<div class="amarilla-container">
	<div class="amarilla-hero-grid">
		<div class="amarilla-hero-content">
			<?php if ( $eyebrow ) : ?>
				<div class="amarilla-eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
			<?php endif; ?>
			<h1 class="amarilla-hero-title">
				<?php echo wp_kses_post( $title ); ?>
				<?php if ( $title_accent ) : ?>
					<br><em class="amarilla-hero-accent"><?php echo esc_html( $title_accent ); ?></em>
				<?php endif; ?>
			</h1>
			<?php if ( $lead ) : ?>
				<p class="amarilla-hero-lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>

			<?php if ( $show_booking ) : ?>
				<?php echo do_shortcode( '[amarilla_booking_widget]' ); ?>
			<?php else : ?>
				<div class="amarilla-hero-actions">
					<?php if ( $btn1_text && $btn1_url ) : ?>
						<a href="<?php echo esc_url( $btn1_url ); ?>" class="amarilla-btn amarilla-btn--primary amarilla-hero-cta-primary">
							<span class="amarilla-hero-cta-primary-text"><?php echo esc_html( $btn1_text ); ?></span>
							<svg class="arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
						</a>
					<?php endif; ?>
					<?php if ( $btn2_text && $btn2_url ) : ?>
						<a href="<?php echo esc_url( $btn2_url ); ?>" class="amarilla-btn amarilla-btn--ghost amarilla-hero-cta-secondary">
							<span class="amarilla-hero-cta-secondary-text"><?php echo esc_html( $btn2_text ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="amarilla-hero-visual">
			<?php if ( $hero_image ) : ?>
				<img src="<?php echo esc_url( $hero_image ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="eager">
			<?php endif; ?>
			<?php if ( $badge ) : ?>
				<div class="amarilla-hero-badge"><?php echo esc_html( $badge ); ?></div>
			<?php endif; ?>
			<?php if ( $stat_value || $stat_label ) : ?>
				<div class="amarilla-hero-stat">
					<div>
						<?php if ( $stat_value ) : ?>
							<div class="amarilla-hero-stat-num"><?php echo esc_html( $stat_value ); ?></div>
						<?php endif; ?>
						<?php if ( $stat_label ) : ?>
							<div class="amarilla-hero-stat-label"><?php echo esc_html( $stat_label ); ?></div>
						<?php endif; ?>
					</div>
					<?php if ( $btn1_url ) : ?>
						<a href="<?php echo esc_url( $btn1_url ); ?>" class="amarilla-hero-stat-link"><?php esc_html_e( 'Vybrat', 'amarilla' ); ?> →</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<!-- /wp:html -->
</section>
<!-- /wp:group -->
