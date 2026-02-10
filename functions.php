<?php
/**
 * Reggio Calabria Hub - Theme Functions
 *
 * @package ReggioHub
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'REGGIO_VERSION', '1.0.0' );
define( 'REGGIO_DIR', get_template_directory() );
define( 'REGGIO_URI', get_template_directory_uri() );

/**
 * Theme setup
 */
function reggio_setup() {
	// Load text domain for translations
	load_theme_textdomain( 'reggio-hub', REGGIO_DIR . '/languages' );

	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', array(
		'height'      => 60,
		'width'       => 200,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );

	add_editor_style( 'assets/css/editor.css' );

	register_nav_menus( array(
		'primary'   => __( 'Primary Menu', 'reggio-hub' ),
		'footer'    => __( 'Footer Menu', 'reggio-hub' ),
	) );

	add_image_size( 'reggio-card', 600, 400, true );
	add_image_size( 'reggio-hero', 1920, 800, true );
	add_image_size( 'reggio-thumbnail', 300, 200, true );
}
add_action( 'after_setup_theme', 'reggio_setup' );

/**
 * Enqueue styles
 */
function reggio_enqueue_assets() {
	wp_enqueue_style(
		'reggio-style',
		get_stylesheet_uri(),
		array(),
		REGGIO_VERSION
	);

	wp_enqueue_style(
		'reggio-theme',
		REGGIO_URI . '/assets/css/theme.css',
		array(),
		REGGIO_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'reggio_enqueue_assets' );

/**
 * Register block pattern categories
 */
function reggio_register_pattern_categories() {
	register_block_pattern_category( 'reggio-hero', array(
		'label' => __( 'Reggio - Hero', 'reggio-hub' ),
	) );

	register_block_pattern_category( 'reggio-cards', array(
		'label' => __( 'Reggio - Cards', 'reggio-hub' ),
	) );

	register_block_pattern_category( 'reggio-cta', array(
		'label' => __( 'Reggio - Call to Action', 'reggio-hub' ),
	) );

	register_block_pattern_category( 'reggio-content', array(
		'label' => __( 'Reggio - Content', 'reggio-hub' ),
	) );
}
add_action( 'init', 'reggio_register_pattern_categories' );

/**
 * Register custom block styles
 */
function reggio_register_block_styles() {
	register_block_style( 'core/group', array(
		'name'  => 'reggio-card',
		'label' => __( 'Card', 'reggio-hub' ),
	) );

	register_block_style( 'core/group', array(
		'name'  => 'reggio-card-hover',
		'label' => __( 'Card with Hover', 'reggio-hub' ),
	) );

	register_block_style( 'core/image', array(
		'name'  => 'reggio-rounded',
		'label' => __( 'Rounded', 'reggio-hub' ),
	) );

	register_block_style( 'core/separator', array(
		'name'  => 'reggio-ornament',
		'label' => __( 'Ornament', 'reggio-hub' ),
	) );
}
add_action( 'init', 'reggio_register_block_styles' );

/**
 * Custom excerpt length
 */
function reggio_excerpt_length( $length ) {
	return 25;
}
add_filter( 'excerpt_length', 'reggio_excerpt_length' );

/**
 * Custom excerpt more
 */
function reggio_excerpt_more( $more ) {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'reggio_excerpt_more' );

/**
 * Add hreflang tags for multilingual SEO
 * Works with Polylang - outputs hreflang link tags in <head>
 */
function reggio_add_hreflang_tags() {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return;
	}

	$translations = pll_the_languages( array(
		'raw'          => 1,
		'hide_current' => 0,
	) );

	if ( empty( $translations ) ) {
		return;
	}

	foreach ( $translations as $lang ) {
		$locale = str_replace( '_', '-', $lang['locale'] );
		printf(
			'<link rel="alternate" hreflang="%s" href="%s" />' . "\n",
			esc_attr( $locale ),
			esc_url( $lang['url'] )
		);
	}

	// x-default points to English version
	if ( isset( $translations['en'] ) ) {
		printf(
			'<link rel="alternate" hreflang="x-default" href="%s" />' . "\n",
			esc_url( $translations['en']['url'] )
		);
	}
}
add_action( 'wp_head', 'reggio_add_hreflang_tags' );

/**
 * Redirect root URL to default language
 * Only if Polylang doesn't handle it already
 */
function reggio_root_redirect() {
	if ( function_exists( 'pll_default_language' ) ) {
		return; // Polylang handles redirects
	}

	// Fallback: if no multilingual plugin, redirect root to /en/
	if ( $_SERVER['REQUEST_URI'] === '/' ) {
		wp_redirect( home_url( '/en/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'reggio_root_redirect' );
