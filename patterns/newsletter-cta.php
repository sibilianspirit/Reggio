<?php
/**
 * Title: Newsletter CTA
 * Slug: best-of-calabria/newsletter-cta
 * Categories: boc-cta
 * Keywords: newsletter, cta, email, signup
 */
?>

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"backgroundColor":"primary","layout":{"type":"constrained","contentSize":"700px"}} -->
<div class="wp-block-group alignfull has-primary-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">

	<!-- wp:heading {"textAlign":"center","textColor":"white","style":{"typography":{"fontWeight":"700"}},"fontSize":"x-large"} -->
	<h2 class="wp-block-heading has-text-align-center has-white-color has-text-color has-x-large-font-size" style="font-weight:700"><?php echo esc_html__( 'Get the Best of Calabria in your inbox', 'best-of-calabria' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","textColor":"neutral-light","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|30"}}}} -->
	<p class="has-text-align-center has-neutral-light-color has-text-color" style="margin-bottom:var(--wp--preset--spacing--30)"><?php echo esc_html__( 'Travel tips, hidden gems, Calabrian recipes and event updates — delivered weekly.', 'best-of-calabria' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons">
		<!-- wp:button {"backgroundColor":"secondary","textColor":"primary-dark","style":{"typography":{"fontWeight":"700"},"border":{"radius":"4px"},"spacing":{"padding":{"top":"0.9rem","right":"2.5rem","bottom":"0.9rem","left":"2.5rem"}}}} -->
		<div class="wp-block-button"><a class="wp-block-button__link has-primary-dark-color has-secondary-background-color has-text-color has-background wp-element-button" style="border-radius:4px;padding-top:0.9rem;padding-right:2.5rem;padding-bottom:0.9rem;padding-left:2.5rem;font-weight:700"><?php echo esc_html__( 'Subscribe to newsletter', 'best-of-calabria' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->

	<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"0.8rem"}},"textColor":"neutral"} -->
	<p class="has-text-align-center has-neutral-color has-text-color" style="font-size:0.8rem"><?php echo esc_html__( 'No spam. Unsubscribe anytime.', 'best-of-calabria' ); ?></p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->
