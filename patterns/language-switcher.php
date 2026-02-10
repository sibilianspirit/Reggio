<?php
/**
 * Title: Language Switcher
 * Slug: reggio-hub/language-switcher
 * Categories: reggio-content
 * Keywords: language, switcher, i18n, polylang
 * Inserter: false
 */

// Language switcher for Polylang
if ( function_exists( 'pll_the_languages' ) ) {
	$languages = pll_the_languages( array(
		'raw'                 => 1,
		'hide_if_no_translation' => 0,
	) );

	if ( ! empty( $languages ) ) {
		echo '<div class="reggio-lang-switcher">';
		$items = array();
		foreach ( $languages as $lang ) {
			$class = $lang['current_lang'] ? 'reggio-lang-active' : '';
			$items[] = sprintf(
				'<a href="%s" class="reggio-lang-link %s" hreflang="%s">%s</a>',
				esc_url( $lang['url'] ),
				esc_attr( $class ),
				esc_attr( $lang['slug'] ),
				esc_html( strtoupper( $lang['slug'] ) )
			);
		}
		echo implode( '<span class="reggio-lang-sep">|</span>', $items );
		echo '</div>';
	}
} else {
	// Fallback when Polylang is not active
	echo '<div class="reggio-lang-switcher">';
	echo '<a href="/en/" class="reggio-lang-link" hreflang="en">EN</a>';
	echo '<span class="reggio-lang-sep">|</span>';
	echo '<a href="/pl/" class="reggio-lang-link" hreflang="pl">PL</a>';
	echo '</div>';
}
?>
