<?php
/**
 * Title: Language Switcher
 * Slug: best-of-calabria/language-switcher
 * Categories: boc-content
 * Keywords: language, switcher, i18n, polylang
 * Inserter: false
 */

if ( function_exists( 'pll_the_languages' ) ) {
	$languages = pll_the_languages( array(
		'raw'                    => 1,
		'hide_if_no_translation' => 0,
	) );

	if ( ! empty( $languages ) ) {
		echo '<div class="boc-lang-switcher">';
		$items = array();
		foreach ( $languages as $lang ) {
			$class = $lang['current_lang'] ? 'boc-lang-active' : '';
			$items[] = sprintf(
				'<a href="%s" class="boc-lang-link %s" hreflang="%s">%s</a>',
				esc_url( $lang['url'] ),
				esc_attr( $class ),
				esc_attr( $lang['slug'] ),
				esc_html( strtoupper( $lang['slug'] ) )
			);
		}
		echo implode( '<span class="boc-lang-sep">|</span>', $items );
		echo '</div>';
	}
} else {
	echo '<div class="boc-lang-switcher">';
	echo '<a href="/en/" class="boc-lang-link" hreflang="en">EN</a>';
	echo '<span class="boc-lang-sep">|</span>';
	echo '<a href="/pl/" class="boc-lang-link" hreflang="pl">PL</a>';
	echo '</div>';
}
?>
