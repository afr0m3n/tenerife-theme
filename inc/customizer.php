<?php
/**
 * Amarilla Tenerife — Customizer
 *
 * Kompletní sada uživatelských nastavení rozdělená do tematických panelů.
 * Vše se ovládá z `Vzhled → Přizpůsobit` (Customize) v administraci WordPressu.
 *
 * @package Amarilla
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Centrální registrace všech sekcí, settings a kontrolek
 */
function amarilla_customize_register( $wp_customize ) {

	/* ============================================================
	 * VLASTNÍ PANEL "AMARILLA"
	 * ============================================================ */
	$wp_customize->add_panel( 'amarilla_panel', array(
		'title'       => __( 'Amarilla — nastavení šablony', 'amarilla' ),
		'description' => __( 'Všechny obsahové bloky autopůjčovny pohromadě. Změny se ihned promítnou do živého náhledu.', 'amarilla' ),
		'priority'    => 30,
	) );

	/* ============================================================
	 * 1) IDENTITA (logo, claim, ikona)
	 * Stávající WP sekce "title_tagline" jen rozšíříme.
	 * ============================================================ */
	$wp_customize->add_setting( 'amarilla_logo_width', array(
		'default'           => amarilla_get_theme_default( 'amarilla_logo_width' ),
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'amarilla_logo_width', array(
		'label'       => __( 'Šířka loga v hlavičce (px)', 'amarilla' ),
		'description' => __( 'Doporučeno 120–220 px.', 'amarilla' ),
		'section'     => 'title_tagline',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 60, 'max' => 320, 'step' => 5 ),
	) );

	$wp_customize->add_setting( 'amarilla_logo_light', array(
		'default'           => amarilla_get_theme_default( 'amarilla_logo_light' ),
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'amarilla_logo_light', array(
		'label'       => __( 'Logo pro tmavé pozadí (patička)', 'amarilla' ),
		'description' => __( 'Pokud necháte prázdné, v patičce se použije název webu.', 'amarilla' ),
		'section'     => 'title_tagline',
	) ) );

	$wp_customize->add_setting( 'amarilla_show_text_logo_fallback', array(
		'default'           => amarilla_get_theme_default( 'amarilla_show_text_logo_fallback' ),
		'sanitize_callback' => 'amarilla_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'amarilla_show_text_logo_fallback', array(
		'label'       => __( 'Pokud není nahráno logo, zobrazit textovou variantu', 'amarilla' ),
		'description' => __( 'Žlutý kruh s písmenem + název webu.', 'amarilla' ),
		'section'     => 'title_tagline',
		'type'        => 'checkbox',
	) );

	/* ============================================================
	 * 2) HORNÍ LIŠTA (topbar)
	 * ============================================================ */
	$wp_customize->add_section( 'amarilla_topbar', array(
		'title'    => __( 'Horní lišta (topbar)', 'amarilla' ),
		'panel'    => 'amarilla_panel',
		'priority' => 10,
	) );

	$wp_customize->add_setting( 'amarilla_topbar_enabled', array(
		'default'           => amarilla_get_theme_default( 'amarilla_topbar_enabled' ),
		'sanitize_callback' => 'amarilla_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'amarilla_topbar_enabled', array(
		'label'   => __( 'Zobrazit horní tmavou lištu', 'amarilla' ),
		'section' => 'amarilla_topbar',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'amarilla_topbar_phone', array(
		'default'           => amarilla_get_theme_default( 'amarilla_topbar_phone' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_topbar_phone', array(
		'label'       => __( 'Telefon v topbaru', 'amarilla' ),
		'description' => __( 'Zobrazené číslo (např. +420 702 143 084).', 'amarilla' ),
		'section'     => 'amarilla_topbar',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_topbar_phone_link', array(
		'default'           => amarilla_get_theme_default( 'amarilla_topbar_phone_link' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_topbar_phone_link', array(
		'label'       => __( 'Telefon — formát pro proklik (tel:)', 'amarilla' ),
		'description' => __( 'Bez mezer a pomlček, např. +420702143084.', 'amarilla' ),
		'section'     => 'amarilla_topbar',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_topbar_location', array(
		'default'           => amarilla_get_theme_default( 'amarilla_topbar_location' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_topbar_location', array(
		'label'   => __( 'Lokalita v topbaru', 'amarilla' ),
		'section' => 'amarilla_topbar',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_topbar_show_langs', array(
		'default'           => amarilla_get_theme_default( 'amarilla_topbar_show_langs' ),
		'sanitize_callback' => 'amarilla_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'amarilla_topbar_show_langs', array(
		'label'       => __( 'Zobrazit přepínač jazyků', 'amarilla' ),
		'description' => __( 'Pokud je aktivní Polylang, použije se. Jinak statické zkratky CZ/EN/ES/DE.', 'amarilla' ),
		'section'     => 'amarilla_topbar',
		'type'        => 'checkbox',
	) );

	/* ============================================================
	 * 3) KONTAKTNÍ ÚDAJE (sdílené napříč šablonou)
	 * ============================================================ */
	$wp_customize->add_section( 'amarilla_contact', array(
		'title'       => __( 'Kontaktní údaje', 'amarilla' ),
		'description' => __( 'Údaje se používají v patičce, kontaktní stránce, schema.org a CTA blocích.', 'amarilla' ),
		'panel'       => 'amarilla_panel',
		'priority'    => 20,
	) );

	$wp_customize->add_setting( 'amarilla_phone', array(
		'default'           => amarilla_get_theme_default( 'amarilla_phone' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_phone', array(
		'label'   => __( 'Telefon (hlavní)', 'amarilla' ),
		'section' => 'amarilla_contact',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_email', array(
		'default'           => amarilla_get_theme_default( 'amarilla_email' ),
		'sanitize_callback' => 'sanitize_email',
	) );
	$wp_customize->add_control( 'amarilla_email', array(
		'label'   => __( 'E-mail', 'amarilla' ),
		'section' => 'amarilla_contact',
		'type'    => 'email',
	) );

	$wp_customize->add_setting( 'amarilla_address', array(
		'default'           => amarilla_get_theme_default( 'amarilla_address' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_address', array(
		'label'   => __( 'Adresa (jeden řádek)', 'amarilla' ),
		'section' => 'amarilla_contact',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_address_full', array(
		'default'           => amarilla_get_theme_default( 'amarilla_address_full' ),
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_address_full', array(
		'label'       => __( 'Adresa (víceřádková — pro kontaktní stránku)', 'amarilla' ),
		'description' => __( 'Každý řádek = nový řádek na webu.', 'amarilla' ),
		'section'     => 'amarilla_contact',
		'type'        => 'textarea',
	) );

	$wp_customize->add_setting( 'amarilla_hours', array(
		'default'           => amarilla_get_theme_default( 'amarilla_hours' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hours', array(
		'label'   => __( 'Provozní doba', 'amarilla' ),
		'section' => 'amarilla_contact',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_whatsapp', array(
		'default'           => amarilla_get_theme_default( 'amarilla_whatsapp' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_whatsapp', array(
		'label'       => __( 'WhatsApp', 'amarilla' ),
		'description' => __( 'Mezinárodní formát, např. +34611222333. Necháte-li prázdné, tlačítko se nezobrazí.', 'amarilla' ),
		'section'     => 'amarilla_contact',
		'type'        => 'text',
	) );

	/* ============================================================
	 * 4) HERO SEKCE
	 * ============================================================ */
	$wp_customize->add_section( 'amarilla_hero', array(
		'title'       => __( 'Hero — úvodní sekce', 'amarilla' ),
		'description' => __( 'Velký úvodní blok na hlavní stránce.', 'amarilla' ),
		'panel'       => 'amarilla_panel',
		'priority'    => 30,
	) );

	$wp_customize->add_setting( 'amarilla_hero_eyebrow', array(
		'default'           => amarilla_get_theme_default( 'amarilla_hero_eyebrow' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_eyebrow', array(
		'label'       => __( 'Popisek nad nadpisem', 'amarilla' ),
		'description' => __( 'Krátký text malými písmeny nad hlavním nadpisem.', 'amarilla' ),
		'section'     => 'amarilla_hero',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_hero_title', array(
		'default'           => amarilla_get_theme_default( 'amarilla_hero_title' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_title', array(
		'label'       => __( 'Hlavní nadpis', 'amarilla' ),
		'description' => __( 'Před zvýrazněným slovem. Pro nový řádek vložte "<br>".', 'amarilla' ),
		'section'     => 'amarilla_hero',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_hero_title_accent', array(
		'default'           => amarilla_get_theme_default( 'amarilla_hero_title_accent' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_title_accent', array(
		'label'       => __( 'Zvýrazněné slovo (kurzíva, korálová)', 'amarilla' ),
		'section'     => 'amarilla_hero',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_hero_lead', array(
		'default'           => amarilla_get_theme_default( 'amarilla_hero_lead' ),
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_lead', array(
		'label'   => __( 'Podnadpis / popis', 'amarilla' ),
		'section' => 'amarilla_hero',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'amarilla_hero_btn1_text', array(
		'default'           => amarilla_get_theme_default( 'amarilla_hero_btn1_text' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_btn1_text', array(
		'label'   => __( 'Tlačítko 1 — text', 'amarilla' ),
		'section' => 'amarilla_hero',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_hero_btn1_url', array(
		'default'           => amarilla_get_theme_default( 'amarilla_hero_btn1_url' ),
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'amarilla_hero_btn1_url', array(
		'label'   => __( 'Tlačítko 1 — odkaz', 'amarilla' ),
		'section' => 'amarilla_hero',
		'type'    => 'url',
	) );

	$wp_customize->add_setting( 'amarilla_hero_btn2_text', array(
		'default'           => amarilla_get_theme_default( 'amarilla_hero_btn2_text' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_btn2_text', array(
		'label'   => __( 'Tlačítko 2 — text', 'amarilla' ),
		'section' => 'amarilla_hero',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_hero_btn2_url', array(
		'default'           => amarilla_get_theme_default( 'amarilla_hero_btn2_url' ),
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'amarilla_hero_btn2_url', array(
		'label'   => __( 'Tlačítko 2 — odkaz', 'amarilla' ),
		'section' => 'amarilla_hero',
		'type'    => 'url',
	) );

	$wp_customize->add_setting( 'amarilla_hero_show_booking', array(
		'default'           => amarilla_get_theme_default( 'amarilla_hero_show_booking' ),
		'sanitize_callback' => 'amarilla_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'amarilla_hero_show_booking', array(
		'label'       => __( 'Zobrazit poptávkový widget v hero', 'amarilla' ),
		'description' => __( 'Compact 4-pole formulář (datum vyzvednutí → vrácení → lokalita → třída vozu). Po odeslání předvyplní velký formulář na /kontakt/.', 'amarilla' ),
		'section'     => 'amarilla_hero',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'amarilla_hero_image', array(
		'default'           => amarilla_get_theme_default( 'amarilla_hero_image' ),
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'amarilla_hero_image', array(
		'label'       => __( 'Hero obrázek', 'amarilla' ),
		'description' => __( 'Nahrajte obrázek na pravou stranu hero sekce.', 'amarilla' ),
		'section'     => 'amarilla_hero',
	) ) );

	$wp_customize->add_setting( 'amarilla_hero_badge', array(
		'default'           => amarilla_get_theme_default( 'amarilla_hero_badge' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_badge', array(
		'label'   => __( 'Štítek nad obrázkem (se zeleným bodem)', 'amarilla' ),
		'section' => 'amarilla_hero',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_hero_stat_value', array(
		'default'           => amarilla_get_theme_default( 'amarilla_hero_stat_value' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_stat_value', array(
		'label'   => __( 'Číslo / hodnota v dolním rohu', 'amarilla' ),
		'section' => 'amarilla_hero',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_hero_stat_label', array(
		'default'           => amarilla_get_theme_default( 'amarilla_hero_stat_label' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_stat_label', array(
		'label'   => __( 'Popisek pod hodnotou', 'amarilla' ),
		'section' => 'amarilla_hero',
		'type'    => 'text',
	) );

	/* ============================================================
	 * 5) PRUH DŮVĚRY (4 ikony)
	 * ============================================================ */
	$wp_customize->add_section( 'amarilla_trust', array(
		'title'       => __( 'Pruh důvěry (4 ikony)', 'amarilla' ),
		'description' => __( 'Tmavý pruh pod hero — 4 sloupce s ikonami a krátkými výhodami.', 'amarilla' ),
		'panel'       => 'amarilla_panel',
		'priority'    => 40,
	) );

	$wp_customize->add_setting( 'amarilla_trust_enabled', array(
		'default'           => amarilla_get_theme_default( 'amarilla_trust_enabled' ),
		'sanitize_callback' => 'amarilla_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'amarilla_trust_enabled', array(
		'label'   => __( 'Zobrazit pruh důvěry', 'amarilla' ),
		'section' => 'amarilla_trust',
		'type'    => 'checkbox',
	) );

	$trust_defaults = array(
		1 => array( amarilla_get_theme_default( 'amarilla_trust_1_icon' ), amarilla_get_theme_default( 'amarilla_trust_1_title' ), amarilla_get_theme_default( 'amarilla_trust_1_subtitle' ) ),
		2 => array( amarilla_get_theme_default( 'amarilla_trust_2_icon' ), amarilla_get_theme_default( 'amarilla_trust_2_title' ), amarilla_get_theme_default( 'amarilla_trust_2_subtitle' ) ),
		3 => array( amarilla_get_theme_default( 'amarilla_trust_3_icon' ), amarilla_get_theme_default( 'amarilla_trust_3_title' ), amarilla_get_theme_default( 'amarilla_trust_3_subtitle' ) ),
		4 => array( amarilla_get_theme_default( 'amarilla_trust_4_icon' ), amarilla_get_theme_default( 'amarilla_trust_4_title' ), amarilla_get_theme_default( 'amarilla_trust_4_subtitle' ) ),
	);
	$icon_choices = array(
		'check'    => __( '✓ Zaškrtnutí', 'amarilla' ),
		'shield'   => __( '⛨ Štít (pojištění)', 'amarilla' ),
		'clock'    => __( '◷ Hodiny (24/7)', 'amarilla' ),
		'location' => __( '⚲ Pin (lokace)', 'amarilla' ),
		'car'      => __( '🚗 Auto', 'amarilla' ),
		'key'      => __( '🔑 Klíč', 'amarilla' ),
		'star'     => __( '★ Hvězda', 'amarilla' ),
		'phone'    => __( '☏ Telefon', 'amarilla' ),
	);
	foreach ( $trust_defaults as $i => $defaults ) {
		$wp_customize->add_setting( "amarilla_trust_{$i}_icon", array(
			'default'           => $defaults[0],
			'sanitize_callback' => 'sanitize_key',
		) );
		$wp_customize->add_control( "amarilla_trust_{$i}_icon", array(
			'label'   => sprintf( __( 'Ikona %d', 'amarilla' ), $i ),
			'section' => 'amarilla_trust',
			'type'    => 'select',
			'choices' => $icon_choices,
		) );

		$wp_customize->add_setting( "amarilla_trust_{$i}_title", array(
			'default'           => $defaults[1],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "amarilla_trust_{$i}_title", array(
			'label'   => sprintf( __( 'Nadpis %d', 'amarilla' ), $i ),
			'section' => 'amarilla_trust',
			'type'    => 'text',
		) );

		$wp_customize->add_setting( "amarilla_trust_{$i}_subtitle", array(
			'default'           => $defaults[2],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "amarilla_trust_{$i}_subtitle", array(
			'label'   => sprintf( __( 'Podtitul %d', 'amarilla' ), $i ),
			'section' => 'amarilla_trust',
			'type'    => 'text',
		) );
	}

	/* ============================================================
	 * 6) PROČ SI VYBRAT NÁS
	 * ============================================================ */
	$wp_customize->add_section( 'amarilla_why', array(
		'title'    => __( 'Sekce "Proč si vybrat nás"', 'amarilla' ),
		'panel'    => 'amarilla_panel',
		'priority' => 50,
	) );

	$wp_customize->add_setting( 'amarilla_why_enabled', array(
		'default'           => amarilla_get_theme_default( 'amarilla_why_enabled' ),
		'sanitize_callback' => 'amarilla_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'amarilla_why_enabled', array(
		'label'   => __( 'Zobrazit sekci', 'amarilla' ),
		'section' => 'amarilla_why',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'amarilla_why_eyebrow', array(
		'default'           => amarilla_get_theme_default( 'amarilla_why_eyebrow' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_why_eyebrow', array(
		'label'   => __( 'Popisek nad nadpisem', 'amarilla' ),
		'section' => 'amarilla_why',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_why_title', array(
		'default'           => amarilla_get_theme_default( 'amarilla_why_title' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_why_title', array(
		'label'   => __( 'Nadpis (před zvýrazněným slovem)', 'amarilla' ),
		'section' => 'amarilla_why',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_why_title_accent', array(
		'default'           => amarilla_get_theme_default( 'amarilla_why_title_accent' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_why_title_accent', array(
		'label'   => __( 'Zvýrazněné slovo', 'amarilla' ),
		'section' => 'amarilla_why',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_why_lead', array(
		'default'           => amarilla_get_theme_default( 'amarilla_why_lead' ),
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_why_lead', array(
		'label'   => __( 'Popisný text', 'amarilla' ),
		'section' => 'amarilla_why',
		'type'    => 'textarea',
	) );

	$why_defaults = array(
		1 => array( amarilla_get_theme_default( 'amarilla_why_1_category' ), amarilla_get_theme_default( 'amarilla_why_1_title' ), amarilla_get_theme_default( 'amarilla_why_1_desc' ) ),
		2 => array( amarilla_get_theme_default( 'amarilla_why_2_category' ), amarilla_get_theme_default( 'amarilla_why_2_title' ), amarilla_get_theme_default( 'amarilla_why_2_desc' ) ),
		3 => array( amarilla_get_theme_default( 'amarilla_why_3_category' ), amarilla_get_theme_default( 'amarilla_why_3_title' ), amarilla_get_theme_default( 'amarilla_why_3_desc' ) ),
		4 => array( amarilla_get_theme_default( 'amarilla_why_4_category' ), amarilla_get_theme_default( 'amarilla_why_4_title' ), amarilla_get_theme_default( 'amarilla_why_4_desc' ) ),
	);
	foreach ( $why_defaults as $i => $defaults ) {
		$wp_customize->add_setting( "amarilla_why_{$i}_category", array(
			'default'           => $defaults[0],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "amarilla_why_{$i}_category", array(
			'label'   => sprintf( __( 'Kategorie %d (např. "Cena")', 'amarilla' ), $i ),
			'section' => 'amarilla_why',
			'type'    => 'text',
		) );

		$wp_customize->add_setting( "amarilla_why_{$i}_title", array(
			'default'           => $defaults[1],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "amarilla_why_{$i}_title", array(
			'label'   => sprintf( __( 'Nadpis výhody %d', 'amarilla' ), $i ),
			'section' => 'amarilla_why',
			'type'    => 'text',
		) );

		$wp_customize->add_setting( "amarilla_why_{$i}_desc", array(
			'default'           => $defaults[2],
			'sanitize_callback' => 'sanitize_textarea_field',
		) );
		$wp_customize->add_control( "amarilla_why_{$i}_desc", array(
			'label'   => sprintf( __( 'Popis výhody %d', 'amarilla' ), $i ),
			'section' => 'amarilla_why',
			'type'    => 'textarea',
		) );
	}

	/* ============================================================
	 * 7) TIPY Z TENERIFE
	 * ============================================================ */
	$wp_customize->add_section( 'amarilla_tips', array(
		'title'    => __( 'Sekce "Tipy z Tenerife"', 'amarilla' ),
		'panel'    => 'amarilla_panel',
		'priority' => 60,
	) );

	$wp_customize->add_setting( 'amarilla_tips_enabled', array(
		'default'           => amarilla_get_theme_default( 'amarilla_tips_enabled' ),
		'sanitize_callback' => 'amarilla_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'amarilla_tips_enabled', array(
		'label'   => __( 'Zobrazit sekci', 'amarilla' ),
		'section' => 'amarilla_tips',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'amarilla_tips_eyebrow', array(
		'default'           => amarilla_get_theme_default( 'amarilla_tips_eyebrow' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_tips_eyebrow', array(
		'label'   => __( 'Popisek', 'amarilla' ),
		'section' => 'amarilla_tips',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_tips_title', array(
		'default'           => amarilla_get_theme_default( 'amarilla_tips_title' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_tips_title', array(
		'label'   => __( 'Nadpis', 'amarilla' ),
		'section' => 'amarilla_tips',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_tips_lead', array(
		'default'           => amarilla_get_theme_default( 'amarilla_tips_lead' ),
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_tips_lead', array(
		'label'   => __( 'Popis', 'amarilla' ),
		'section' => 'amarilla_tips',
		'type'    => 'textarea',
	) );

	$tips_defaults = array(
		1 => array( amarilla_get_theme_default( 'amarilla_tip_1_tag' ), amarilla_get_theme_default( 'amarilla_tip_1_title' ), amarilla_get_theme_default( 'amarilla_tip_1_image' ) ),
		2 => array( amarilla_get_theme_default( 'amarilla_tip_2_tag' ), amarilla_get_theme_default( 'amarilla_tip_2_title' ), amarilla_get_theme_default( 'amarilla_tip_2_image' ) ),
		3 => array( amarilla_get_theme_default( 'amarilla_tip_3_tag' ), amarilla_get_theme_default( 'amarilla_tip_3_title' ), amarilla_get_theme_default( 'amarilla_tip_3_image' ) ),
	);
	foreach ( $tips_defaults as $i => $defaults ) {
		$wp_customize->add_setting( "amarilla_tip_{$i}_tag", array(
			'default'           => $defaults[0],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "amarilla_tip_{$i}_tag", array(
			'label'   => sprintf( __( 'Tip %d — štítek', 'amarilla' ), $i ),
			'section' => 'amarilla_tips',
			'type'    => 'text',
		) );

		$wp_customize->add_setting( "amarilla_tip_{$i}_title", array(
			'default'           => $defaults[1],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "amarilla_tip_{$i}_title", array(
			'label'   => sprintf( __( 'Tip %d — název', 'amarilla' ), $i ),
			'section' => 'amarilla_tips',
			'type'    => 'text',
		) );

		$wp_customize->add_setting( "amarilla_tip_{$i}_image", array(
			'default'           => $defaults[2],
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, "amarilla_tip_{$i}_image", array(
			'label'   => sprintf( __( 'Tip %d — obrázek', 'amarilla' ), $i ),
			'section' => 'amarilla_tips',
		) ) );

		$wp_customize->add_setting( "amarilla_tip_{$i}_url", array(
			'default'           => amarilla_get_theme_default( "amarilla_tip_{$i}_url" ),
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( "amarilla_tip_{$i}_url", array(
			'label'       => sprintf( __( 'Tip %d — odkaz', 'amarilla' ), $i ),
			'description' => __( 'Volitelné. Pokud necháte prázdné, karta nebude prokliknutelná.', 'amarilla' ),
			'section'     => "amarilla_tips",
			'type'        => 'url',
		) );
	}

	/* ============================================================
	 * 8) ZÁVĚREČNÉ CTA
	 * ============================================================ */
	$wp_customize->add_section( 'amarilla_cta', array(
		'title'    => __( 'Závěrečné CTA', 'amarilla' ),
		'panel'    => 'amarilla_panel',
		'priority' => 70,
	) );

	$wp_customize->add_setting( 'amarilla_cta_enabled', array(
		'default'           => amarilla_get_theme_default( 'amarilla_cta_enabled' ),
		'sanitize_callback' => 'amarilla_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'amarilla_cta_enabled', array(
		'label'   => __( 'Zobrazit sekci', 'amarilla' ),
		'section' => 'amarilla_cta',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'amarilla_cta_title', array(
		'default'           => amarilla_get_theme_default( 'amarilla_cta_title' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_cta_title', array(
		'label'   => __( 'Nadpis (před zvýrazněným slovem)', 'amarilla' ),
		'section' => 'amarilla_cta',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_cta_title_accent', array(
		'default'           => amarilla_get_theme_default( 'amarilla_cta_title_accent' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_cta_title_accent', array(
		'label'   => __( 'Zvýrazněné slovo', 'amarilla' ),
		'section' => 'amarilla_cta',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_cta_lead', array(
		'default'           => amarilla_get_theme_default( 'amarilla_cta_lead' ),
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_cta_lead', array(
		'label'   => __( 'Popis', 'amarilla' ),
		'section' => 'amarilla_cta',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'amarilla_cta_btn_text', array(
		'default'           => amarilla_get_theme_default( 'amarilla_cta_btn_text' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_cta_btn_text', array(
		'label'   => __( 'Text tlačítka', 'amarilla' ),
		'section' => 'amarilla_cta',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_cta_btn_url', array(
		'default'           => amarilla_get_theme_default( 'amarilla_cta_btn_url' ),
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'amarilla_cta_btn_url', array(
		'label'   => __( 'Odkaz tlačítka', 'amarilla' ),
		'section' => 'amarilla_cta',
		'type'    => 'url',
	) );

	/* ============================================================
	 * 9) PATIČKA
	 * ============================================================ */
	$wp_customize->add_section( 'amarilla_footer', array(
		'title'       => __( 'Patička', 'amarilla' ),
		'description' => __( 'Obsah patičky webu (čtyři sloupce).', 'amarilla' ),
		'panel'       => 'amarilla_panel',
		'priority'    => 80,
	) );

	$wp_customize->add_setting( 'amarilla_footer_about', array(
		'default'           => amarilla_get_theme_default( 'amarilla_footer_about' ),
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_footer_about', array(
		'label'   => __( 'Krátký popis pod logem', 'amarilla' ),
		'section' => 'amarilla_footer',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'amarilla_footer_col1_title', array(
		'default'           => amarilla_get_theme_default( 'amarilla_footer_col1_title' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_footer_col1_title', array(
		'label'   => __( 'Sloupec 1 — nadpis', 'amarilla' ),
		'section' => 'amarilla_footer',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_footer_col1_links', array(
		'default'           => amarilla_get_theme_default( 'amarilla_footer_col1_links' ),
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_footer_col1_links', array(
		'label'       => __( 'Sloupec 1 — odkazy', 'amarilla' ),
		'description' => __( 'Jeden odkaz na řádek ve formátu: Název|URL', 'amarilla' ),
		'section'     => 'amarilla_footer',
		'type'        => 'textarea',
	) );

	$wp_customize->add_setting( 'amarilla_footer_col2_title', array(
		'default'           => amarilla_get_theme_default( 'amarilla_footer_col2_title' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_footer_col2_title', array(
		'label'   => __( 'Sloupec 2 — nadpis', 'amarilla' ),
		'section' => 'amarilla_footer',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_footer_col2_links', array(
		'default'           => amarilla_get_theme_default( 'amarilla_footer_col2_links' ),
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_footer_col2_links', array(
		'label'       => __( 'Sloupec 2 — odkazy (Název|URL)', 'amarilla' ),
		'section'     => 'amarilla_footer',
		'type'        => 'textarea',
	) );

	$wp_customize->add_setting( 'amarilla_footer_copyright', array(
		'default'           => amarilla_get_theme_default( 'amarilla_footer_copyright' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_footer_copyright', array(
		'label'   => __( 'Copyright (spodní lišta)', 'amarilla' ),
		'section' => 'amarilla_footer',
		'type'    => 'text',
	) );

	/* ============================================================
	 * 10) SOCIÁLNÍ SÍTĚ
	 * ============================================================ */
	$wp_customize->add_section( 'amarilla_social', array(
		'title'       => __( 'Sociální sítě', 'amarilla' ),
		'description' => __( 'Odkazy se zobrazí v patičce. Necháte-li prázdné, ikona se nezobrazí.', 'amarilla' ),
		'panel'       => 'amarilla_panel',
		'priority'    => 90,
	) );

	$socials = array(
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'youtube'   => 'YouTube',
		'tripadvisor' => 'TripAdvisor',
	);
	foreach ( $socials as $key => $label ) {
		$wp_customize->add_setting( "amarilla_social_{$key}", array(
			'default'           => amarilla_get_theme_default( "amarilla_social_{$key}" ),
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( "amarilla_social_{$key}", array(
			'label'   => $label,
			'section' => 'amarilla_social',
			'type'    => 'url',
		) );
	}
}
add_action( 'customize_register', 'amarilla_customize_register' );

/**
 * Sanitizace zaškrtávacího políčka
 */
function amarilla_sanitize_checkbox( $value ) {
	return ( isset( $value ) && true === (bool) $value ) ? true : false;
}

/**
 * Obsah hero nadpisu pro selective refresh.
 */
function amarilla_customize_render_hero_title() {
	$title        = amarilla_get_theme_mod( 'amarilla_hero_title' );
	$title_accent = amarilla_get_theme_mod( 'amarilla_hero_title_accent' );
	$output       = wp_kses_post( $title );

	if ( $title_accent ) {
		$output .= '<br><em class="amarilla-hero-accent">' . esc_html( $title_accent ) . '</em>';
	}

	return $output;
}

/**
 * Vykreslí existující pattern jako HTML fragment pro selective refresh.
 */
function amarilla_customize_render_pattern( $pattern_file ) {
	$path = AMARILLA_DIR . '/patterns/' . $pattern_file;

	if ( ! file_exists( $path ) ) {
		return '';
	}

	ob_start();
	include $path;
	return ob_get_clean();
}

/**
 * Selective refresh callback: pruh důvěry.
 */
function amarilla_customize_render_trust_strip() {
	return amarilla_customize_render_pattern( 'trust-strip.php' );
}

/**
 * Selective refresh callback: sekce Proč si vybrat nás.
 */
function amarilla_customize_render_why_us() {
	return amarilla_customize_render_pattern( 'why-us.php' );
}

/**
 * Selective refresh callback: sekce Tipy z Tenerife.
 */
function amarilla_customize_render_tenerife_tips() {
	return amarilla_customize_render_pattern( 'tenerife-tips.php' );
}

/**
 * Selective refresh callback: sekce Kde nás najdete.
 */
function amarilla_customize_render_locations() {
	return amarilla_customize_render_pattern( 'locations-map.php' );
}

/**
 * Selective refresh callback: závěrečná CTA sekce.
 */
function amarilla_customize_render_cta_section() {
	return amarilla_customize_render_pattern( 'cta-section.php' );
}

/**
 * Selective refresh — aktualizace náhledu bez kompletního reloadu
 */
function amarilla_customize_partials( $wp_customize ) {
	if ( ! isset( $wp_customize->selective_refresh ) ) {
		return;
	}

	$partials = array(
		'amarilla_hero_title'       => array(
			'selector'        => '.amarilla-hero-title',
			'render_callback' => 'amarilla_customize_render_hero_title',
		),
		'amarilla_hero_lead'        => array(
			'selector' => '.amarilla-hero-lead',
		),
		'amarilla_hero_eyebrow'     => array(
			'selector' => '.amarilla-hero .amarilla-eyebrow',
		),
		'amarilla_footer_about'     => array(
			'selector' => '.amarilla-footer-about p',
		),
		'amarilla_footer_copyright' => array(
			'selector' => '.amarilla-footer-bottom span:first-child',
		),
	);

	foreach ( $partials as $setting => $partial ) {
		if ( $wp_customize->get_setting( $setting ) ) {
			$wp_customize->get_setting( $setting )->transport = 'postMessage';
			$wp_customize->selective_refresh->add_partial( $setting, array(
				'selector'        => $partial['selector'],
				'render_callback' => isset( $partial['render_callback'] ) ? $partial['render_callback'] : function() use ( $setting ) {
					return esc_html( amarilla_get_theme_mod( $setting ) );
				},
			) );
		}
	}

	$section_partials = array(
		'amarilla_trust_strip'   => array(
			'selector'        => '.amarilla-trust',
			'primary_setting' => 'amarilla_trust_1_title',
			'settings'        => array(
				'amarilla_trust_enabled',
				'amarilla_trust_1_icon',
				'amarilla_trust_1_title',
				'amarilla_trust_1_subtitle',
				'amarilla_trust_2_icon',
				'amarilla_trust_2_title',
				'amarilla_trust_2_subtitle',
				'amarilla_trust_3_icon',
				'amarilla_trust_3_title',
				'amarilla_trust_3_subtitle',
				'amarilla_trust_4_icon',
				'amarilla_trust_4_title',
				'amarilla_trust_4_subtitle',
			),
			'render_callback' => 'amarilla_customize_render_trust_strip',
		),
		'amarilla_why_us'        => array(
			'selector'        => '.amarilla-why',
			'primary_setting' => 'amarilla_why_title',
			'settings'        => array(
				'amarilla_why_enabled',
				'amarilla_why_eyebrow',
				'amarilla_why_title',
				'amarilla_why_title_accent',
				'amarilla_why_lead',
				'amarilla_why_1_category',
				'amarilla_why_1_title',
				'amarilla_why_1_desc',
				'amarilla_why_2_category',
				'amarilla_why_2_title',
				'amarilla_why_2_desc',
				'amarilla_why_3_category',
				'amarilla_why_3_title',
				'amarilla_why_3_desc',
				'amarilla_why_4_category',
				'amarilla_why_4_title',
				'amarilla_why_4_desc',
			),
			'render_callback' => 'amarilla_customize_render_why_us',
		),
		'amarilla_tenerife_tips' => array(
			'selector'        => '.amarilla-tips',
			'primary_setting' => 'amarilla_tips_title',
			'settings'        => array(
				'amarilla_tips_enabled',
				'amarilla_tips_eyebrow',
				'amarilla_tips_title',
				'amarilla_tips_lead',
				'amarilla_tip_1_tag',
				'amarilla_tip_1_title',
				'amarilla_tip_1_image',
				'amarilla_tip_1_url',
				'amarilla_tip_2_tag',
				'amarilla_tip_2_title',
				'amarilla_tip_2_image',
				'amarilla_tip_2_url',
				'amarilla_tip_3_tag',
				'amarilla_tip_3_title',
				'amarilla_tip_3_image',
				'amarilla_tip_3_url',
			),
			'render_callback' => 'amarilla_customize_render_tenerife_tips',
		),
		'amarilla_locations'     => array(
			'selector'        => '.amarilla-locations',
			'primary_setting' => 'amarilla_locations_title',
			'settings'        => array(
				'amarilla_locations_enabled',
				'amarilla_locations_eyebrow',
				'amarilla_locations_title',
				'amarilla_locations_lead',
				'amarilla_loc_1_name',
				'amarilla_loc_1_address',
				'amarilla_loc_1_hours',
				'amarilla_loc_1_lat',
				'amarilla_loc_1_lng',
				'amarilla_loc_2_name',
				'amarilla_loc_2_address',
				'amarilla_loc_2_hours',
				'amarilla_loc_2_lat',
				'amarilla_loc_2_lng',
				'amarilla_loc_3_name',
				'amarilla_loc_3_address',
				'amarilla_loc_3_hours',
				'amarilla_loc_3_lat',
				'amarilla_loc_3_lng',
				'amarilla_loc_4_name',
				'amarilla_loc_4_address',
				'amarilla_loc_4_hours',
				'amarilla_loc_4_lat',
				'amarilla_loc_4_lng',
				'amarilla_loc_5_name',
				'amarilla_loc_5_address',
				'amarilla_loc_5_hours',
				'amarilla_loc_5_lat',
				'amarilla_loc_5_lng',
				'amarilla_loc_6_name',
				'amarilla_loc_6_address',
				'amarilla_loc_6_hours',
				'amarilla_loc_6_lat',
				'amarilla_loc_6_lng',
			),
			'render_callback' => 'amarilla_customize_render_locations',
		),
		'amarilla_cta_section'   => array(
			'selector'        => '.amarilla-cta',
			'primary_setting' => 'amarilla_cta_title',
			'settings'        => array(
				'amarilla_cta_enabled',
				'amarilla_cta_title',
				'amarilla_cta_title_accent',
				'amarilla_cta_lead',
				'amarilla_cta_btn_text',
				'amarilla_cta_btn_url',
			),
			'render_callback' => 'amarilla_customize_render_cta_section',
		),
	);

	foreach ( $section_partials as $partial_id => $partial ) {
		$settings = array_values( array_filter( $partial['settings'], array( $wp_customize, 'get_setting' ) ) );

		if ( empty( $settings ) ) {
			continue;
		}

		foreach ( $settings as $setting ) {
			$wp_customize->get_setting( $setting )->transport = 'postMessage';
		}

		$partial_args = array(
			'selector'            => $partial['selector'],
			'settings'            => $settings,
			'render_callback'     => $partial['render_callback'],
			'container_inclusive' => true,
			'fallback_refresh'    => true,
		);

		if ( isset( $partial['primary_setting'] ) ) {
			$partial_args['primary_setting'] = $partial['primary_setting'];
		}

		$wp_customize->selective_refresh->add_partial( $partial_id, $partial_args );
	}
}
add_action( 'customize_register', 'amarilla_customize_partials', 35 );

/**
 * Skript pro postMessage transport v náhledu
 */
function amarilla_customize_preview_js() {
	wp_enqueue_script(
		'amarilla-customize-preview',
		AMARILLA_URI . '/assets/js/customize-preview.js',
		array( 'customize-preview', 'jquery' ),
		AMARILLA_VERSION,
		true
	);
}
add_action( 'customize_preview_init', 'amarilla_customize_preview_js' );

/**
 * Preview-only pozice edit shortcutů pro full-width sekce.
 */
function amarilla_customize_preview_shortcut_styles() {
	$css = '
		.amarilla-trust > .customize-partial-edit-shortcut,
		.amarilla-why > .customize-partial-edit-shortcut,
		.amarilla-tips > .customize-partial-edit-shortcut,
		.amarilla-locations > .customize-partial-edit-shortcut,
		.amarilla-cta > .customize-partial-edit-shortcut {
			z-index: 100000;
		}
		.amarilla-trust > .customize-partial-edit-shortcut button,
		.amarilla-why > .customize-partial-edit-shortcut button,
		.amarilla-tips > .customize-partial-edit-shortcut button,
		.amarilla-locations > .customize-partial-edit-shortcut button,
		.amarilla-cta > .customize-partial-edit-shortcut button {
			left: 12px;
			top: 12px;
			z-index: 100001;
		}
	';

	wp_register_style( 'amarilla-customize-preview-shortcuts', false, array(), AMARILLA_VERSION );
	wp_enqueue_style( 'amarilla-customize-preview-shortcuts' );
	wp_add_inline_style( 'amarilla-customize-preview-shortcuts', $css );
}
add_action( 'customize_preview_init', 'amarilla_customize_preview_shortcut_styles' );
