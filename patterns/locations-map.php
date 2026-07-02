<?php
/**
 * Title: Pobočky / místa vyzvednutí
 * Slug: amarilla/locations-map
 * Categories: amarilla
 * Description: Sekce s mapou poboček (OpenStreetMap embed). Konfigurovatelná v Customizeru.
 */

$enabled = amarilla_get_theme_mod( 'amarilla_locations_enabled' );
if ( ! $enabled ) {
	return;
}
$eyebrow = amarilla_get_theme_mod( 'amarilla_locations_eyebrow' );
$title   = amarilla_get_theme_mod( 'amarilla_locations_title' );
$lead    = amarilla_get_theme_mod( 'amarilla_locations_lead' );
?>
<!-- wp:group {"align":"full","tagName":"section","className":"amarilla-locations","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull amarilla-locations">
<!-- wp:html -->
<div class="amarilla-container">
	<div class="amarilla-section-head">
		<div>
			<?php if ( $eyebrow ) : ?>
				<div class="amarilla-eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
			<?php endif; ?>
			<h2><?php echo esc_html( $title ); ?></h2>
		</div>
		<?php if ( $lead ) : ?>
			<p><?php echo esc_html( $lead ); ?></p>
		<?php endif; ?>
	</div>

	<?php echo do_shortcode( '[amarilla_locations_map]' ); ?>
</div>
<!-- /wp:html -->
</section>
<!-- /wp:group -->
