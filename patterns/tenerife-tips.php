<?php
/**
 * Title: Tipy z Tenerife
 * Slug: amarilla/tenerife-tips
 * Categories: amarilla
 * Description: Tmavá sekce s tipy. Obsah v Customizeru → Amarilla → Tipy z Tenerife.
 */

if ( ! get_theme_mod( 'amarilla_tips_enabled', true ) ) {
	return;
}

$eyebrow = get_theme_mod( 'amarilla_tips_eyebrow', 'Tipy z ostrova' );
$title   = get_theme_mod( 'amarilla_tips_title', 'Tenerife, které stojí za to.' );
$lead    = get_theme_mod( 'amarilla_tips_lead', '' );

$tips = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$tips[] = array(
		'tag'   => get_theme_mod( "amarilla_tip_{$i}_tag", '' ),
		'title' => get_theme_mod( "amarilla_tip_{$i}_title", '' ),
		'image' => get_theme_mod( "amarilla_tip_{$i}_image", '' ),
		'url'   => get_theme_mod( "amarilla_tip_{$i}_url", '' ),
	);
}
?>
<!-- wp:group {"align":"full","tagName":"section","className":"amarilla-tips","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull amarilla-tips">
<!-- wp:html -->
<div class="amarilla-container">
	<div class="amarilla-section-head">
		<div>
			<?php if ( $eyebrow ) : ?>
				<div class="amarilla-eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
			<?php endif; ?>
			<?php if ( $title ) : ?>
				<h2><?php echo wp_kses_post( $title ); ?></h2>
			<?php endif; ?>
		</div>
		<?php if ( $lead ) : ?>
			<p><?php echo esc_html( $lead ); ?></p>
		<?php endif; ?>
	</div>
	<div class="amarilla-tips-grid">
		<?php foreach ( $tips as $tip ) :
			if ( ! $tip['title'] && ! $tip['image'] ) continue;
			$wrapper_tag = $tip['url'] ? 'a' : 'div';
			$wrapper_attr = $tip['url'] ? ' href="' . esc_url( $tip['url'] ) . '"' : '';
			?>
			<<?php echo $wrapper_tag; ?> class="amarilla-tip-card"<?php echo $wrapper_attr; ?>>
				<?php if ( $tip['image'] ) : ?>
					<img src="<?php echo esc_url( $tip['image'] ); ?>" alt="<?php echo esc_attr( $tip['title'] ); ?>" loading="lazy">
				<?php endif; ?>
				<div class="amarilla-tip-content">
					<?php if ( $tip['tag'] ) : ?>
						<span class="tag"><?php echo esc_html( $tip['tag'] ); ?></span>
					<?php endif; ?>
					<?php if ( $tip['title'] ) : ?>
						<h3><?php echo esc_html( $tip['title'] ); ?></h3>
					<?php endif; ?>
				</div>
			</<?php echo $wrapper_tag; ?>>
		<?php endforeach; ?>
	</div>
</div>
<!-- /wp:html -->
</section>
<!-- /wp:group -->
