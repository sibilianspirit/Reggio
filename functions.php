<?php
/**
 * Best of Calabria - Theme Functions
 *
 * @package BestOfCalabria
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BOC_VERSION', '2.0.0' );
define( 'BOC_DIR', get_template_directory() );
define( 'BOC_URI', get_template_directory_uri() );

/**
 * Theme setup
 */
function boc_setup() {
	load_theme_textdomain( 'best-of-calabria', BOC_DIR . '/languages' );

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
		'primary' => __( 'Primary Menu', 'best-of-calabria' ),
		'footer'  => __( 'Footer Menu', 'best-of-calabria' ),
	) );

	add_image_size( 'boc-card', 600, 400, true );
	add_image_size( 'boc-hero', 1920, 800, true );
	add_image_size( 'boc-thumbnail', 300, 200, true );
	add_image_size( 'boc-destination', 800, 600, true );
}
add_action( 'after_setup_theme', 'boc_setup' );

/**
 * Enqueue styles
 */
function boc_enqueue_assets() {
	wp_enqueue_style(
		'boc-style',
		get_stylesheet_uri(),
		array(),
		BOC_VERSION
	);

	wp_enqueue_style(
		'boc-theme',
		BOC_URI . '/assets/css/theme.css',
		array(),
		BOC_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'boc_enqueue_assets' );

/**
 * Register block pattern categories
 */
function boc_register_pattern_categories() {
	register_block_pattern_category( 'boc-hero', array(
		'label' => __( 'Best of Calabria - Hero', 'best-of-calabria' ),
	) );

	register_block_pattern_category( 'boc-cards', array(
		'label' => __( 'Best of Calabria - Cards', 'best-of-calabria' ),
	) );

	register_block_pattern_category( 'boc-cta', array(
		'label' => __( 'Best of Calabria - Call to Action', 'best-of-calabria' ),
	) );

	register_block_pattern_category( 'boc-content', array(
		'label' => __( 'Best of Calabria - Content', 'best-of-calabria' ),
	) );

	register_block_pattern_category( 'boc-destinations', array(
		'label' => __( 'Best of Calabria - Destinations', 'best-of-calabria' ),
	) );
}
add_action( 'init', 'boc_register_pattern_categories' );

/**
 * Register custom block styles
 */
function boc_register_block_styles() {
	register_block_style( 'core/group', array(
		'name'  => 'boc-card',
		'label' => __( 'Card', 'best-of-calabria' ),
	) );

	register_block_style( 'core/group', array(
		'name'  => 'boc-card-hover',
		'label' => __( 'Card with Hover', 'best-of-calabria' ),
	) );

	register_block_style( 'core/image', array(
		'name'  => 'boc-rounded',
		'label' => __( 'Rounded', 'best-of-calabria' ),
	) );

	register_block_style( 'core/separator', array(
		'name'  => 'boc-ornament',
		'label' => __( 'Ornament', 'best-of-calabria' ),
	) );
}
add_action( 'init', 'boc_register_block_styles' );

/**
 * Custom excerpt length
 */
function boc_excerpt_length( $length ) {
	return 25;
}
add_filter( 'excerpt_length', 'boc_excerpt_length' );

/**
 * Custom excerpt more
 */
function boc_excerpt_more( $more ) {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'boc_excerpt_more' );

/**
 * Add hreflang tags for multilingual SEO
 */
function boc_add_hreflang_tags() {
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

	if ( isset( $translations['en'] ) ) {
		printf(
			'<link rel="alternate" hreflang="x-default" href="%s" />' . "\n",
			esc_url( $translations['en']['url'] )
		);
	}
}
add_action( 'wp_head', 'boc_add_hreflang_tags' );

/**
 * Redirect root URL to default language
 */
function boc_root_redirect() {
	if ( function_exists( 'pll_default_language' ) ) {
		return;
	}

	if ( $_SERVER['REQUEST_URI'] === '/' ) {
		wp_redirect( home_url( '/en/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'boc_root_redirect' );
