<?php
/**
 * Amarilla Tenerife — poptávkový / rezervační formulář
 *
 * Vlastní formulář (žádný externí plugin) s flow:
 *   1) Compact widget v hero (datum vyzvednutí → vrácení → lokalita → třída vozu)
 *      → po odeslání přesměruje na kontaktní stránku s předvyplněnými poli.
 *   2) Plný formulář na /kontakt/ (jméno, e-mail, telefon, poznámka, GDPR)
 *      → odešle e-mail provozovateli (amarilla_get_email()) a uloží
 *        příchozí poptávku jako CPT `amarilla_inquiry` pro administraci.
 *
 * Bezpečnost:
 *   - Honeypot pole (`amarilla_website`)
 *   - WP nonce + admin-post.php handler
 *   - Rate limit (1 odeslání / 60 s na IP, přes transient)
 *   - Serverová validace dat (datum vrácení > vyzvednutí, nikoliv minulost)
 *   - Sanitizace a `wp_kses` u poznámky
 *
 * @package Amarilla
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
 * 1) CPT amarilla_inquiry — uchovávání příchozích poptávek
 * ============================================================ */
function amarilla_register_inquiry_cpt() {
	$labels = array(
		'name'               => __( 'Poptávky', 'amarilla' ),
		'singular_name'      => __( 'Poptávka', 'amarilla' ),
		'menu_name'          => __( 'Poptávky', 'amarilla' ),
		'all_items'          => __( 'Všechny poptávky', 'amarilla' ),
		'add_new'            => __( 'Přidat ručně', 'amarilla' ),
		'add_new_item'       => __( 'Nová poptávka', 'amarilla' ),
		'edit_item'          => __( 'Detail poptávky', 'amarilla' ),
		'view_item'          => __( 'Zobrazit poptávku', 'amarilla' ),
		'search_items'       => __( 'Hledat v poptávkách', 'amarilla' ),
		'not_found'          => __( 'Žádné poptávky', 'amarilla' ),
		'not_found_in_trash' => __( 'V koši nejsou žádné poptávky', 'amarilla' ),
	);

	register_post_type( 'amarilla_inquiry', array(
		'labels'             => $labels,
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => false,
		'capability_type'    => 'post',
		'hierarchical'       => false,
		'menu_position'      => 6,
		'menu_icon'          => 'dashicons-email-alt',
		'supports'           => array( 'title', 'editor', 'custom-fields' ),
		'capabilities'       => array(
			// Vypneme možnost vytvářet nové poptávky z adminu —
			// chodí pouze z formuláře. Mazat ano (čištění spamu).
			'create_posts' => 'do_not_allow',
		),
		'map_meta_cap'       => true,
	) );
}
add_action( 'init', 'amarilla_register_inquiry_cpt' );

/* ============================================================
 * 2) Sloupce v admin tabulce poptávek
 * ============================================================ */
function amarilla_inquiry_columns( $columns ) {
	return array(
		'cb'              => $columns['cb'],
		'title'           => __( 'Žadatel', 'amarilla' ),
		'amarilla_email'  => __( 'E-mail', 'amarilla' ),
		'amarilla_dates'  => __( 'Termín', 'amarilla' ),
		'amarilla_pickup' => __( 'Vyzvednutí', 'amarilla' ),
		'amarilla_class'  => __( 'Třída vozu', 'amarilla' ),
		'date'            => __( 'Přijato', 'amarilla' ),
	);
}
add_filter( 'manage_amarilla_inquiry_posts_columns', 'amarilla_inquiry_columns' );

function amarilla_inquiry_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'amarilla_email':
			$email = get_post_meta( $post_id, '_inq_email', true );
			if ( $email ) {
				printf( '<a href="mailto:%1$s">%1$s</a>', esc_html( $email ) );
			}
			break;
		case 'amarilla_dates':
			$from = get_post_meta( $post_id, '_inq_pickup_date', true );
			$to   = get_post_meta( $post_id, '_inq_return_date', true );
			if ( $from || $to ) {
				echo esc_html( $from ) . ' → ' . esc_html( $to );
			}
			break;
		case 'amarilla_pickup':
			echo esc_html( get_post_meta( $post_id, '_inq_pickup_location', true ) );
			break;
		case 'amarilla_class':
			echo esc_html( get_post_meta( $post_id, '_inq_vehicle_class', true ) );
			break;
	}
}
add_action( 'manage_amarilla_inquiry_posts_custom_column', 'amarilla_inquiry_column_content', 10, 2 );

/* ============================================================
 * 3) Meta box s detailem v adminu
 * ============================================================ */
function amarilla_inquiry_meta_box() {
	add_meta_box(
		'amarilla_inquiry_detail',
		__( 'Detail poptávky', 'amarilla' ),
		'amarilla_inquiry_meta_render',
		'amarilla_inquiry',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'amarilla_inquiry_meta_box' );

function amarilla_inquiry_meta_render( $post ) {
	$fields = array(
		'_inq_name'            => __( 'Jméno', 'amarilla' ),
		'_inq_email'           => __( 'E-mail', 'amarilla' ),
		'_inq_phone'           => __( 'Telefon', 'amarilla' ),
		'_inq_pickup_date'     => __( 'Vyzvednutí (datum)', 'amarilla' ),
		'_inq_pickup_time'     => __( 'Vyzvednutí (čas)', 'amarilla' ),
		'_inq_return_date'     => __( 'Vrácení (datum)', 'amarilla' ),
		'_inq_return_time'     => __( 'Vrácení (čas)', 'amarilla' ),
		'_inq_pickup_location' => __( 'Místo vyzvednutí', 'amarilla' ),
		'_inq_return_location' => __( 'Místo vrácení', 'amarilla' ),
		'_inq_vehicle_class'   => __( 'Třída vozu', 'amarilla' ),
		'_inq_vehicle'         => __( 'Konkrétní vůz', 'amarilla' ),
		'_inq_driver_age'      => __( 'Věk řidiče', 'amarilla' ),
		'_inq_lang'            => __( 'Jazyk', 'amarilla' ),
		'_inq_ip'              => __( 'IP', 'amarilla' ),
		'_inq_user_agent'      => __( 'User-Agent', 'amarilla' ),
	);
	echo '<table class="form-table"><tbody>';
	foreach ( $fields as $key => $label ) {
		$value = get_post_meta( $post->ID, $key, true );
		printf(
			'<tr><th style="width:200px;">%s</th><td>%s</td></tr>',
			esc_html( $label ),
			$value ? esc_html( $value ) : '<span style="color:#999;">—</span>'
		);
	}
	echo '</tbody></table>';
}

/* ============================================================
 * 4) Renderování formuláře — shortcodes
 * ============================================================ */

/**
 * Třídy vozů (slug → label).
 * Vrací z taxonomie vehicle_category, takže přidání nové kategorie
 * v adminu se okamžitě promítne i do formuláře.
 */
function amarilla_get_vehicle_class_options() {
	$terms = get_terms( array(
		'taxonomy'   => 'vehicle_category',
		'hide_empty' => false,
	) );
	$out = array( '' => __( 'Jakákoliv třída', 'amarilla' ) );
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$out[ $term->slug ] = $term->name;
		}
	}
	return $out;
}

/**
 * Možnosti míst vyzvednutí — z customizeru `amarilla_locations`.
 * Vrací pole [slug => name]. Pokud nejsou nastavena, vrátí výchozí
 * sadu (TFS, TFN, Hotel — domluvíme).
 */
function amarilla_get_pickup_location_options() {
	$locations = amarilla_get_locations();
	$out = array();
	if ( $locations ) {
		foreach ( $locations as $loc ) {
			$slug = sanitize_title( $loc['name'] );
			$out[ $slug ] = $loc['name'];
		}
	}
	if ( empty( $out ) ) {
		$out = array(
			'tfs'   => __( 'Letiště Tenerife Sur (TFS)', 'amarilla' ),
			'tfn'   => __( 'Letiště Tenerife Norte (TFN)', 'amarilla' ),
			'hotel' => __( 'Hotel — domluvíme', 'amarilla' ),
		);
	}
	return $out;
}

/**
 * Compact verze pro hero — 4 pole + submit.
 * Action je kontaktní stránka (GET), takže fronta předvyplní
 * plný formulář bez nutnosti POST/JS.
 */
function amarilla_sc_booking_widget() {
	$cf7_shortcode = amarilla_get_theme_mod( 'amarilla_home_booking_form_shortcode' );

	if ( $cf7_shortcode && shortcode_exists( 'contact-form-7' ) ) {
		$cf7_form = do_shortcode( $cf7_shortcode );

		if ( trim( $cf7_form ) && trim( $cf7_form ) !== trim( $cf7_shortcode ) ) {
			ob_start();
			?>
			<div class="amarilla-form-card amarilla-cf7-form amarilla-booking-form">
				<?php
				// Contact Form 7 vrací kompletní formulářový markup.
				echo $cf7_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>
			<?php
			return ob_get_clean();
		}
	}

	$contact_url = amarilla_get_contact_url();
	$locations   = amarilla_get_pickup_location_options();
	$classes     = amarilla_get_vehicle_class_options();
	$tomorrow    = wp_date( 'Y-m-d', strtotime( 'tomorrow' ) );
	$day_after   = wp_date( 'Y-m-d', strtotime( 'tomorrow +5 days' ) );

	ob_start();
	?>
	<form class="amarilla-booking-widget" action="<?php echo esc_url( $contact_url ); ?>" method="get" novalidate>
		<div class="amarilla-booking-widget-grid">
			<label class="amarilla-bw-field">
				<span class="amarilla-bw-label"><?php esc_html_e( 'Vyzvednutí', 'amarilla' ); ?></span>
				<input type="date" name="pickup_date" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" value="<?php echo esc_attr( $tomorrow ); ?>" required>
			</label>
			<label class="amarilla-bw-field">
				<span class="amarilla-bw-label"><?php esc_html_e( 'Vrácení', 'amarilla' ); ?></span>
				<input type="date" name="return_date" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" value="<?php echo esc_attr( $day_after ); ?>" required>
			</label>
			<label class="amarilla-bw-field">
				<span class="amarilla-bw-label"><?php esc_html_e( 'Místo', 'amarilla' ); ?></span>
				<select name="pickup_location">
					<?php foreach ( $locations as $slug => $name ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="amarilla-bw-field">
				<span class="amarilla-bw-label"><?php esc_html_e( 'Třída vozu', 'amarilla' ); ?></span>
				<select name="vehicle_class">
					<?php foreach ( $classes as $slug => $name ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>
		<button type="submit" class="amarilla-btn amarilla-btn--primary amarilla-bw-submit">
			<?php esc_html_e( 'Spočítat cenu', 'amarilla' ); ?>
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
		</button>
		<p class="amarilla-bw-note"><?php esc_html_e( 'Žádná blokace karty, plné pojištění v ceně.', 'amarilla' ); ?></p>
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'amarilla_booking_widget', 'amarilla_sc_booking_widget' );

/**
 * Plný poptávkový formulář — pro kontaktní stránku.
 * Předvyplňuje hodnoty z GET parametrů (z compact widgetu i z vehicle CTA).
 * Po úspěšném odeslání zobrazí thank-you (přes ?success=1).
 */
function amarilla_sc_inquiry_form() {
	// Po úspěchu — thank you místo formuláře.
	if ( isset( $_GET['amarilla_success'] ) ) {
		ob_start();
		?>
		<div class="amarilla-inquiry-success" role="status" aria-live="polite">
			<div class="amarilla-inquiry-success-icon">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="40" height="40"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>
			</div>
			<h3><?php esc_html_e( 'Poptávka odeslána.', 'amarilla' ); ?></h3>
			<p><?php esc_html_e( 'Ozveme se vám e-mailem do hodiny během pracovních dní. Mezitím můžete projít náš vozový park.', 'amarilla' ); ?></p>
			<p>
				<a class="amarilla-btn amarilla-btn--ghost" href="<?php echo esc_url( amarilla_get_fleet_url() ); ?>">
					<?php esc_html_e( 'Prohlédnout vozy', 'amarilla' ); ?>
				</a>
			</p>
		</div>
		<?php
		return ob_get_clean();
	}

	// Prefill z GET parametrů.
	$pickup_date     = isset( $_GET['pickup_date'] )     ? sanitize_text_field( wp_unslash( $_GET['pickup_date'] ) )     : '';
	$return_date     = isset( $_GET['return_date'] )     ? sanitize_text_field( wp_unslash( $_GET['return_date'] ) )     : '';
	$pickup_location = isset( $_GET['pickup_location'] ) ? sanitize_text_field( wp_unslash( $_GET['pickup_location'] ) ) : '';
	$vehicle_class   = isset( $_GET['vehicle_class'] )   ? sanitize_text_field( wp_unslash( $_GET['vehicle_class'] ) )   : '';
	$vehicle_name    = isset( $_GET['vehicle'] )         ? sanitize_text_field( wp_unslash( $_GET['vehicle'] ) )         : '';

	// Chybové hlášení po neúspěšném odeslání.
	$error = isset( $_GET['amarilla_error'] ) ? sanitize_key( wp_unslash( $_GET['amarilla_error'] ) ) : '';
	$error_messages = array(
		'nonce'    => __( 'Vypršela platnost formuláře. Zkuste to prosím znovu.', 'amarilla' ),
		'required' => __( 'Vyplňte prosím všechna povinná pole.', 'amarilla' ),
		'email'    => __( 'Zadejte platnou e-mailovou adresu.', 'amarilla' ),
		'dates'    => __( 'Termín vrácení musí být po termínu vyzvednutí a v budoucnu.', 'amarilla' ),
		'gdpr'     => __( 'Pro odeslání je nutný souhlas se zpracováním údajů.', 'amarilla' ),
		'rate'     => __( 'Příliš mnoho odeslání. Zkuste to prosím za chvíli znovu.', 'amarilla' ),
		'spam'     => __( 'Odeslání bylo zamítnuto jako spam.', 'amarilla' ),
	);

	$locations = amarilla_get_pickup_location_options();
	$classes   = amarilla_get_vehicle_class_options();
	$privacy   = get_privacy_policy_url();

	ob_start();
	?>
	<form class="amarilla-inquiry-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
		<input type="hidden" name="action" value="amarilla_submit_inquiry">
		<?php wp_nonce_field( 'amarilla_submit_inquiry', '_amarilla_nonce' ); ?>
		<input type="hidden" name="_amarilla_referrer" value="<?php echo esc_attr( wp_get_referer() ?: home_url() ); ?>">
		<?php if ( function_exists( 'pll_current_language' ) ) : ?>
			<input type="hidden" name="lang" value="<?php echo esc_attr( pll_current_language() ); ?>">
		<?php endif; ?>

		<?php if ( $error && isset( $error_messages[ $error ] ) ) : ?>
			<div class="amarilla-inquiry-error" role="alert">
				<?php echo esc_html( $error_messages[ $error ] ); ?>
			</div>
		<?php endif; ?>

		<div class="amarilla-inquiry-section">
			<h3><?php esc_html_e( '1. Termín a místo', 'amarilla' ); ?></h3>
			<div class="amarilla-inquiry-grid amarilla-inquiry-grid--2">
				<label>
					<span><?php esc_html_e( 'Vyzvednutí — datum', 'amarilla' ); ?> <em>*</em></span>
					<input type="date" name="pickup_date" required min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" value="<?php echo esc_attr( $pickup_date ); ?>">
				</label>
				<label>
					<span><?php esc_html_e( 'Vyzvednutí — čas', 'amarilla' ); ?></span>
					<input type="time" name="pickup_time" value="10:00">
				</label>
				<label>
					<span><?php esc_html_e( 'Vrácení — datum', 'amarilla' ); ?> <em>*</em></span>
					<input type="date" name="return_date" required min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" value="<?php echo esc_attr( $return_date ); ?>">
				</label>
				<label>
					<span><?php esc_html_e( 'Vrácení — čas', 'amarilla' ); ?></span>
					<input type="time" name="return_time" value="10:00">
				</label>
				<label>
					<span><?php esc_html_e( 'Místo vyzvednutí', 'amarilla' ); ?></span>
					<select name="pickup_location">
						<?php foreach ( $locations as $slug => $name ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $pickup_location, $slug ); ?>><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>
					<span><?php esc_html_e( 'Místo vrácení', 'amarilla' ); ?></span>
					<select name="return_location">
						<option value=""><?php esc_html_e( 'Stejné jako vyzvednutí', 'amarilla' ); ?></option>
						<?php foreach ( $locations as $slug => $name ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
		</div>

		<div class="amarilla-inquiry-section">
			<h3><?php esc_html_e( '2. Vůz', 'amarilla' ); ?></h3>
			<div class="amarilla-inquiry-grid amarilla-inquiry-grid--2">
				<label>
					<span><?php esc_html_e( 'Třída vozu', 'amarilla' ); ?></span>
					<select name="vehicle_class">
						<?php foreach ( $classes as $slug => $name ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $vehicle_class, $slug ); ?>><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>
					<span><?php esc_html_e( 'Věk řidiče', 'amarilla' ); ?></span>
					<input type="number" name="driver_age" min="18" max="99" placeholder="25">
				</label>
				<?php if ( $vehicle_name ) : ?>
					<label class="amarilla-inquiry-grid--full">
						<span><?php esc_html_e( 'Konkrétní vůz', 'amarilla' ); ?></span>
						<input type="text" name="vehicle" value="<?php echo esc_attr( $vehicle_name ); ?>" readonly>
					</label>
				<?php endif; ?>
			</div>
		</div>

		<div class="amarilla-inquiry-section">
			<h3><?php esc_html_e( '3. Kontakt', 'amarilla' ); ?></h3>
			<div class="amarilla-inquiry-grid amarilla-inquiry-grid--2">
				<label>
					<span><?php esc_html_e( 'Jméno a příjmení', 'amarilla' ); ?> <em>*</em></span>
					<input type="text" name="name" required autocomplete="name">
				</label>
				<label>
					<span><?php esc_html_e( 'E-mail', 'amarilla' ); ?> <em>*</em></span>
					<input type="email" name="email" required autocomplete="email">
				</label>
				<label class="amarilla-inquiry-grid--full">
					<span><?php esc_html_e( 'Telefon (nebo WhatsApp)', 'amarilla' ); ?></span>
					<input type="tel" name="phone" autocomplete="tel">
				</label>
				<label class="amarilla-inquiry-grid--full">
					<span><?php esc_html_e( 'Poznámka', 'amarilla' ); ?></span>
					<textarea name="note" rows="4" placeholder="<?php esc_attr_e( 'Dětská sedačka, druhý řidič, GPS… cokoliv co potřebujete vědět dopředu.', 'amarilla' ); ?>"></textarea>
				</label>
			</div>
		</div>

		<div class="amarilla-inquiry-section">
			<label class="amarilla-inquiry-gdpr">
				<input type="checkbox" name="gdpr" value="1" required>
				<span>
					<?php
					if ( $privacy ) {
						printf(
							/* translators: %s: privacy policy link */
							wp_kses_post( __( 'Souhlasím se zpracováním osobních údajů za účelem odpovědi na poptávku. Více v %s.', 'amarilla' ) ),
							'<a href="' . esc_url( $privacy ) . '">' . esc_html__( 'zásadách ochrany osobních údajů', 'amarilla' ) . '</a>'
						);
					} else {
						esc_html_e( 'Souhlasím se zpracováním osobních údajů za účelem odpovědi na poptávku.', 'amarilla' );
					}
					?>
				</span>
			</label>
		</div>

		<?php // Honeypot — skryt přes CSS .amarilla-hp { position:absolute; left:-9999px; } ?>
		<div class="amarilla-hp" aria-hidden="true">
			<label>Website (nevyplňujte) <input type="text" name="amarilla_website" tabindex="-1" autocomplete="off"></label>
		</div>

		<div class="amarilla-inquiry-submit">
			<button type="submit" class="amarilla-btn amarilla-btn--primary">
				<?php esc_html_e( 'Odeslat poptávku', 'amarilla' ); ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
			</button>
			<p class="amarilla-inquiry-note"><?php esc_html_e( 'Odpovídáme do hodiny v pracovních dnech.', 'amarilla' ); ?></p>
		</div>
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'amarilla_inquiry_form', 'amarilla_sc_inquiry_form' );

/* ============================================================
 * 5) Handler — admin-post.php?action=amarilla_submit_inquiry
 * ============================================================ */
function amarilla_handle_inquiry_submission() {
	// Pro veřejné odesílání používáme nopriv variantu i přihlášenou variantu —
	// obě voláme stejnou funkci.

	$redirect_back = isset( $_POST['_amarilla_referrer'] ) ? esc_url_raw( wp_unslash( $_POST['_amarilla_referrer'] ) ) : home_url();

	// 1) Nonce
	if ( ! isset( $_POST['_amarilla_nonce'] ) || ! wp_verify_nonce( $_POST['_amarilla_nonce'], 'amarilla_submit_inquiry' ) ) {
		wp_safe_redirect( add_query_arg( 'amarilla_error', 'nonce', $redirect_back ) );
		exit;
	}

	// 2) Honeypot
	if ( ! empty( $_POST['amarilla_website'] ) ) {
		// Vrátíme tichý úspěch, ať bot neví, že jsme ho odhalili.
		wp_safe_redirect( add_query_arg( 'amarilla_success', '1', $redirect_back ) );
		exit;
	}

	// 3) Rate limit (60 s na IP)
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( $ip ) {
		$key = 'amarilla_inq_' . md5( $ip );
		if ( get_transient( $key ) ) {
			wp_safe_redirect( add_query_arg( 'amarilla_error', 'rate', $redirect_back ) );
			exit;
		}
		set_transient( $key, 1, 60 );
	}

	// 4) Sběr a sanitizace
	$data = array(
		'pickup_date'     => isset( $_POST['pickup_date'] )     ? sanitize_text_field( wp_unslash( $_POST['pickup_date'] ) )     : '',
		'pickup_time'     => isset( $_POST['pickup_time'] )     ? sanitize_text_field( wp_unslash( $_POST['pickup_time'] ) )     : '',
		'return_date'     => isset( $_POST['return_date'] )     ? sanitize_text_field( wp_unslash( $_POST['return_date'] ) )     : '',
		'return_time'     => isset( $_POST['return_time'] )     ? sanitize_text_field( wp_unslash( $_POST['return_time'] ) )     : '',
		'pickup_location' => isset( $_POST['pickup_location'] ) ? sanitize_text_field( wp_unslash( $_POST['pickup_location'] ) ) : '',
		'return_location' => isset( $_POST['return_location'] ) ? sanitize_text_field( wp_unslash( $_POST['return_location'] ) ) : '',
		'vehicle_class'   => isset( $_POST['vehicle_class'] )   ? sanitize_text_field( wp_unslash( $_POST['vehicle_class'] ) )   : '',
		'vehicle'         => isset( $_POST['vehicle'] )         ? sanitize_text_field( wp_unslash( $_POST['vehicle'] ) )         : '',
		'driver_age'      => isset( $_POST['driver_age'] )      ? absint( $_POST['driver_age'] )                                  : '',
		'name'            => isset( $_POST['name'] )            ? sanitize_text_field( wp_unslash( $_POST['name'] ) )            : '',
		'email'           => isset( $_POST['email'] )           ? sanitize_email( wp_unslash( $_POST['email'] ) )                : '',
		'phone'           => isset( $_POST['phone'] )           ? sanitize_text_field( wp_unslash( $_POST['phone'] ) )           : '',
		'note'            => isset( $_POST['note'] )            ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) )        : '',
		'gdpr'            => ! empty( $_POST['gdpr'] ),
		'lang'            => isset( $_POST['lang'] )            ? sanitize_text_field( wp_unslash( $_POST['lang'] ) )            : '',
	);

	// 5) Validace
	if ( empty( $data['name'] ) || empty( $data['email'] ) || empty( $data['pickup_date'] ) || empty( $data['return_date'] ) ) {
		wp_safe_redirect( add_query_arg( 'amarilla_error', 'required', $redirect_back ) );
		exit;
	}
	if ( ! is_email( $data['email'] ) ) {
		wp_safe_redirect( add_query_arg( 'amarilla_error', 'email', $redirect_back ) );
		exit;
	}
	if ( ! $data['gdpr'] ) {
		wp_safe_redirect( add_query_arg( 'amarilla_error', 'gdpr', $redirect_back ) );
		exit;
	}
	$today_ts  = strtotime( wp_date( 'Y-m-d' ) );
	$pickup_ts = strtotime( $data['pickup_date'] );
	$return_ts = strtotime( $data['return_date'] );
	if ( ! $pickup_ts || ! $return_ts || $pickup_ts < $today_ts || $return_ts <= $pickup_ts ) {
		wp_safe_redirect( add_query_arg( 'amarilla_error', 'dates', $redirect_back ) );
		exit;
	}

	// 6) Vytvořit CPT
	$title = sprintf(
		'%s — %s → %s',
		$data['name'],
		$data['pickup_date'],
		$data['return_date']
	);
	$post_id = wp_insert_post( array(
		'post_type'   => 'amarilla_inquiry',
		'post_status' => 'publish',
		'post_title'  => $title,
		'post_content' => $data['note'],
	) );

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		wp_safe_redirect( add_query_arg( 'amarilla_error', 'required', $redirect_back ) );
		exit;
	}

	// Meta
	$meta_map = array(
		'_inq_pickup_date'     => $data['pickup_date'],
		'_inq_pickup_time'     => $data['pickup_time'],
		'_inq_return_date'     => $data['return_date'],
		'_inq_return_time'     => $data['return_time'],
		'_inq_pickup_location' => $data['pickup_location'],
		'_inq_return_location' => $data['return_location'],
		'_inq_vehicle_class'   => $data['vehicle_class'],
		'_inq_vehicle'         => $data['vehicle'],
		'_inq_driver_age'      => $data['driver_age'],
		'_inq_name'            => $data['name'],
		'_inq_email'           => $data['email'],
		'_inq_phone'           => $data['phone'],
		'_inq_lang'            => $data['lang'],
		'_inq_ip'              => $ip,
		'_inq_user_agent'      => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
	);
	foreach ( $meta_map as $k => $v ) {
		update_post_meta( $post_id, $k, $v );
	}

	// 7) Odeslat e-mail provozovateli (a kopii žadateli pokud má sense)
	amarilla_send_inquiry_emails( $data, $post_id );

	// Akce pro integraci (CRM, Slack webhook apod.)
	do_action( 'amarilla_inquiry_received', $post_id, $data );

	// 8) Redirect na thank-you
	wp_safe_redirect( add_query_arg( 'amarilla_success', '1', remove_query_arg( 'amarilla_error', $redirect_back ) ) );
	exit;
}
add_action( 'admin_post_amarilla_submit_inquiry',        'amarilla_handle_inquiry_submission' );
add_action( 'admin_post_nopriv_amarilla_submit_inquiry', 'amarilla_handle_inquiry_submission' );

/* ============================================================
 * 6) E-mail šablony
 * ============================================================ */
function amarilla_send_inquiry_emails( $data, $post_id ) {
	$site_name   = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$operator_to = amarilla_get_email();
	$admin_email = get_option( 'admin_email' );

	$locations_map = amarilla_get_pickup_location_options();
	$pickup_label  = isset( $locations_map[ $data['pickup_location'] ] ) ? $locations_map[ $data['pickup_location'] ] : $data['pickup_location'];
	$return_label  = $data['return_location'] && isset( $locations_map[ $data['return_location'] ] )
		? $locations_map[ $data['return_location'] ]
		: __( 'Stejné jako vyzvednutí', 'amarilla' );

	$classes_map  = amarilla_get_vehicle_class_options();
	$class_label  = isset( $classes_map[ $data['vehicle_class'] ] ) ? $classes_map[ $data['vehicle_class'] ] : ( $data['vehicle_class'] ?: __( 'Jakákoliv třída', 'amarilla' ) );

	// === Provozovatel ===
	$subject_op = sprintf(
		/* translators: 1: name, 2: dates */
		__( '[%1$s] Nová poptávka — %2$s', 'amarilla' ),
		$site_name,
		$data['name']
	);

	$lines_op = array(
		'<strong>' . esc_html__( 'Nová poptávka z webu', 'amarilla' ) . '</strong>',
		'',
		'<strong>' . esc_html__( 'Termín', 'amarilla' ) . ':</strong> ' . esc_html( $data['pickup_date'] . ' ' . $data['pickup_time'] . '  →  ' . $data['return_date'] . ' ' . $data['return_time'] ),
		'<strong>' . esc_html__( 'Vyzvednutí', 'amarilla' ) . ':</strong> ' . esc_html( $pickup_label ),
		'<strong>' . esc_html__( 'Vrácení', 'amarilla' ) . ':</strong> ' . esc_html( $return_label ),
		'<strong>' . esc_html__( 'Třída vozu', 'amarilla' ) . ':</strong> ' . esc_html( $class_label ),
	);
	if ( $data['vehicle'] ) {
		$lines_op[] = '<strong>' . esc_html__( 'Konkrétní vůz', 'amarilla' ) . ':</strong> ' . esc_html( $data['vehicle'] );
	}
	if ( $data['driver_age'] ) {
		$lines_op[] = '<strong>' . esc_html__( 'Věk řidiče', 'amarilla' ) . ':</strong> ' . esc_html( $data['driver_age'] );
	}
	$lines_op[] = '';
	$lines_op[] = '<strong>' . esc_html__( 'Kontakt', 'amarilla' ) . '</strong>';
	$lines_op[] = esc_html( $data['name'] );
	$lines_op[] = esc_html( $data['email'] );
	if ( $data['phone'] ) {
		$lines_op[] = esc_html( $data['phone'] );
	}
	if ( $data['note'] ) {
		$lines_op[] = '';
		$lines_op[] = '<strong>' . esc_html__( 'Poznámka', 'amarilla' ) . ':</strong>';
		$lines_op[] = nl2br( esc_html( $data['note'] ) );
	}
	$lines_op[] = '';
	$lines_op[] = '<a href="' . esc_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ) . '">' . esc_html__( 'Otevřít v adminu', 'amarilla' ) . '</a>';

	$body_op = '<div style="font-family:sans-serif;font-size:14px;line-height:1.6;color:#0F2A3D;">' . implode( '<br>', $lines_op ) . '</div>';

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'Reply-To: ' . sprintf( '%s <%s>', $data['name'], $data['email'] ),
	);

	// Recipients: operator email + admin email (pokud se liší a operator je vyplněn)
	$recipients = array_filter( array_unique( array( $operator_to, $admin_email ) ) );
	wp_mail( $recipients, $subject_op, $body_op, $headers );

	// === Potvrzení žadateli ===
	$subject_client = sprintf(
		/* translators: %s: site name */
		__( '%s — vaše poptávka přijata', 'amarilla' ),
		$site_name
	);
	$lines_client = array(
		sprintf( esc_html__( 'Dobrý den %s,', 'amarilla' ), esc_html( $data['name'] ) ),
		'',
		esc_html__( 'děkujeme za vaši poptávku. Ozveme se vám e-mailem nebo telefonem do hodiny v pracovních dnech, většinou rychleji.', 'amarilla' ),
		'',
		'<strong>' . esc_html__( 'Shrnutí poptávky', 'amarilla' ) . '</strong>',
		esc_html__( 'Termín', 'amarilla' ) . ': ' . esc_html( $data['pickup_date'] . ' → ' . $data['return_date'] ),
		esc_html__( 'Vyzvednutí', 'amarilla' ) . ': ' . esc_html( $pickup_label ),
		esc_html__( 'Třída vozu', 'amarilla' ) . ': ' . esc_html( $class_label ),
		'',
		esc_html__( 'Pokud byste se chtěli na něco zeptat dříve, stačí na tento e-mail odpovědět.', 'amarilla' ),
		'',
		esc_html( $site_name ),
		amarilla_get_phone() ? esc_html( amarilla_get_phone() ) : '',
	);
	$body_client = '<div style="font-family:sans-serif;font-size:14px;line-height:1.6;color:#0F2A3D;">' . implode( '<br>', array_filter( $lines_client ) ) . '</div>';

	$headers_client = array(
		'Content-Type: text/html; charset=UTF-8',
	);
	if ( $operator_to ) {
		$headers_client[] = 'Reply-To: ' . $operator_to;
	}

	wp_mail( $data['email'], $subject_client, $body_client, $headers_client );
}

/* ============================================================
 * 7) Aktualizace fleet-card CTA: směrovat na kontakt s prefillem
 * Toto je zajištěno už ve `block-bindings.php` (přepíšeme v update kroku).
 * ============================================================ */
