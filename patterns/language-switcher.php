<?php
/**
 * Title: Language Switcher
 * Slug: best-of-calabria/language-switcher
 * Categories: boc-content
 * Keywords: language, switcher, i18n, polylang
 * Inserter: false
 */

$boc_lang_html = '';

if ( function_exists( 'pll_the_languages' ) ) {
	$languages = pll_the_languages( array(
		'raw'                    => 1,
		'hide_if_no_translation' => 0,
	) );

	if ( ! empty( $languages ) ) {
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
		$boc_lang_html = implode( '<span class="boc-lang-sep"> | </span>', $items );
	}
} else {
	$boc_lang_html = '<a href="/" class="boc-lang-link boc-lang-active" hreflang="en">EN</a>'
		. '<span class="boc-lang-sep"> | </span>'
		. '<a href="/pl/" class="boc-lang-link" hreflang="pl">PL</a>';
}
?>

<!-- wp:html -->
<div class="boc-lang-switcher"><?php echo $boc_lang_html; ?></div>
<!-- /wp:html -->
