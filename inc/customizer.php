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
		'default'           => 160,
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
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'amarilla_logo_light', array(
		'label'       => __( 'Logo pro tmavé pozadí (patička)', 'amarilla' ),
		'description' => __( 'Pokud necháte prázdné, v patičce se použije název webu.', 'amarilla' ),
		'section'     => 'title_tagline',
	) ) );

	$wp_customize->add_setting( 'amarilla_show_text_logo_fallback', array(
		'default'           => true,
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
		'default'           => true,
		'sanitize_callback' => 'amarilla_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'amarilla_topbar_enabled', array(
		'label'   => __( 'Zobrazit horní tmavou lištu', 'amarilla' ),
		'section' => 'amarilla_topbar',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'amarilla_topbar_phone', array(
		'default'           => '+420 702 143 084',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_topbar_phone', array(
		'label'       => __( 'Telefon v topbaru', 'amarilla' ),
		'description' => __( 'Zobrazené číslo (např. +420 702 143 084).', 'amarilla' ),
		'section'     => 'amarilla_topbar',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_topbar_phone_link', array(
		'default'           => '+420702143084',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_topbar_phone_link', array(
		'label'       => __( 'Telefon — formát pro proklik (tel:)', 'amarilla' ),
		'description' => __( 'Bez mezer a pomlček, např. +420702143084.', 'amarilla' ),
		'section'     => 'amarilla_topbar',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_topbar_location', array(
		'default'           => 'Letiště Tenerife Sur (TFS)',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_topbar_location', array(
		'label'   => __( 'Lokalita v topbaru', 'amarilla' ),
		'section' => 'amarilla_topbar',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_topbar_show_langs', array(
		'default'           => true,
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
		'default'           => '+34 922 000 000',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_phone', array(
		'label'   => __( 'Telefon (hlavní)', 'amarilla' ),
		'section' => 'amarilla_contact',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_email', array(
		'default'           => 'info@autopujcovna-tenerife.cz',
		'sanitize_callback' => 'sanitize_email',
	) );
	$wp_customize->add_control( 'amarilla_email', array(
		'label'   => __( 'E-mail', 'amarilla' ),
		'section' => 'amarilla_contact',
		'type'    => 'email',
	) );

	$wp_customize->add_setting( 'amarilla_address', array(
		'default'           => 'Letiště Tenerife Sur, Avenida Bruselas, 38660',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_address', array(
		'label'   => __( 'Adresa (jeden řádek)', 'amarilla' ),
		'section' => 'amarilla_contact',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_address_full', array(
		'default'           => "Letiště Tenerife Sur (TFS)\nAvenida Bruselas, 38660\nAdeje, Santa Cruz de Tenerife",
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_address_full', array(
		'label'       => __( 'Adresa (víceřádková — pro kontaktní stránku)', 'amarilla' ),
		'description' => __( 'Každý řádek = nový řádek na webu.', 'amarilla' ),
		'section'     => 'amarilla_contact',
		'type'        => 'textarea',
	) );

	$wp_customize->add_setting( 'amarilla_hours', array(
		'default'           => 'Po–Ne, 7:00–23:00',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hours', array(
		'label'   => __( 'Provozní doba', 'amarilla' ),
		'section' => 'amarilla_contact',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_whatsapp', array(
		'default'           => '',
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
		'default'           => 'Autopůjčovna na Tenerife',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_eyebrow', array(
		'label'       => __( 'Popisek nad nadpisem', 'amarilla' ),
		'description' => __( 'Krátký text malými písmeny nad hlavním nadpisem.', 'amarilla' ),
		'section'     => 'amarilla_hero',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_hero_title', array(
		'default'           => 'Tenerife po vašem',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_title', array(
		'label'       => __( 'Hlavní nadpis', 'amarilla' ),
		'description' => __( 'Před zvýrazněným slovem. Pro nový řádek vložte "<br>".', 'amarilla' ),
		'section'     => 'amarilla_hero',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_hero_title_accent', array(
		'default'           => 'rytmu.',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_title_accent', array(
		'label'       => __( 'Zvýrazněné slovo (kurzíva, korálová)', 'amarilla' ),
		'section'     => 'amarilla_hero',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_hero_lead', array(
		'default'           => 'Půjčte si auto bez starostí. Vyzvednutí přímo na letišti, plné pojištění a žádné skryté poplatky. Místní tým, který ostrov zná.',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_lead', array(
		'label'   => __( 'Podnadpis / popis', 'amarilla' ),
		'section' => 'amarilla_hero',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'amarilla_hero_btn1_text', array(
		'default'           => 'Prohlédnout vozy',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_btn1_text', array(
		'label'   => __( 'Tlačítko 1 — text', 'amarilla' ),
		'section' => 'amarilla_hero',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_hero_btn1_url', array(
		'default'           => '/vozovy-park/',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'amarilla_hero_btn1_url', array(
		'label'   => __( 'Tlačítko 1 — odkaz', 'amarilla' ),
		'section' => 'amarilla_hero',
		'type'    => 'url',
	) );

	$wp_customize->add_setting( 'amarilla_hero_btn2_text', array(
		'default'           => 'Jak to funguje',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_btn2_text', array(
		'label'   => __( 'Tlačítko 2 — text', 'amarilla' ),
		'section' => 'amarilla_hero',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_hero_btn2_url', array(
		'default'           => '/kontakt/',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'amarilla_hero_btn2_url', array(
		'label'   => __( 'Tlačítko 2 — odkaz', 'amarilla' ),
		'section' => 'amarilla_hero',
		'type'    => 'url',
	) );

	$wp_customize->add_setting( 'amarilla_hero_show_booking', array(
		'default'           => true,
		'sanitize_callback' => 'amarilla_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'amarilla_hero_show_booking', array(
		'label'       => __( 'Zobrazit poptávkový widget v hero', 'amarilla' ),
		'description' => __( 'Compact 4-pole formulář (datum vyzvednutí → vrácení → lokalita → třída vozu). Po odeslání předvyplní velký formulář na /kontakt/.', 'amarilla' ),
		'section'     => 'amarilla_hero',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'amarilla_hero_image', array(
		'default'           => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?auto=format&fit=crop&w=900&q=80',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'amarilla_hero_image', array(
		'label'       => __( 'Hero obrázek', 'amarilla' ),
		'description' => __( 'Nahrajte obrázek na pravou stranu hero sekce.', 'amarilla' ),
		'section'     => 'amarilla_hero',
	) ) );

	$wp_customize->add_setting( 'amarilla_hero_badge', array(
		'default'           => 'K vyzvednutí dnes',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_badge', array(
		'label'   => __( 'Štítek nad obrázkem (se zeleným bodem)', 'amarilla' ),
		'section' => 'amarilla_hero',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_hero_stat_value', array(
		'default'           => 'od 25 €',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_hero_stat_value', array(
		'label'   => __( 'Číslo / hodnota v dolním rohu', 'amarilla' ),
		'section' => 'amarilla_hero',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_hero_stat_label', array(
		'default'           => 'Cena za den',
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
		'default'           => true,
		'sanitize_callback' => 'amarilla_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'amarilla_trust_enabled', array(
		'label'   => __( 'Zobrazit pruh důvěry', 'amarilla' ),
		'section' => 'amarilla_trust',
		'type'    => 'checkbox',
	) );

	$trust_defaults = array(
		1 => array( 'check',    'Bez depozitu',         'Žádná blokace na kartě' ),
		2 => array( 'shield',   'Plné pojištění',       'V ceně všech vozů' ),
		3 => array( 'clock',    '24/7 podpora',         'Česky, anglicky, španělsky' ),
		4 => array( 'location', 'Vyzvednutí na letišti', 'TFS i TFN, zdarma' ),
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
		'default'           => true,
		'sanitize_callback' => 'amarilla_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'amarilla_why_enabled', array(
		'label'   => __( 'Zobrazit sekci', 'amarilla' ),
		'section' => 'amarilla_why',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'amarilla_why_eyebrow', array(
		'default'           => 'Proč si vybrat nás',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_why_eyebrow', array(
		'label'   => __( 'Popisek nad nadpisem', 'amarilla' ),
		'section' => 'amarilla_why',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_why_title', array(
		'default'           => 'Místní tým, který ostrov',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_why_title', array(
		'label'   => __( 'Nadpis (před zvýrazněným slovem)', 'amarilla' ),
		'section' => 'amarilla_why',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_why_title_accent', array(
		'default'           => 'zná.',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_why_title_accent', array(
		'label'   => __( 'Zvýrazněné slovo', 'amarilla' ),
		'section' => 'amarilla_why',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_why_lead', array(
		'default'           => 'Auto si nepůjčujete jenom kvůli kolům. Půjčujete si svobodu objevovat Tenerife podle sebe. Postaráme se o vše ostatní.',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_why_lead', array(
		'label'   => __( 'Popisný text', 'amarilla' ),
		'section' => 'amarilla_why',
		'type'    => 'textarea',
	) );

	$why_defaults = array(
		1 => array( 'Cena',    'Žádné skryté poplatky',     'Cena, kterou vidíte, je cena, kterou zaplatíte. Pojištění, neomezené kilometry i druhý řidič v ceně.' ),
		2 => array( 'Servis',  'Auta v perfektním stavu',   'Pravidelná údržba a kontroly. Většina vozů je mladší tří let.' ),
		3 => array( 'Lidé',    'Mluvíme česky',             'Český servis přímo na ostrově. Komunikace, papírování i podpora bez jazykových bariér.' ),
		4 => array( 'Volnost', 'Jste v plánu',              'Bezplatné storno do 48 hodin před vyzvednutím. Změna termínu kdykoli.' ),
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
		'default'           => true,
		'sanitize_callback' => 'amarilla_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'amarilla_tips_enabled', array(
		'label'   => __( 'Zobrazit sekci', 'amarilla' ),
		'section' => 'amarilla_tips',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'amarilla_tips_eyebrow', array(
		'default'           => 'Tipy z ostrova',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_tips_eyebrow', array(
		'label'   => __( 'Popisek', 'amarilla' ),
		'section' => 'amarilla_tips',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_tips_title', array(
		'default'           => 'Tenerife, které stojí za to.',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_tips_title', array(
		'label'   => __( 'Nadpis', 'amarilla' ),
		'section' => 'amarilla_tips',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_tips_lead', array(
		'default'           => 'Co navštívit s autem? Pár míst, kde to skutečně žije, sepsaných od lidí, co tady bydlí.',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_tips_lead', array(
		'label'   => __( 'Popis', 'amarilla' ),
		'section' => 'amarilla_tips',
		'type'    => 'textarea',
	) );

	$tips_defaults = array(
		1 => array( 'Příroda', 'Národní park Teide za úsvitu', 'https://images.unsplash.com/photo-1583425423320-1f1f0c8b4a3a?auto=format&fit=crop&w=900&q=80' ),
		2 => array( 'Trasy',   'Pohoří Anaga',                  'https://images.unsplash.com/photo-1571893544028-06b07af6dade?auto=format&fit=crop&w=700&q=80' ),
		3 => array( 'Vesnice', 'Masca a Garachico',             'https://images.unsplash.com/photo-1535914254981-b5012eebbd15?auto=format&fit=crop&w=700&q=80' ),
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
			'default'           => '',
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
		'default'           => true,
		'sanitize_callback' => 'amarilla_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'amarilla_cta_enabled', array(
		'label'   => __( 'Zobrazit sekci', 'amarilla' ),
		'section' => 'amarilla_cta',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'amarilla_cta_title', array(
		'default'           => 'Připraveni vyrazit na',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_cta_title', array(
		'label'   => __( 'Nadpis (před zvýrazněným slovem)', 'amarilla' ),
		'section' => 'amarilla_cta',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_cta_title_accent', array(
		'default'           => 'cestu?',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_cta_title_accent', array(
		'label'   => __( 'Zvýrazněné slovo', 'amarilla' ),
		'section' => 'amarilla_cta',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_cta_lead', array(
		'default'           => 'Napište nám termín a my připravíme nabídku na míru. Odpovídáme do hodiny.',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_cta_lead', array(
		'label'   => __( 'Popis', 'amarilla' ),
		'section' => 'amarilla_cta',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'amarilla_cta_btn_text', array(
		'default'           => 'Poptat termín',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_cta_btn_text', array(
		'label'   => __( 'Text tlačítka', 'amarilla' ),
		'section' => 'amarilla_cta',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_cta_btn_url', array(
		'default'           => '/kontakt/',
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
		'default'           => 'Autopůjčovna provozovaná místními. Tenerife pro vás.',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_footer_about', array(
		'label'   => __( 'Krátký popis pod logem', 'amarilla' ),
		'section' => 'amarilla_footer',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'amarilla_footer_col1_title', array(
		'default'           => 'Stránky',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_footer_col1_title', array(
		'label'   => __( 'Sloupec 1 — nadpis', 'amarilla' ),
		'section' => 'amarilla_footer',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_footer_col1_links', array(
		'default'           => "Domů|/\nVozový park|/vozovy-park/\nSlužby|/sluzby/\nO nás|/o-nas/\nKontakt|/kontakt/",
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_footer_col1_links', array(
		'label'       => __( 'Sloupec 1 — odkazy', 'amarilla' ),
		'description' => __( 'Jeden odkaz na řádek ve formátu: Název|URL', 'amarilla' ),
		'section'     => 'amarilla_footer',
		'type'        => 'textarea',
	) );

	$wp_customize->add_setting( 'amarilla_footer_col2_title', array(
		'default'           => 'Pomoc',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_footer_col2_title', array(
		'label'   => __( 'Sloupec 2 — nadpis', 'amarilla' ),
		'section' => 'amarilla_footer',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_footer_col2_links', array(
		'default'           => "Časté dotazy|/faq/\nPojištění|/pojisteni/\nStorno podmínky|/storno-podminky/\nObchodní podmínky|/obchodni-podminky/",
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_footer_col2_links', array(
		'label'       => __( 'Sloupec 2 — odkazy (Název|URL)', 'amarilla' ),
		'section'     => 'amarilla_footer',
		'type'        => 'textarea',
	) );

	$wp_customize->add_setting( 'amarilla_footer_copyright', array(
		'default'           => '© ' . date( 'Y' ) . ' Amarilla Car Hire. Všechna práva vyhrazena.',
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
			'default'           => '',
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
 * Selective refresh — aktualizace náhledu bez kompletního reloadu
 */
function amarilla_customize_partials( $wp_customize ) {
	if ( ! isset( $wp_customize->selective_refresh ) ) {
		return;
	}

	$partials = array(
		'amarilla_hero_title'      => '.amarilla-hero h1',
		'amarilla_hero_lead'       => '.amarilla-hero-lead',
		'amarilla_hero_eyebrow'    => '.amarilla-hero .amarilla-eyebrow',
		'amarilla_footer_about'    => '.amarilla-footer-about p',
		'amarilla_footer_copyright' => '.amarilla-footer-bottom span:first-child',
	);

	foreach ( $partials as $setting => $selector ) {
		if ( $wp_customize->get_setting( $setting ) ) {
			$wp_customize->get_setting( $setting )->transport = 'postMessage';
			$wp_customize->selective_refresh->add_partial( $setting, array(
				'selector'        => $selector,
				'render_callback' => function() use ( $setting ) {
					return get_theme_mod( $setting );
				},
			) );
		}
	}
}
add_action( 'customize_register', 'amarilla_customize_partials', 20 );

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
