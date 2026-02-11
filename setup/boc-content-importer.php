<?php
/**
 * Plugin Name: Best of Calabria - Content Importer
 * Description: Import all pages, categories and sample posts. Tools > BOC Import.
 * Version: 2.0.0
 * Author: SibilianSpirit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_management_page( 'BOC Import', 'BOC Import', 'manage_options', 'boc-import', 'boc_import_page' );
} );

function boc_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}

	echo '<div class="wrap"><h1>Best of Calabria - Content Importer</h1>';

	if ( isset( $_POST['boc_reset'] ) && check_admin_referer( 'boc_nonce' ) ) {
		boc_reset_content();
		echo '<div class="notice notice-warning"><p>All content deleted.</p></div>';
	}

	if ( isset( $_POST['boc_import'] ) && check_admin_referer( 'boc_nonce' ) ) {
		boc_run_import();
		echo '<div class="notice notice-success"><p><strong>Import complete!</strong></p></div>';
	}

	echo '<form method="post" style="margin:20px 0">';
	wp_nonce_field( 'boc_nonce' );
	echo '<h2>Step 1: Reset (optional)</h2>';
	echo '<p>Deletes ALL pages, posts, and categories. Use this to start fresh.</p>';
	echo '<button type="submit" name="boc_reset" value="1" class="button" onclick="return confirm(\'Delete ALL content? This cannot be undone.\')">Reset All Content</button>';
	echo '<h2 style="margin-top:30px">Step 2: Import</h2>';
	echo '<p>Creates all pages, categories, sample posts, and configures settings.</p>';
	echo '<button type="submit" name="boc_import" value="1" class="button button-primary">Import Content</button>';
	echo '</form></div>';
}

function boc_reset_content() {
	echo '<pre>';
	$pages = get_posts( array( 'post_type' => 'page', 'numberposts' => -1, 'post_status' => 'any' ) );
	foreach ( $pages as $p ) {
		wp_delete_post( $p->ID, true );
		echo "Deleted page: {$p->post_title}\n";
	}
	$posts = get_posts( array( 'post_type' => 'post', 'numberposts' => -1, 'post_status' => 'any' ) );
	foreach ( $posts as $p ) {
		wp_delete_post( $p->ID, true );
		echo "Deleted post: {$p->post_title}\n";
	}
	$cats = get_categories( array( 'hide_empty' => false ) );
	foreach ( $cats as $c ) {
		if ( $c->slug !== 'uncategorized' ) {
			wp_delete_term( $c->term_id, 'category' );
			echo "Deleted category: {$c->name}\n";
		}
	}
	delete_option( 'boc_content_imported' );
	echo "Done.\n</pre>";
}

function boc_run_import() {
	echo '<pre style="background:#f5f5f5;padding:15px;max-height:400px;overflow:auto">';

	// ─── Helpers ───
	$mk_page = function ( $title, $slug, $content = '', $parent = 0, $tpl = '' ) {
		$exists = get_page_by_path( $slug );
		if ( $exists ) { echo "= {$slug}\n"; return $exists->ID; }
		$a = array( 'post_title' => $title, 'post_name' => $slug, 'post_content' => $content,
			'post_status' => 'publish', 'post_type' => 'page', 'post_parent' => $parent );
		if ( $tpl ) { $a['page_template'] = $tpl; }
		$id = wp_insert_post( $a );
		if ( is_wp_error( $id ) ) { echo "! {$slug}\n"; return 0; }
		if ( $tpl ) { update_post_meta( $id, '_wp_page_template', $tpl ); }
		echo "+ {$title}\n";
		return $id;
	};

	$mk_cat = function ( $name, $slug, $desc = '' ) {
		$e = get_term_by( 'slug', $slug, 'category' );
		if ( $e ) return $e->term_id;
		$r = wp_insert_term( $name, 'category', array( 'slug' => $slug, 'description' => $desc ) );
		if ( is_wp_error( $r ) ) return 0;
		echo "+ cat: {$name}\n";
		return $r['term_id'];
	};

	$mk_post = function ( $title, $slug, $content, $cats = array() ) {
		$e = get_page_by_path( $slug, OBJECT, 'post' );
		if ( $e ) return $e->ID;
		$id = wp_insert_post( array( 'post_title' => $title, 'post_name' => $slug,
			'post_content' => $content, 'post_status' => 'draft', 'post_type' => 'post',
			'post_category' => $cats ) );
		echo "+ draft: {$title}\n";
		return $id;
	};

	// ═══ EN PAGES ═══
	echo "--- EN PAGES ---\n";

	$home = $mk_page( 'Home', 'home', '<!-- wp:pattern {"slug":"best-of-calabria/hero-home"} /-->
<!-- wp:pattern {"slug":"best-of-calabria/section-categories"} /-->
<!-- wp:pattern {"slug":"best-of-calabria/featured-destinations"} /-->
<!-- wp:pattern {"slug":"best-of-calabria/newsletter-cta"} /-->', 0, 'page-landing.html' );

	$dest = $mk_page( 'Destinations', 'destinations', '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"}}},"backgroundColor":"neutral-light","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-neutral-light-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--30)">
<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"hero","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-hero-font-size">Destinations</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">From ancient Greek colonies to cliff-top villages above turquoise waters.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:pattern {"slug":"best-of-calabria/featured-destinations"} /-->' );

	$dests = array(
		'reggio-calabria' => array( 'Reggio Calabria', 'Capital of Calabria, home to the Bronzi di Riace and the Lungomare Falcomata — "the most beautiful kilometer in Italy."' ),
		'tropea'          => array( 'Tropea', 'Perched on a cliff above the Tyrrhenian Sea with crystal-clear waters and the iconic Santa Maria dell\'Isola church.' ),
		'scilla'          => array( 'Scilla', 'Named after Homer\'s mythological sea monster. The Chianalea fishing quarter is "Little Venice of the South."' ),
		'pizzo'           => array( 'Pizzo', 'Birthplace of tartufo gelato with the mysterious Piedigrotta cave church carved from rock.' ),
		'bova'            => array( 'Bova', 'Cultural capital of the Grecanici who still speak Griko, a language rooted in ancient Greek.' ),
		'gerace'          => array( 'Gerace', 'Largest Norman cathedral in Calabria. A perfectly preserved medieval town on a rocky plateau.' ),
		'stilo'           => array( 'Stilo', 'The 9th-century Cattolica di Stilo, a Byzantine masterpiece and UNESCO candidate.' ),
		'cosenza'         => array( 'Cosenza', 'The "Athens of Calabria" with a vibrant old town, Telesio theater, and MAB museum.' ),
		'catanzaro'       => array( 'Catanzaro', 'Regional capital on a hilltop between two seas with spectacular belvedere views.' ),
		'locri'           => array( 'Locri', 'Archaeological site of Locri Epizefiri, one of the most important Greek colonies in Magna Graecia.' ),
	);

	foreach ( $dests as $s => $d ) {
		$c = '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"}}},"backgroundColor":"neutral-light","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-neutral-light-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--30)">
<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"hero","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-hero-font-size">' . esc_html( $d[0] ) . '</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">' . esc_html( $d[1] ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"layout":{"type":"constrained","contentSize":"800px"}} -->
<div class="wp-block-group">
<!-- wp:heading -->
<h2 class="wp-block-heading">What to see</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Comprehensive guide coming soon.</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading">Where to stay</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Accommodation recommendations coming soon.</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading">How to get there</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Transportation guide coming soon.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:pattern {"slug":"best-of-calabria/newsletter-cta"} /-->';
		$mk_page( $d[0], $s, $c, $dest );
	}

	$nature = $mk_page( 'Nature', 'nature', '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"}}},"backgroundColor":"neutral-light","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-neutral-light-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--30)">
<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"hero","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-hero-font-size">Nature &amp; Outdoors</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Three national parks, 800 km of coastline, wild mountains and pristine beaches.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->' );

	foreach ( array( 'aspromonte' => 'Aspromonte National Park', 'sila' => 'Sila National Park', 'pollino' => 'Pollino National Park', 'costa-viola' => 'Costa Viola', 'capo-vaticano' => 'Capo Vaticano', 'beaches' => 'Best Beaches' ) as $s => $t ) {
		$mk_page( $t, $s, '', $nature );
	}

	$cuisine = $mk_page( 'Cuisine', 'cuisine', '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"}}},"backgroundColor":"neutral-light","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-neutral-light-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--30)">
<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"hero","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-hero-font-size">Calabrian Cuisine</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Spicy \'nduja, fresh swordfish, hand-rolled fileja pasta, and the world\'s best bergamot.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->' );

	foreach ( array( 'recipes' => 'Recipes', 'products' => 'Local Products', 'restaurants' => 'Restaurant Guide', 'wine' => 'Calabrian Wine', 'street-food' => 'Street Food' ) as $s => $t ) {
		$mk_page( $t, $s, '', $cuisine );
	}

	$culture = $mk_page( 'Culture & History', 'culture', '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"}}},"backgroundColor":"neutral-light","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-neutral-light-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--30)">
<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"hero","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-hero-font-size">Culture &amp; History</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">From Magna Graecia to the Normans — 3,000 years of Calabrian heritage.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->' );

	foreach ( array( 'history' => 'History', 'traditions' => 'Traditions & Festivals', 'language' => 'Language & Dialect', 'art' => 'Art & Crafts' ) as $s => $t ) {
		$mk_page( $t, $s, '', $culture );
	}

	$practical = $mk_page( 'Practical Info', 'practical', '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"}}},"backgroundColor":"neutral-light","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-neutral-light-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--30)">
<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"hero","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-hero-font-size">Practical Information</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Everything you need to plan your trip to Calabria.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->' );

	foreach ( array( 'getting-there' => 'Getting There', 'accommodation' => 'Where to Stay', 'car-rental' => 'Car Rental', 'safety' => 'Safety Tips', 'weather' => 'Weather & Best Time', 'itineraries' => 'Itineraries' ) as $s => $t ) {
		$mk_page( $t, $s, '', $practical );
	}

	$blog = $mk_page( 'Blog', 'blog', '' );
	$mk_page( 'About', 'about', '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"}}},"backgroundColor":"neutral-light","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-neutral-light-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--30)">
<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"hero","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-hero-font-size">About</h1>
<!-- /wp:heading -->
</div>
<!-- /wp:group -->
<!-- wp:group {"layout":{"type":"constrained","contentSize":"800px"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">We are passionate travelers on a mission to share the beauty of southern Italy\'s best-kept secret with the world.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>Calabria is often overlooked by tourists heading to Tuscany or the Amalfi Coast. But those who venture to the toe of Italy\'s boot discover something extraordinary — pristine beaches, wild mountains, ancient Greek heritage, and the most authentic Italian food.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->' );
	$mk_page( 'Contact', 'contact', '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"}}},"backgroundColor":"neutral-light","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-neutral-light-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--30)">
<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"hero","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-hero-font-size">Contact</h1>
<!-- /wp:heading -->
</div>
<!-- /wp:group -->
<!-- wp:group {"layout":{"type":"constrained","contentSize":"800px"}} -->
<div class="wp-block-group">
<!-- wp:paragraph -->
<p>Email: hello@bestofcalabria.com</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->' );
	$mk_page( 'Privacy Policy', 'privacy-policy' );
	$mk_page( 'Partnership', 'partnership', '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"}}},"backgroundColor":"neutral-light","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-neutral-light-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--30)">
<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"hero","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-hero-font-size">Partner With Us</h1>
<!-- /wp:heading -->
</div>
<!-- /wp:group -->
<!-- wp:group {"layout":{"type":"constrained","contentSize":"800px"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Reach travelers interested in Calabria through sponsored content and advertising.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>Contact: partners@bestofcalabria.com</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->' );

	// ═══ PL PAGES ═══
	echo "\n--- PL PAGES ---\n";

	$pl = $mk_page( 'PL', 'pl', '<!-- wp:pattern {"slug":"best-of-calabria/hero-home"} /-->
<!-- wp:pattern {"slug":"best-of-calabria/section-categories"} /-->
<!-- wp:pattern {"slug":"best-of-calabria/newsletter-cta"} /-->', 0, 'page-landing.html' );

	$pl_dest = $mk_page( 'Miejsca', 'miejsca', '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"}}},"backgroundColor":"neutral-light","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-neutral-light-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--30)">
<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"hero","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-hero-font-size">Miejsca w Kalabrii</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Od starożytnych greckich kolonii po wioski na klifach nad turkusową wodą.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->', $pl );

	foreach ( array(
		'reggio-calabria-pl' => 'Reggio Calabria',
		'tropea-pl' => 'Tropea',
		'scilla-pl' => 'Scilla',
		'pizzo-pl' => 'Pizzo',
		'bova-pl' => 'Bova',
	) as $s => $t ) {
		$mk_page( $t, $s, '', $pl_dest );
	}

	$mk_page( 'Natura', 'natura', '', $pl );
	$mk_page( 'Kuchnia', 'kuchnia', '', $pl );
	$mk_page( 'Kultura', 'kultura', '', $pl );
	$mk_page( 'Praktyczne', 'praktyczne', '', $pl );
	$mk_page( 'O nas', 'o-nas', '', $pl );
	$mk_page( 'Kontakt', 'kontakt-pl', '', $pl );

	// ═══ CATEGORIES ═══
	echo "\n--- CATEGORIES ---\n";
	$c1 = $mk_cat( 'Destinations', 'destinations', 'Places to visit' );
	$c2 = $mk_cat( 'Nature', 'nature', 'Nature and outdoor' );
	$c3 = $mk_cat( 'Cuisine', 'cuisine', 'Food and drink' );
	$c4 = $mk_cat( 'Culture', 'culture', 'History and traditions' );
	$c5 = $mk_cat( 'Practical', 'practical', 'Travel tips' );
	$c6 = $mk_cat( 'Hidden Gems', 'hidden-gems', 'Off the beaten path' );

	// ═══ POSTS ═══
	echo "\n--- SAMPLE POSTS ---\n";
	$mk_post( '10 Reasons Why Calabria Should Be Your Next Destination', '10-reasons-calabria',
		'<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">While millions flock to Rome and the Amalfi Coast, Calabria remains beautifully unspoiled.</p>
<!-- /wp:paragraph -->
<!-- wp:heading --><h2 class="wp-block-heading">1. Beaches that rival the Caribbean</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Tropea and Capo Vaticano offer turquoise waters at a fraction of the price.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2 class="wp-block-heading">2. The best Italian food you\'ve never had</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>From spicy \'nduja to hand-rolled fileja pasta — bold, unforgettable flavors.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2 class="wp-block-heading">3. Three wild national parks</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Aspromonte, Sila, and Pollino — mountains, forests, and ancient trees.</p><!-- /wp:paragraph -->',
		array( $c1, $c5 ) );

	$mk_post( 'The Perfect 7-Day Calabria Itinerary', '7-day-calabria-itinerary',
		'<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">One week from the Tyrrhenian Coast through the mountains to the toe of Italy\'s boot.</p>
<!-- /wp:paragraph -->
<!-- wp:heading --><h2 class="wp-block-heading">Day 1-2: Tropea</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>The cliff-top crown jewel of Calabria. Santa Maria dell\'Isola and world-class beaches.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2 class="wp-block-heading">Day 3: Pizzo</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Original tartufo gelato and the Piedigrotta cave church.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2 class="wp-block-heading">Day 4-5: Scilla &amp; Reggio</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Chianalea fishing village and the Bronzi di Riace.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2 class="wp-block-heading">Day 6-7: Aspromonte &amp; Bova</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wild mountains and the Greek-speaking village of Bova.</p><!-- /wp:paragraph -->',
		array( $c5, $c1 ) );

	$mk_post( 'What is \'Nduja? The Complete Guide', 'what-is-nduja',
		'<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">A fiery, spreadable pork salami from Spilinga that has taken the culinary world by storm.</p>
<!-- /wp:paragraph -->
<!-- wp:heading --><h2 class="wp-block-heading">Origins</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>From the French "andouille", transformed by Calabrians with generous peperoncino.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2 class="wp-block-heading">How to eat it</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>On crusty bread, melted into pasta, on pizza, or stirred into risotto.</p><!-- /wp:paragraph -->',
		array( $c3 ) );

	$mk_post( 'Chianalea: Italy\'s Hidden Little Venice', 'chianalea-little-venice',
		'<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">A tiny fishing quarter clings to the rocks where the Tyrrhenian meets the Strait of Messina.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Houses built on rocks, sea lapping at foundations, narrow alleys, grilled swordfish in the air. Not a tourist trap — a living fishing community for centuries.</p><!-- /wp:paragraph -->',
		array( $c1, $c6 ) );

	// ═══ SETTINGS ═══
	echo "\n--- SETTINGS ---\n";
	if ( $home ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home );
		echo "Homepage set\n";
	}
	if ( $blog ) {
		update_option( 'page_for_posts', $blog );
		echo "Blog page set\n";
	}
	update_option( 'blogname', 'Best of Calabria' );
	update_option( 'blogdescription', 'The Ultimate Guide to Southern Italy' );
	update_option( 'permalink_structure', '/%postname%/' );
	flush_rewrite_rules();
	echo "Title, tagline, permalinks set\n";
	echo "\nDONE!\n</pre>";
}
