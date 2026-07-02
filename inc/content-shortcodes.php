<?php
/**
 * Amarilla Tenerife — Customizer shortcodes
 *
 * Block templates (HTML template parts) nemohou obsahovat PHP, proto
 * dynamické hodnoty z Customizeru vystavujeme jako shortcodes.
 *
 * @package Amarilla
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
 * SVG IKONY (helper)
 * ============================================================ */
function amarilla_get_icon( $name, $size = 18 ) {
	$icons = array(
		'check'    => '<path d="M20 6L9 17l-5-5"/>',
		'shield'   => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
		'clock'    => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
		'location' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>',
		'car'      => '<path d="M5 17h14M7 17v-3M17 17v-3M5 14h14l-2-6H7l-2 6z"/><circle cx="8" cy="17" r="2"/><circle cx="16" cy="17" r="2"/>',
		'key'      => '<circle cx="8" cy="15" r="4"/><path d="M10.85 12.15L19 4M18 5l2 2M15 8l2 2"/>',
		'star'     => '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>',
		'phone'    => '<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.13.96.37 1.9.72 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.91.35 1.85.59 2.81.72A2 2 0 0122 16.92z"/>',
		'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><polyline points="3,7 12,13 21,7"/>',
		'arrow'    => '<path d="M5 12h14M12 5l7 7-7 7"/>',
	);
	$path = isset( $icons[ $name ] ) ? $icons[ $name ] : $icons['check'];
	return sprintf(
		'<svg viewBox="0 0 24 24" width="%1$d" height="%1$d" fill="none" stroke="currentColor" stroke-width="2">%2$s</svg>',
		(int) $size,
		$path
	);
}

/* ============================================================
 * TOPBAR — kompletní obsah jako jediný shortcode
 * ============================================================ */
function amarilla_sc_topbar() {
	if ( ! amarilla_get_theme_mod( 'amarilla_topbar_enabled' ) ) {
		return '';
	}

	$phone_display = amarilla_get_theme_mod( 'amarilla_topbar_phone' );
	$phone_link    = amarilla_get_theme_mod( 'amarilla_topbar_phone_link' );
	$location      = amarilla_get_theme_mod( 'amarilla_topbar_location' );
	$show_langs    = amarilla_get_theme_mod( 'amarilla_topbar_show_langs' );

	ob_start();
	?>
	<div class="amarilla-topbar">
		<div class="amarilla-topbar-inner">
			<div class="amarilla-topbar-info">
				<?php if ( $phone_display ) : ?>
					<a class="amarilla-topbar-phone-link" href="tel:<?php echo esc_attr( $phone_link ); ?>">
						<?php echo amarilla_get_icon( 'phone', 14 ); ?>
						<span class="amarilla-topbar-phone"><?php echo esc_html( $phone_display ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( $location ) : ?>
					<span>
						<?php echo amarilla_get_icon( 'location', 14 ); ?>
						<span class="amarilla-topbar-location"><?php echo esc_html( $location ); ?></span>
					</span>
				<?php endif; ?>
			</div>
			<?php if ( $show_langs ) : ?>
				<?php echo amarilla_language_switcher_shortcode(); ?>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'amarilla_topbar', 'amarilla_sc_topbar' );

/* ============================================================
 * LOGO (s fallbackem na textové logo)
 * ============================================================ */
function amarilla_sc_logo() {
	$width      = (int) amarilla_get_theme_mod( 'amarilla_logo_width' );
	$show_text  = amarilla_get_theme_mod( 'amarilla_show_text_logo_fallback' );
	$site_name  = get_bloginfo( 'name' );
	$home_url   = home_url( '/' );

	// 1) custom logo přes WP
	if ( has_custom_logo() ) {
		$logo_id  = get_theme_mod( 'custom_logo' );
		$logo_src = wp_get_attachment_image_src( $logo_id, 'full' );
		if ( $logo_src ) {
			return sprintf(
				'<a href="%1$s" class="custom-logo-link" rel="home"><img src="%2$s" alt="%3$s" width="%4$d" style="height:auto;display:block;max-width:100%%;" /></a>',
				esc_url( $home_url ),
				esc_url( $logo_src[0] ),
				esc_attr( $site_name ),
				$width
			);
		}
	}

	// 2) textový fallback
	if ( $show_text ) {
		$initial = mb_substr( $site_name, 0, 1, 'UTF-8' );
		return sprintf(
			'<a href="%1$s" class="amarilla-logo-placeholder" rel="home"><span class="amarilla-logo-mark">%2$s</span>%3$s</a>',
			esc_url( $home_url ),
			esc_html( $initial ),
			esc_html( $site_name )
		);
	}

	// 3) jen název webu
	return sprintf(
		'<a href="%1$s" class="amarilla-logo-text" rel="home" style="font-family:\'Fraunces\',serif;font-size:24px;font-weight:600;text-decoration:none;color:inherit;">%2$s</a>',
		esc_url( $home_url ),
		esc_html( $site_name )
	);
}
add_shortcode( 'amarilla_logo', 'amarilla_sc_logo' );

/* ============================================================
 * LOGO PRO TMAVÉ POZADÍ (patička)
 * ============================================================ */
function amarilla_sc_logo_light() {
	$light_logo = amarilla_get_theme_mod( 'amarilla_logo_light' );
	$home_url   = home_url( '/' );
	$site_name  = get_bloginfo( 'name' );

	if ( $light_logo ) {
		return sprintf(
			'<a href="%1$s" rel="home" style="display:inline-block;"><img src="%2$s" alt="%3$s" style="max-width:180px;height:auto;display:block;" /></a>',
			esc_url( $home_url ),
			esc_url( $light_logo ),
			esc_attr( $site_name )
		);
	}

	$initial = mb_substr( $site_name, 0, 1, 'UTF-8' );
	return sprintf(
		'<a href="%1$s" class="amarilla-logo-placeholder" style="color:#F8F2E4;" rel="home"><span class="amarilla-logo-mark">%2$s</span>%3$s</a>',
		esc_url( $home_url ),
		esc_html( $initial ),
		esc_html( $site_name )
	);
}
add_shortcode( 'amarilla_logo_light', 'amarilla_sc_logo_light' );

/* ============================================================
 * CTA TLAČÍTKO V HLAVIČCE
 * ============================================================ */
function amarilla_sc_header_cta() {
	$contact_url = amarilla_get_contact_url();
	$text = __( 'Nezávazná poptávka', 'amarilla' );
	return sprintf(
		'<a class="amarilla-btn amarilla-btn--primary" href="%1$s">%2$s %3$s</a>',
		esc_url( $contact_url ),
		esc_html( $text ),
		amarilla_get_icon( 'arrow', 14 )
	);
}
add_shortcode( 'amarilla_header_cta', 'amarilla_sc_header_cta' );

/* ============================================================
 * KOMPLETNÍ PATIČKA (jediný shortcode pro footer.html)
 * ============================================================ */
function amarilla_sc_footer() {
	$about      = amarilla_get_theme_mod( 'amarilla_footer_about' );
	$col1_title = amarilla_get_theme_mod( 'amarilla_footer_col1_title' );
	$col1_links = amarilla_get_theme_mod( 'amarilla_footer_col1_links' );
	$col2_title = amarilla_get_theme_mod( 'amarilla_footer_col2_title' );
	$col2_links = amarilla_get_theme_mod( 'amarilla_footer_col2_links' );
	$copyright  = amarilla_get_theme_mod( 'amarilla_footer_copyright' );

	$phone   = amarilla_get_phone();
	$email   = amarilla_get_email();
	$address = amarilla_get_theme_mod( 'amarilla_address_full' );

	$socials = array(
		'facebook'    => amarilla_get_theme_mod( 'amarilla_social_facebook' ),
		'instagram'   => amarilla_get_theme_mod( 'amarilla_social_instagram' ),
		'youtube'     => amarilla_get_theme_mod( 'amarilla_social_youtube' ),
		'tripadvisor' => amarilla_get_theme_mod( 'amarilla_social_tripadvisor' ),
	);

	ob_start();
	?>
	<footer class="amarilla-footer">
		<div class="amarilla-footer-container">
			<div class="amarilla-footer-grid">
				<div class="amarilla-footer-about">
					<?php echo amarilla_sc_logo_light(); ?>
					<p><?php echo esc_html( $about ); ?></p>
					<?php if ( array_filter( $socials ) ) : ?>
						<div class="amarilla-footer-social">
							<?php foreach ( $socials as $key => $url ) :
								if ( ! $url ) continue; ?>
								<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( ucfirst( $key ) ); ?>">
									<?php echo esc_html( strtoupper( substr( $key, 0, 2 ) ) ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<?php
				// Sloupce s odkazy
				$columns = array(
					array( $col1_title, $col1_links ),
					array( $col2_title, $col2_links ),
				);
				foreach ( $columns as $index => $col ) :
					$lines = array_filter( array_map( 'trim', explode( "\n", $col[1] ) ) );
					if ( empty( $lines ) ) continue;
					?>
					<div class="amarilla-footer-col amarilla-footer-col-<?php echo esc_attr( $index + 1 ); ?>">
						<h5><?php echo esc_html( $col[0] ); ?></h5>
						<ul>
							<?php foreach ( $lines as $line ) :
								$parts = array_map( 'trim', explode( '|', $line, 2 ) );
								$label = $parts[0];
								$url   = isset( $parts[1] ) ? $parts[1] : '#';
								?>
								<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>

				<div class="amarilla-footer-contact">
					<h5><?php esc_html_e( 'Kontakt', 'amarilla' ); ?></h5>
					<ul>
						<?php if ( $phone ) : ?>
							<li><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li>
						<?php endif; ?>
						<?php if ( $email ) : ?>
							<li><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
						<?php endif; ?>
						<?php if ( $address ) : ?>
							<li class="amarilla-footer-address"><?php echo nl2br( esc_html( $address ) ); ?></li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
			<div class="amarilla-footer-bottom">
				<span><?php echo esc_html( $copyright ); ?></span>
				<span><?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?></span>
			</div>
		</div>
	</footer>
	<?php
	return ob_get_clean();
}
add_shortcode( 'amarilla_footer', 'amarilla_sc_footer' );

/**
 * Shortcode: [amarilla_contact_info] — Kontaktní údaje pro stránku Kontakt
 * Renderuje telefon, email, adresu a provozní dobu z Customizeru.
 */
function amarilla_sc_contact_info() {
	$phone        = amarilla_get_phone();
	$phone_link   = amarilla_get_phone_link();
	$email        = amarilla_get_email();
	$address_full = amarilla_get_theme_mod( 'amarilla_address_full' );
	$hours        = amarilla_get_theme_mod( 'amarilla_hours' );

	$address_lines = array_filter( array_map( 'trim', preg_split( "/\r\n|\r|\n/", $address_full ) ) );
	$hours_lines   = array_filter( array_map( 'trim', preg_split( "/\r\n|\r|\n/", $hours ) ) );

	ob_start();
	?>
	<div class="amarilla-contact-info">
		<dl>
			<?php if ( $phone ) : ?>
			<div>
				<dt><?php esc_html_e( 'Telefon (24/7)', 'amarilla-tenerife' ); ?></dt>
				<dd><a href="tel:<?php echo esc_attr( $phone_link ); ?>"><?php echo esc_html( $phone ); ?></a></dd>
			</div>
			<?php endif; ?>

			<?php if ( $email ) : ?>
			<div>
				<dt><?php esc_html_e( 'E-mail', 'amarilla-tenerife' ); ?></dt>
				<dd><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></dd>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $address_lines ) ) : ?>
			<div>
				<dt><?php esc_html_e( 'Adresa', 'amarilla-tenerife' ); ?></dt>
				<dd><?php echo wp_kses_post( implode( '<br>', array_map( 'esc_html', $address_lines ) ) ); ?></dd>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $hours_lines ) ) : ?>
			<div>
				<dt><?php esc_html_e( 'Provozní doba', 'amarilla-tenerife' ); ?></dt>
				<dd>
					<?php
					$first = array_shift( $hours_lines );
					echo esc_html( $first );
					if ( ! empty( $hours_lines ) ) {
						echo '<br><span style="font-size:14px;color:var(--amarilla-gray-text);font-family:\'DM Sans\',sans-serif;font-weight:400;">';
						echo wp_kses_post( implode( '<br>', array_map( 'esc_html', $hours_lines ) ) );
						echo '</span>';
					}
					?>
				</dd>
			</div>
			<?php endif; ?>
		</dl>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'amarilla_contact_info', 'amarilla_sc_contact_info' );
