<?php
/**
 * Title: Pobočky / místa vyzvednutí
 * Slug: amarilla/locations-map
 * Categories: amarilla
 * Description: Sekce s mapou poboček (OpenStreetMap embed). Konfigurovatelná v Customizeru.
 */

$enabled = get_theme_mod( 'amarilla_locations_enabled', true );
if ( ! $enabled ) {
	return;
}
$eyebrow = get_theme_mod( 'amarilla_locations_eyebrow', 'Kde nás najdete' );
$title   = get_theme_mod( 'amarilla_locations_title', 'Pobočky po celém ostrově.' );
$lead    = get_theme_mod( 'amarilla_locations_lead', 'Vozy si můžete vyzvednout přímo na letišti nebo si je přivezeme zdarma na váš hotel.' );
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
