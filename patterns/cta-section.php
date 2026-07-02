<?php
/**
 * Title: Závěrečné CTA
 * Slug: amarilla/cta-section
 * Categories: amarilla
 * Description: Závěrečná výzva. Obsah v Customizeru → Amarilla → Závěrečné CTA.
 */

if ( ! get_theme_mod( 'amarilla_cta_enabled', true ) ) {
	return;
}

$title        = get_theme_mod( 'amarilla_cta_title', 'Připraveni vyrazit na' );
$title_accent = get_theme_mod( 'amarilla_cta_title_accent', 'cestu?' );
$lead         = get_theme_mod( 'amarilla_cta_lead', '' );
$btn_text     = get_theme_mod( 'amarilla_cta_btn_text', 'Poptat termín' );
$btn_url      = get_theme_mod( 'amarilla_cta_btn_url', '/kontakt/' );
$phone        = amarilla_get_phone();
$phone_link   = amarilla_get_phone_link();
?>
<!-- wp:group {"align":"full","tagName":"section","className":"amarilla-cta","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull amarilla-cta">
<!-- wp:html -->
<div class="amarilla-container amarilla-cta-inner">
	<?php if ( $title ) : ?>
		<h2 class="amarilla-cta-title">
			<?php echo wp_kses_post( $title ); ?>
			<?php if ( $title_accent ) : ?>
				<br><em><?php echo esc_html( $title_accent ); ?></em>
			<?php endif; ?>
		</h2>
	<?php endif; ?>
	<?php if ( $lead ) : ?>
		<p class="amarilla-cta-lead"><?php echo esc_html( $lead ); ?></p>
	<?php endif; ?>
	<div class="amarilla-cta-actions">
		<?php if ( $btn_text && $btn_url ) : ?>
			<a href="<?php echo esc_url( $btn_url ); ?>" class="amarilla-btn amarilla-btn--primary">
				<?php echo esc_html( $btn_text ); ?>
				<svg class="arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
			</a>
		<?php endif; ?>
		<?php if ( $phone ) : ?>
			<a href="tel:<?php echo esc_attr( $phone_link ); ?>" class="amarilla-btn amarilla-btn--ghost"><?php echo esc_html( $phone ); ?></a>
		<?php endif; ?>
	</div>
</div>
<!-- /wp:html -->
</section>
<!-- /wp:group -->
