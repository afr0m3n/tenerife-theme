<?php
/**
 * Amarilla Tenerife — pobočky / místa vyzvednutí
 *
 * Modul řeší konfiguraci poboček (TFS, TFN, hotely, partneři) a jejich
 * zobrazení na webu. Mapa je řešena přes OpenStreetMap iframe — žádné
 * Google Maps, žádný Mapbox token, žádné externí JS knihovny. GDPR-friendly,
 * konzistentní s rozhodnutím self-hostovat fonty.
 *
 * Datový model:
 *   - Customizer panel „Pobočky" — pro 1 až 6 míst.
 *   - Pro každé místo: název, adresa, otevírací doba, lat, lng.
 *   - OSM URL je dopočítané z lat/lng.
 *
 * @package Amarilla
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maximální počet poboček v Customizeru.
 */
function amarilla_locations_max() {
	return 6;
}

/**
 * Strukturované načtení poboček z theme_mod.
 *
 * @return array<int, array{name:string,address:string,hours:string,lat:string,lng:string,osm_url:string}>
 */
function amarilla_get_locations() {
	$locations = array();
	for ( $i = 1; $i <= amarilla_locations_max(); $i++ ) {
		$name = trim( (string) amarilla_get_theme_mod( "amarilla_loc_{$i}_name" ) );
		if ( ! $name ) {
			continue;
		}
		$lat = (string) amarilla_get_theme_mod( "amarilla_loc_{$i}_lat" );
		$lng = (string) amarilla_get_theme_mod( "amarilla_loc_{$i}_lng" );
		$locations[] = array(
			'name'    => $name,
			'address' => (string) amarilla_get_theme_mod( "amarilla_loc_{$i}_address" ),
			'hours'   => (string) amarilla_get_theme_mod( "amarilla_loc_{$i}_hours" ),
			'lat'     => $lat,
			'lng'     => $lng,
			'osm_url' => amarilla_build_osm_url( $lat, $lng, $name ),
		);
	}
	return $locations;
}

/**
 * Vrátí URL na OSM s markerem (kliknutelný odkaz "Otevřít v mapě")
 */
function amarilla_build_osm_url( $lat, $lng, $name = '' ) {
	if ( ! is_numeric( $lat ) || ! is_numeric( $lng ) ) {
		return '';
	}
	return sprintf(
		'https://www.openstreetmap.org/?mlat=%1$s&mlon=%2$s#map=17/%1$s/%2$s',
		rawurlencode( $lat ),
		rawurlencode( $lng )
	);
}

/**
 * Vrátí URL pro OSM embed iframe — bounding box přes všechny pobočky.
 * Pokud není dostatek dat, vrátí výchozí bbox pro Tenerife.
 */
function amarilla_build_osm_embed_url() {
	$lats = array();
	$lngs = array();
	foreach ( amarilla_get_locations() as $loc ) {
		if ( is_numeric( $loc['lat'] ) && is_numeric( $loc['lng'] ) ) {
			$lats[] = (float) $loc['lat'];
			$lngs[] = (float) $loc['lng'];
		}
	}
	if ( count( $lats ) >= 1 ) {
		$lat_min = min( $lats );
		$lat_max = max( $lats );
		$lng_min = min( $lngs );
		$lng_max = max( $lngs );
		// Padding kolem bboxu (aby markery nebyly úplně na okraji)
		$lat_pad = max( 0.02, ( $lat_max - $lat_min ) * 0.2 );
		$lng_pad = max( 0.02, ( $lng_max - $lng_min ) * 0.2 );
		$bbox = sprintf( '%.4f,%.4f,%.4f,%.4f',
			$lng_min - $lng_pad,
			$lat_min - $lat_pad,
			$lng_max + $lng_pad,
			$lat_max + $lat_pad
		);

		// Jeden marker — pokud je jen jedna lokace
		$marker = '';
		if ( count( $lats ) === 1 ) {
			$marker = '&marker=' . $lats[0] . ',' . $lngs[0];
		}

		return 'https://www.openstreetmap.org/export/embed.html?bbox=' . $bbox . '&layer=mapnik' . $marker;
	}

	// Fallback — celé Tenerife
	return 'https://www.openstreetmap.org/export/embed.html?bbox=-16.9300,27.9900,-16.1000,28.6000&layer=mapnik';
}

/* ============================================================
 * Customizer — panel „Pobočky"
 * ============================================================ */
function amarilla_customize_locations( $wp_customize ) {
	if ( ! $wp_customize->get_panel( 'amarilla_panel' ) ) {
		return;
	}
	$wp_customize->add_section( 'amarilla_locations', array(
		'title'       => __( 'Pobočky / místa vyzvednutí', 'amarilla' ),
		'description' => __( 'Až 6 míst. Pro mapu vyplňte zeměpisné souřadnice (najdete je kliknutím na openstreetmap.org → „Zobrazit adresu").', 'amarilla' ),
		'panel'       => 'amarilla_panel',
		'priority'    => 85,
	) );

	$wp_customize->add_setting( 'amarilla_locations_enabled', array(
		'default'           => amarilla_get_theme_default( 'amarilla_locations_enabled' ),
		'sanitize_callback' => 'amarilla_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'amarilla_locations_enabled', array(
		'label'   => __( 'Zobrazit sekci poboček na hlavní stránce', 'amarilla' ),
		'section' => 'amarilla_locations',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'amarilla_locations_eyebrow', array(
		'default'           => amarilla_get_theme_default( 'amarilla_locations_eyebrow' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_locations_eyebrow', array(
		'label'   => __( 'Popisek (nad nadpisem)', 'amarilla' ),
		'section' => 'amarilla_locations',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_locations_title', array(
		'default'           => amarilla_get_theme_default( 'amarilla_locations_title' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_locations_title', array(
		'label'   => __( 'Nadpis sekce', 'amarilla' ),
		'section' => 'amarilla_locations',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_locations_lead', array(
		'default'           => amarilla_get_theme_default( 'amarilla_locations_lead' ),
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_locations_lead', array(
		'label'   => __( 'Popis sekce', 'amarilla' ),
		'section' => 'amarilla_locations',
		'type'    => 'textarea',
	) );

	for ( $i = 1; $i <= amarilla_locations_max(); $i++ ) {
		$wp_customize->add_setting( "amarilla_loc_{$i}_name", array(
			'default'           => amarilla_get_theme_default( "amarilla_loc_{$i}_name" ),
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "amarilla_loc_{$i}_name", array(
			'label'   => sprintf( __( 'Pobočka %d — název', 'amarilla' ), $i ),
			'section' => 'amarilla_locations',
			'type'    => 'text',
		) );

		$wp_customize->add_setting( "amarilla_loc_{$i}_address", array(
			'default'           => amarilla_get_theme_default( "amarilla_loc_{$i}_address" ),
			'sanitize_callback' => 'sanitize_textarea_field',
		) );
		$wp_customize->add_control( "amarilla_loc_{$i}_address", array(
			'label'       => sprintf( __( 'Pobočka %d — adresa', 'amarilla' ), $i ),
			'description' => __( 'Jeden řádek = nový řádek na webu.', 'amarilla' ),
			'section'     => 'amarilla_locations',
			'type'        => 'textarea',
		) );

		$wp_customize->add_setting( "amarilla_loc_{$i}_hours", array(
			'default'           => amarilla_get_theme_default( "amarilla_loc_{$i}_hours" ),
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "amarilla_loc_{$i}_hours", array(
			'label'   => sprintf( __( 'Pobočka %d — otevírací doba', 'amarilla' ), $i ),
			'section' => 'amarilla_locations',
			'type'    => 'text',
		) );

		$wp_customize->add_setting( "amarilla_loc_{$i}_lat", array(
			'default'           => amarilla_get_theme_default( "amarilla_loc_{$i}_lat" ),
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "amarilla_loc_{$i}_lat", array(
			'label'   => sprintf( __( 'Pobočka %d — zeměpisná šířka (lat)', 'amarilla' ), $i ),
			'section' => 'amarilla_locations',
			'type'    => 'text',
		) );

		$wp_customize->add_setting( "amarilla_loc_{$i}_lng", array(
			'default'           => amarilla_get_theme_default( "amarilla_loc_{$i}_lng" ),
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "amarilla_loc_{$i}_lng", array(
			'label'   => sprintf( __( 'Pobočka %d — zeměpisná délka (lng)', 'amarilla' ), $i ),
			'section' => 'amarilla_locations',
			'type'    => 'text',
		) );
	}
}
add_action( 'customize_register', 'amarilla_customize_locations', 30 );

/* ============================================================
 * Shortcode pro vykreslení mapy + seznamu poboček
 * Použito v patternu amarilla/locations-map a kdekoli jinde
 * ============================================================ */
function amarilla_sc_locations_map() {
	$locations = amarilla_get_locations();
	if ( empty( $locations ) ) {
		return '<p>' . esc_html__( 'Pobočky nejsou nakonfigurované — nastavte je v Customizeru → Pobočky.', 'amarilla' ) . '</p>';
	}

	$embed = amarilla_build_osm_embed_url();

	ob_start();
	?>
	<div class="amarilla-locations-grid">
		<div class="amarilla-locations-list">
			<?php foreach ( $locations as $i => $loc ) : ?>
				<article class="amarilla-location-card">
					<div class="amarilla-location-index"><?php echo esc_html( $i + 1 ); ?></div>
					<div class="amarilla-location-body">
						<h3><?php echo esc_html( $loc['name'] ); ?></h3>
						<?php if ( $loc['address'] ) : ?>
							<p class="amarilla-location-address"><?php echo nl2br( esc_html( $loc['address'] ) ); ?></p>
						<?php endif; ?>
						<?php if ( $loc['hours'] ) : ?>
							<p class="amarilla-location-hours">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
								<?php echo esc_html( $loc['hours'] ); ?>
							</p>
						<?php endif; ?>
						<?php if ( $loc['osm_url'] ) : ?>
							<a class="amarilla-location-link" href="<?php echo esc_url( $loc['osm_url'] ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Otevřít v mapě', 'amarilla' ); ?>
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
							</a>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<div class="amarilla-locations-map">
			<iframe
				src="<?php echo esc_url( $embed ); ?>"
				width="100%"
				height="100%"
				frameborder="0"
				style="border:0;"
				loading="lazy"
				title="<?php esc_attr_e( 'Mapa poboček — OpenStreetMap', 'amarilla' ); ?>"
				referrerpolicy="no-referrer-when-downgrade">
			</iframe>
			<p class="amarilla-locations-attribution">
				<?php
				printf(
					/* translators: %s: link to openstreetmap.org */
					esc_html__( 'Mapa: %s', 'amarilla' ),
					'<a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">© OpenStreetMap</a>'
				);
				?>
			</p>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'amarilla_locations_map', 'amarilla_sc_locations_map' );
