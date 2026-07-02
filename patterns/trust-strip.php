<?php
/**
 * Title: Pruh důvěry (4 ikony)
 * Slug: amarilla/trust-strip
 * Categories: amarilla
 * Description: Tmavý pruh se 4 ikonami. Obsah lze upravit v Customizeru → Amarilla → Pruh důvěry.
 */

if ( ! amarilla_get_theme_mod( 'amarilla_trust_enabled' ) ) {
	return;
}

$items = array();
for ( $i = 1; $i <= 4; $i++ ) {
	$items[] = array(
		'icon'     => amarilla_get_theme_mod( "amarilla_trust_{$i}_icon" ),
		'title'    => amarilla_get_theme_mod( "amarilla_trust_{$i}_title" ),
		'subtitle' => amarilla_get_theme_mod( "amarilla_trust_{$i}_subtitle" ),
	);
}
?>
<!-- wp:group {"align":"full","tagName":"section","className":"amarilla-trust","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull amarilla-trust">
<!-- wp:html -->
<div class="amarilla-container">
	<div class="amarilla-trust-grid">
		<?php foreach ( $items as $item ) :
			if ( ! $item['title'] && ! $item['subtitle'] ) continue; ?>
			<div class="amarilla-trust-item">
				<div class="amarilla-trust-icon">
					<?php echo amarilla_get_icon( $item['icon'], 18 ); ?>
				</div>
				<div>
					<?php if ( $item['title'] ) : ?>
						<strong><?php echo esc_html( $item['title'] ); ?></strong>
					<?php endif; ?>
					<?php if ( $item['subtitle'] ) : ?>
						<small><?php echo esc_html( $item['subtitle'] ); ?></small>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
<!-- /wp:html -->
</section>
<!-- /wp:group -->
