<?php
/**
 * Amarilla Tenerife — blog ("Tipy z Tenerife")
 *
 * Volné rozšíření existující sekce Tips. Toto je plnohodnotný blog
 * (post type `post`) pro SEO: dlouhé články o výletech, tipy, novinky.
 *
 * Modul řeší:
 *   - Customizer pro hlavičku archivní stránky (eyebrow / title / lead)
 *   - Reading time (odhad) jako shortcode
 *   - Sourozenecký výpis článků pro single page (related posts)
 *   - Helper pro výchozí slug stránky blogu (`/blog/`)
 *
 * Šablony:
 *   - templates/home.html  → archiv blogu (pokud je v nastavení čtení
 *     vybrána statická titulka + samostatná stránka pro příspěvky)
 *   - templates/single.html → samostatný článek
 *   - templates/archive.html → kategorie, autoři, datumy
 *
 * @package Amarilla
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
 * Customizer panel
 * ============================================================ */
function amarilla_customize_blog( $wp_customize ) {
	if ( ! $wp_customize->get_panel( 'amarilla_panel' ) ) {
		return;
	}
	$wp_customize->add_section( 'amarilla_blog', array(
		'title'       => __( 'Blog — Tipy z Tenerife', 'amarilla' ),
		'description' => __( 'Hlavička archivní stránky blogu. Pro plnohodnotný blog vytvořte v Nastavení → Čtení statickou hlavní stránku a samostatnou stránku „Blog" pro příspěvky.', 'amarilla' ),
		'panel'       => 'amarilla_panel',
		'priority'    => 95,
	) );

	$wp_customize->add_setting( 'amarilla_blog_eyebrow', array(
		'default'           => 'Tipy z Tenerife',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_blog_eyebrow', array(
		'label'   => __( 'Popisek (nad nadpisem)', 'amarilla' ),
		'section' => 'amarilla_blog',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_blog_title', array(
		'default'           => 'Co dělat na ostrově, když máte volant.',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'amarilla_blog_title', array(
		'label'   => __( 'Nadpis archivu', 'amarilla' ),
		'section' => 'amarilla_blog',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'amarilla_blog_lead', array(
		'default'           => 'Trasy, vyhlídky a praktické tipy od místních. Pište nám i své otázky — rádi odpovíme přímo v článku.',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'amarilla_blog_lead', array(
		'label'   => __( 'Popis archivu', 'amarilla' ),
		'section' => 'amarilla_blog',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'amarilla_blog_show_related', array(
		'default'           => true,
		'sanitize_callback' => 'amarilla_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'amarilla_blog_show_related', array(
		'label'   => __( 'Pod článkem zobrazit související články', 'amarilla' ),
		'section' => 'amarilla_blog',
		'type'    => 'checkbox',
	) );
}
add_action( 'customize_register', 'amarilla_customize_blog', 40 );

/* ============================================================
 * Reading time — shortcode [amarilla_reading_time]
 * ============================================================ */
function amarilla_sc_reading_time() {
	$post = get_post();
	if ( ! $post ) {
		return '';
	}
	$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );
	$minutes    = max( 1, (int) ceil( $word_count / 200 ) );
	return sprintf(
		'<span class="amarilla-reading-time"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> %s</span>',
		esc_html( sprintf( _n( '%d min čtení', '%d min čtení', $minutes, 'amarilla' ), $minutes ) )
	);
}
add_shortcode( 'amarilla_reading_time', 'amarilla_sc_reading_time' );

/* ============================================================
 * Header archivu blogu — shortcode (renderuje hero pro index/home)
 * ============================================================ */
function amarilla_sc_blog_header() {
	$eyebrow = get_theme_mod( 'amarilla_blog_eyebrow', 'Tipy z Tenerife' );
	$title   = get_theme_mod( 'amarilla_blog_title',   'Co dělat na ostrově, když máte volant.' );
	$lead    = get_theme_mod( 'amarilla_blog_lead',    'Trasy, vyhlídky a praktické tipy od místních.' );

	ob_start();
	?>
	<header class="amarilla-blog-header">
		<div class="amarilla-container">
			<?php if ( $eyebrow ) : ?>
				<div class="amarilla-eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
			<?php endif; ?>
			<h1><?php echo esc_html( $title ); ?></h1>
			<?php if ( $lead ) : ?>
				<p class="amarilla-blog-lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>
		</div>
	</header>
	<?php
	return ob_get_clean();
}
add_shortcode( 'amarilla_blog_header', 'amarilla_sc_blog_header' );

/* ============================================================
 * Související články — shortcode [amarilla_related_posts]
 * Vybírá 3 nejnovější příspěvky ze stejných kategorií,
 * fallback: 3 nejnovější obecně.
 * ============================================================ */
function amarilla_sc_related_posts() {
	$post = get_post();
	if ( ! $post || $post->post_type !== 'post' ) {
		return '';
	}
	if ( ! get_theme_mod( 'amarilla_blog_show_related', true ) ) {
		return '';
	}

	$cats     = wp_get_post_categories( $post->ID );
	$args     = array(
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'post__not_in'   => array( $post->ID ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	if ( $cats ) {
		$args['category__in'] = $cats;
	}
	$query = new WP_Query( $args );
	if ( ! $query->have_posts() ) {
		return '';
	}

	ob_start();
	?>
	<section class="amarilla-related-posts">
		<div class="amarilla-container">
			<h2><?php esc_html_e( 'Mohlo by vás zajímat', 'amarilla' ); ?></h2>
			<div class="amarilla-related-posts-grid">
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
					<article class="amarilla-post-card">
						<a class="amarilla-post-card-link" href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="amarilla-post-card-image">
									<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'alt' => esc_attr( get_the_title() ) ) ); ?>
								</div>
							<?php endif; ?>
							<div class="amarilla-post-card-body">
								<?php
								$post_cats = get_the_category();
								if ( $post_cats ) : ?>
									<div class="amarilla-post-card-cat"><?php echo esc_html( $post_cats[0]->name ); ?></div>
								<?php endif; ?>
								<h3><?php the_title(); ?></h3>
								<div class="amarilla-post-card-meta">
									<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
								</div>
							</div>
						</a>
					</article>
				<?php endwhile; ?>
			</div>
		</div>
	</section>
	<?php
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'amarilla_related_posts', 'amarilla_sc_related_posts' );
