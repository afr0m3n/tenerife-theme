/**
 * Amarilla Tenerife — Customizer Live Preview
 *
 * Doplňuje selective refresh o okamžitý postMessage update
 * jednoduchých textových polí (bez čekání na server round-trip).
 *
 * @package Amarilla_Tenerife
 * @since   1.1.0
 */
( function( $ ) {
	'use strict';

	/**
	 * Pomocná funkce: zaregistruje binding setting → DOM aktualizace
	 *
	 * @param {string}   settingId Identifikátor nastavení.
	 * @param {Function} callback  Funkce, která dostane novou hodnotu.
	 */
	function bind( settingId, callback ) {
		if ( typeof wp === 'undefined' || ! wp.customize ) {
			return;
		}
		wp.customize( settingId, function( value ) {
			value.bind( callback );
		} );
	}

	// --- HERO ---
	bind( 'amarilla_hero_eyebrow', function( newValue ) {
		$( '.amarilla-hero .amarilla-eyebrow' ).text( newValue );
	} );

	bind( 'amarilla_hero_title', function( newValue ) {
		// Zachováme případný accent <span> na konci, pokud existuje
		var $h1 = $( '.amarilla-hero h1' ).first();
		var $accent = $h1.find( '.amarilla-hero-accent' ).clone();
		$h1.text( newValue + ' ' );
		if ( $accent.length ) {
			$h1.append( $accent );
		}
	} );

	bind( 'amarilla_hero_title_accent', function( newValue ) {
		var $accent = $( '.amarilla-hero h1 .amarilla-hero-accent' );
		if ( $accent.length ) {
			$accent.text( newValue );
		}
	} );

	bind( 'amarilla_hero_lead', function( newValue ) {
		$( '.amarilla-hero-lead' ).text( newValue );
	} );

	bind( 'amarilla_hero_badge', function( newValue ) {
		$( '.amarilla-hero-badge' ).text( newValue );
	} );

	bind( 'amarilla_hero_stat_value', function( newValue ) {
		$( '.amarilla-hero-stat-value' ).text( newValue );
	} );

	bind( 'amarilla_hero_stat_label', function( newValue ) {
		$( '.amarilla-hero-stat-label' ).text( newValue );
	} );

	bind( 'amarilla_hero_btn1_text', function( newValue ) {
		$( '.amarilla-hero-cta-primary' ).text( newValue );
	} );

	bind( 'amarilla_hero_btn2_text', function( newValue ) {
		$( '.amarilla-hero-cta-secondary' ).text( newValue );
	} );

	// --- CTA SEKCE ---
	bind( 'amarilla_cta_lead', function( newValue ) {
		$( '.amarilla-cta-lead' ).text( newValue );
	} );

	bind( 'amarilla_cta_btn_text', function( newValue ) {
		$( '.amarilla-cta-button' ).text( newValue );
	} );

	// --- WHY US ---
	bind( 'amarilla_why_eyebrow', function( newValue ) {
		$( '.amarilla-why .amarilla-eyebrow' ).text( newValue );
	} );

	bind( 'amarilla_why_lead', function( newValue ) {
		$( '.amarilla-why-lead' ).text( newValue );
	} );

	// --- TIPS ---
	bind( 'amarilla_tips_eyebrow', function( newValue ) {
		$( '.amarilla-tips .amarilla-eyebrow' ).text( newValue );
	} );

	bind( 'amarilla_tips_lead', function( newValue ) {
		$( '.amarilla-tips-lead' ).text( newValue );
	} );

	// --- FOOTER ---
	bind( 'amarilla_footer_about', function( newValue ) {
		$( '.amarilla-footer-about p' ).text( newValue );
	} );

	bind( 'amarilla_footer_copyright', function( newValue ) {
		$( '.amarilla-footer-bottom span' ).first().text( newValue );
	} );

	bind( 'amarilla_footer_col1_title', function( newValue ) {
		$( '.amarilla-footer-col-1 h4' ).text( newValue );
	} );

	bind( 'amarilla_footer_col2_title', function( newValue ) {
		$( '.amarilla-footer-col-2 h4' ).text( newValue );
	} );

	// --- TOPBAR ---
	bind( 'amarilla_topbar_phone_display', function( newValue ) {
		$( '.amarilla-topbar-phone' ).text( newValue );
	} );

	bind( 'amarilla_topbar_location', function( newValue ) {
		$( '.amarilla-topbar-location' ).text( newValue );
	} );

	// --- IDENTITA / LOGO ---
	// Šířka loga (px) — okamžitá změna stylu
	bind( 'amarilla_logo_width', function( newValue ) {
		var width = parseInt( newValue, 10 );
		if ( ! isNaN( width ) && width > 0 ) {
			$( '.amarilla-logo img, .custom-logo' ).css( 'max-width', width + 'px' );
		}
	} );

} )( jQuery );
