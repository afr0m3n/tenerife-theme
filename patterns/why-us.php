<?php
/**
 * Title: Proč si vybrat nás
 * Slug: amarilla/why-us
 * Categories: amarilla
 * Description: Sekce se 4 důvody, proč si vybrat firmu. Obsah v Customizeru → Amarilla → Proč si vybrat nás.
 */

if ( ! amarilla_get_theme_mod( 'amarilla_why_enabled' ) ) {
	return;
}

$eyebrow      = amarilla_get_theme_mod( 'amarilla_why_eyebrow' );
$title        = amarilla_get_theme_mod( 'amarilla_why_title' );
$title_accent = amarilla_get_theme_mod( 'amarilla_why_title_accent' );
$lead         = amarilla_get_theme_mod( 'amarilla_why_lead' );
$about_url    = get_page_by_path( 'o-nas' ) ? get_permalink( get_page_by_path( 'o-nas' ) ) : home_url( '/o-nas/' );

$features = array();
for ( $i = 1; $i <= 4; $i++ ) {
	$features[] = array(
		'num'      => str_pad( $i, 2, '0', STR_PAD_LEFT ),
		'category' => amarilla_get_theme_mod( "amarilla_why_{$i}_category" ),
		'title'    => amarilla_get_theme_mod( "amarilla_why_{$i}_title" ),
		'desc'     => amarilla_get_theme_mod( "amarilla_why_{$i}_desc" ),
	);
}
?>
<!-- wp:group {"align":"full","tagName":"section","className":"amarilla-why","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull amarilla-why">
<!-- wp:html -->
<div class="amarilla-container">
	<div class="amarilla-why-grid">
		<div>
			<?php if ( $eyebrow ) : ?>
				<div class="amarilla-eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
			<?php endif; ?>
			<h2>
				<?php echo wp_kses_post( $title ); ?>
				<?php if ( $title_accent ) : ?>
					<br><em><?php echo esc_html( $title_accent ); ?></em>
				<?php endif; ?>
			</h2>
			<?php if ( $lead ) : ?>
				<p class="amarilla-why-lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>
			<a href="<?php echo esc_url( $about_url ); ?>" class="amarilla-btn amarilla-btn--ghost">
				<?php esc_html_e( 'O nás', 'amarilla' ); ?>
				<svg class="arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
			</a>
		</div>
		<div class="amarilla-why-features">
			<?php foreach ( $features as $f ) :
				if ( ! $f['title'] && ! $f['desc'] ) continue; ?>
				<div class="amarilla-feature">
					<span class="amarilla-feature-num"><?php echo esc_html( $f['num'] ); ?><?php if ( $f['category'] ) echo ' / ' . esc_html( $f['category'] ); ?></span>
					<?php if ( $f['title'] ) : ?>
						<h4><?php echo esc_html( $f['title'] ); ?></h4>
					<?php endif; ?>
					<?php if ( $f['desc'] ) : ?>
						<p><?php echo esc_html( $f['desc'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<!-- /wp:html -->
</section>
<!-- /wp:group -->
