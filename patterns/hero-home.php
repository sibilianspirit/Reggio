<?php
/**
 * Title: Hero - Homepage
 * Slug: best-of-calabria/hero-home
 * Categories: boc-hero
 * Keywords: hero, banner, home
 */
?>

<!-- wp:cover {"dimRatio":60,"overlayColor":"primary-dark","minHeight":85,"minHeightUnit":"vh","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}}} -->
<div class="wp-block-cover alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);min-height:85vh">
	<span aria-hidden="true" class="wp-block-cover__background has-primary-dark-background-color has-background-dim-60 has-background-dim"></span>
	<div class="wp-block-cover__inner-container">

		<!-- wp:group {"layout":{"type":"constrained","contentSize":"800px"}} -->
		<div class="wp-block-group">

			<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"0.9rem","letterSpacing":"0.2em","textTransform":"uppercase","fontWeight":"500"}},"textColor":"secondary"} -->
			<p class="has-text-align-center has-secondary-color has-text-color" style="font-size:0.9rem;font-weight:500;letter-spacing:0.2em;text-transform:uppercase"><?php echo esc_html__( 'The Ultimate Guide to Southern Italy', 'best-of-calabria' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontWeight":"800"}},"textColor":"white","fontSize":"hero","fontFamily":"heading"} -->
			<h1 class="wp-block-heading has-text-align-center has-white-color has-text-color has-heading-font-family has-hero-font-size" style="font-weight:800">Best of Calabria</h1>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"1.25rem","lineHeight":"1.6"}},"textColor":"neutral-light"} -->
			<p class="has-text-align-center has-neutral-light-color has-text-color" style="font-size:1.25rem;line-height:1.6"><?php echo esc_html__( 'Discover the hidden gem of Italy. From the dramatic cliffs of Tropea to the ancient streets of Reggio Calabria — stunning beaches, wild mountains, incredible food and authentic culture.', 'best-of-calabria' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
			<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--30)">
				<!-- wp:button {"backgroundColor":"secondary","textColor":"primary-dark","style":{"typography":{"fontWeight":"700"},"border":{"radius":"4px"},"spacing":{"padding":{"top":"0.9rem","right":"2rem","bottom":"0.9rem","left":"2rem"}}}} -->
				<div class="wp-block-button"><a class="wp-block-button__link has-primary-dark-color has-secondary-background-color has-text-color has-background wp-element-button" href="/destinations/" style="border-radius:4px;padding-top:0.9rem;padding-right:2rem;padding-bottom:0.9rem;padding-left:2rem;font-weight:700"><?php echo esc_html__( 'Explore destinations', 'best-of-calabria' ); ?></a></div>
				<!-- /wp:button -->
				<!-- wp:button {"textColor":"white","className":"is-style-outline","style":{"border":{"radius":"4px"},"spacing":{"padding":{"top":"0.9rem","right":"2rem","bottom":"0.9rem","left":"2rem"}}}} -->
				<div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-white-color has-text-color wp-element-button" href="/practical/itineraries/" style="border-radius:4px;padding-top:0.9rem;padding-right:2rem;padding-bottom:0.9rem;padding-left:2rem"><?php echo esc_html__( 'Plan your trip', 'best-of-calabria' ); ?></a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->

		</div>
		<!-- /wp:group -->

	</div>
</div>
<!-- /wp:cover -->
