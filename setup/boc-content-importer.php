<?php
/**
 * Plugin Name: Best of Calabria - Content Importer
 * Description: One-click import of all pages, categories, and sample posts. Go to Tools > BOC Import to run.
 * Version: 1.0.0
 * Author: SibilianSpirit
 *
 * INSTRUCTIONS:
 * 1. Copy this file to wp-content/plugins/
 * 2. Activate the plugin in WP Admin > Plugins
 * 3. Go to Tools > BOC Import
 * 4. Click "Import Content"
 * 5. Deactivate and delete the plugin when done
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add menu item under Tools
add_action( 'admin_menu', function () {
	add_management_page(
		'BOC Content Importer',
		'BOC Import',
		'manage_options',
		'boc-import',
		'boc_import_page'
	);
} );

function boc_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}

	echo '<div class="wrap">';
	echo '<h1>Best of Calabria - Content Importer</h1>';

	// Handle form submission
	if ( isset( $_POST['boc_run_import'] ) && check_admin_referer( 'boc_import_nonce' ) ) {
		boc_run_import();
		echo '<div class="notice notice-success"><p><strong>Import complete!</strong> You can now deactivate and delete this plugin.</p></div>';
		echo '<p><a href="' . admin_url( 'plugins.php' ) . '" class="button">Go to Plugins</a></p>';
	} else {
		if ( false ) {
			// Allow re-running
		} else {
			echo '<p>This will create:</p>';
			echo '<ul style="list-style:disc;margin-left:20px">';
			echo '<li><strong>40+ pages</strong> — Destinations, Nature, Cuisine, Culture, Practical + subpages</li>';
			echo '<li><strong>6 categories</strong> — Destinations, Nature, Cuisine, Culture, Practical, Hidden Gems</li>';
			echo '<li><strong>4 draft posts</strong> — Sample articles ready to edit and publish</li>';
			echo '<li><strong>Settings</strong> — Site title, tagline, static homepage, permalinks</li>';
			echo '</ul>';
			echo '<form method="post" style="margin-top:20px">';
			wp_nonce_field( 'boc_import_nonce' );
			echo '<input type="hidden" name="boc_run_import" value="1" />';
			submit_button( 'Import Content', 'primary', 'submit', false );
			echo '</form>';
		}
	}

	echo '</div>';
}

function boc_run_import() {
	echo '<pre style="background:#f0f0f0;padding:15px;max-height:500px;overflow:auto">';

	// ─── Helpers ───
	$create_page = function ( $title, $slug, $content = '', $parent_id = 0, $template = '' ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			echo "✓ Page exists: {$slug}\n";
			return $existing->ID;
		}

		$args = array(
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_parent'  => $parent_id,
		);

		if ( $template ) {
			$args['page_template'] = $template;
		}

		$id = wp_insert_post( $args );
		if ( is_wp_error( $id ) ) {
			echo "ERROR: {$slug} — " . $id->get_error_message() . "\n";
			return 0;
		}

		if ( $template ) {
			update_post_meta( $id, '_wp_page_template', $template );
		}

		echo "+ Created page: {$title} (/{$slug}/)\n";
		return $id;
	};

	$create_cat = function ( $name, $slug, $description = '', $parent_id = 0 ) {
		$existing = get_term_by( 'slug', $slug, 'category' );
		if ( $existing ) {
			echo "✓ Category exists: {$slug}\n";
			return $existing->term_id;
		}

		$result = wp_insert_term( $name, 'category', array(
			'slug'        => $slug,
			'description' => $description,
			'parent'      => $parent_id,
		) );

		if ( is_wp_error( $result ) ) {
			echo "ERROR: category {$slug} — " . $result->get_error_message() . "\n";
			return 0;
		}

		echo "+ Created category: {$name}\n";
		return $result['term_id'];
	};

	$create_post = function ( $title, $slug, $content, $category_ids = array() ) {
		$existing = get_page_by_path( $slug, OBJECT, 'post' );
		if ( $existing ) {
			echo "✓ Post exists: {$slug}\n";
			return $existing->ID;
		}

		$id = wp_insert_post( array(
			'post_title'    => $title,
			'post_name'     => $slug,
			'post_content'  => $content,
			'post_status'   => 'draft',
			'post_type'     => 'post',
			'post_category' => $category_ids,
		) );

		if ( is_wp_error( $id ) ) {
			echo "ERROR: post {$slug} — " . $id->get_error_message() . "\n";
			return 0;
		}

		echo "+ Draft post: {$title}\n";
		return $id;
	};

	// ═══ PAGES ═══
	echo "=== CREATING PAGES ===\n\n";

	$home_content = '<!-- wp:pattern {"slug":"best-of-calabria/hero-home"} /-->
<!-- wp:pattern {"slug":"best-of-calabria/section-categories"} /-->
<!-- wp:pattern {"slug":"best-of-calabria/featured-destinations"} /-->
<!-- wp:pattern {"slug":"best-of-calabria/newsletter-cta"} /-->';

	$home_id = $create_page( 'Home', 'home', $home_content, 0, 'page-landing.html' );

	// Destinations
	$destinations_content = '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-xx-large-font-size">Destinations in Calabria</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">From ancient Greek colonies to cliff-top villages perched above turquoise waters — explore the best places Calabria has to offer.</p>
<!-- /wp:paragraph -->

<!-- wp:pattern {"slug":"best-of-calabria/featured-destinations"} /-->';

	$dest_id = $create_page( 'Destinations', 'destinations', $destinations_content );

	$destinations = array(
		'reggio-calabria' => array( 'Reggio Calabria', 'The capital of Calabria\'s metropolitan area, home to the famous Bronzi di Riace and one of the most beautiful waterfronts in Italy.' ),
		'tropea'          => array( 'Tropea', 'Perched on a cliff above the Tyrrhenian Sea, Tropea is Calabria\'s most iconic destination with crystal-clear waters and the dramatic Santa Maria dell\'Isola.' ),
		'scilla'          => array( 'Scilla', 'Named after the mythological sea monster from Homer\'s Odyssey. The Chianalea fishing quarter is one of Italy\'s most charming neighborhoods.' ),
		'pizzo'           => array( 'Pizzo', 'Birthplace of tartufo gelato, with the mysterious Piedigrotta cave church and a charming historic center overlooking the sea.' ),
		'bova'            => array( 'Bova', 'Cultural capital of the Grecanici, descendants of ancient Greek settlers who still speak Griko. A living museum of Magna Graecia heritage.' ),
		'gerace'          => array( 'Gerace', 'Home to the largest Norman cathedral in Calabria, a perfectly preserved medieval town perched on a rocky plateau.' ),
		'stilo'           => array( 'Stilo', 'Famous for the Cattolica di Stilo, a 9th-century Byzantine church and UNESCO World Heritage candidate.' ),
		'cosenza'         => array( 'Cosenza', 'The "Athens of Calabria" — a university city with a vibrant old town, the Telesio theater, and the MAB open-air museum.' ),
		'catanzaro'       => array( 'Catanzaro', 'The regional capital sits on a hilltop between two seas with spectacular belvedere views and silk-weaving tradition.' ),
		'locri'           => array( 'Locri', 'Home to Locri Epizefiri, one of the most important Greek colonies in Magna Graecia, dating back to the 7th century BC.' ),
	);

	foreach ( $destinations as $slug => $d ) {
		$content = '<!-- wp:heading {"level":1,"fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-heading-font-family has-xx-large-font-size">' . esc_html( $d[0] ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">' . esc_html( $d[1] ) . '</p>
<!-- /wp:paragraph -->

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

<!-- wp:pattern {"slug":"best-of-calabria/newsletter-cta"} /-->';

		$create_page( $d[0], $slug, $content, $dest_id );
	}

	// Nature
	$nature_id = $create_page( 'Nature', 'nature', '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-xx-large-font-size">Nature &amp; Outdoors</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Three national parks, 800 kilometers of coastline, wild mountains and pristine beaches.</p>
<!-- /wp:paragraph -->' );

	foreach ( array(
		'aspromonte'    => 'Aspromonte National Park',
		'sila'          => 'Sila National Park',
		'pollino'       => 'Pollino National Park',
		'costa-viola'   => 'Costa Viola',
		'capo-vaticano' => 'Capo Vaticano',
		'beaches'       => 'Best Beaches',
	) as $slug => $title ) {
		$create_page( $title, $slug, '', $nature_id );
	}

	// Cuisine
	$cuisine_id = $create_page( 'Cuisine', 'cuisine', '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-xx-large-font-size">Calabrian Cuisine</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Spicy \'nduja, fresh swordfish, hand-rolled fileja pasta, and the world\'s best bergamot.</p>
<!-- /wp:paragraph -->' );

	foreach ( array(
		'recipes' => 'Recipes', 'products' => 'Local Products', 'restaurants' => 'Restaurant Guide',
		'wine' => 'Calabrian Wine', 'street-food' => 'Street Food',
	) as $slug => $title ) {
		$create_page( $title, $slug, '', $cuisine_id );
	}

	// Culture
	$culture_id = $create_page( 'Culture & History', 'culture', '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-xx-large-font-size">Culture &amp; History</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">From Magna Graecia to the Normans — Calabria\'s culture is 3,000 years deep.</p>
<!-- /wp:paragraph -->' );

	foreach ( array(
		'history' => 'History', 'traditions' => 'Traditions & Festivals',
		'language' => 'Language & Dialect', 'art' => 'Art & Crafts',
	) as $slug => $title ) {
		$create_page( $title, $slug, '', $culture_id );
	}

	// Practical
	$practical_id = $create_page( 'Practical Info', 'practical', '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-xx-large-font-size">Practical Information</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Everything you need to plan your trip to Calabria.</p>
<!-- /wp:paragraph -->' );

	foreach ( array(
		'getting-there' => 'Getting There', 'accommodation' => 'Where to Stay',
		'car-rental' => 'Car Rental', 'safety' => 'Safety Tips',
		'weather' => 'Weather & Best Time to Visit', 'itineraries' => 'Itineraries',
	) as $slug => $title ) {
		$create_page( $title, $slug, '', $practical_id );
	}

	// Static pages
	$create_page( 'About', 'about', '<!-- wp:heading {"level":1,"fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-heading-font-family has-xx-large-font-size">About Best of Calabria</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We\'re passionate travelers on a mission to share the beauty of southern Italy\'s best-kept secret with the world.</p>
<!-- /wp:paragraph -->' );

	$create_page( 'Contact', 'contact', '<!-- wp:heading {"level":1,"fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-heading-font-family has-xx-large-font-size">Contact Us</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Email: hello@bestofcalabria.com</p>
<!-- /wp:paragraph -->' );

	// Blog page (needed for post listing)
	$blog_id = $create_page( 'Blog', 'blog', '' );

	$create_page( 'Privacy Policy', 'privacy-policy' );

	$create_page( 'Partnership', 'partnership', '<!-- wp:heading {"level":1,"fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-heading-font-family has-xx-large-font-size">Partner With Us</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Contact us at partners@bestofcalabria.com to discuss collaboration.</p>
<!-- /wp:paragraph -->' );

	// ═══ POLISH PAGES ═══
	echo "\n=== CREATING POLISH PAGES ===\n\n";

	// PL parent — this will create /pl/ URL prefix
	$pl_id = $create_page( 'Strona główna', 'pl', '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"hero","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-hero-font-size">Best of Calabria</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Odkryj ukryty klejnot Włoch. Dramatyczne klify Tropei, starożytne ulice Reggio Calabria — oszałamiające plaże, dzikie góry, niesamowite jedzenie i autentyczna kultura.</p>
<!-- /wp:paragraph -->', 0, 'page-landing.html' );

	$pl_dest_id = $create_page( 'Miejsca', 'miejsca', '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-xx-large-font-size">Miejsca w Kalabrii</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Od starożytnych greckich kolonii po klify nad turkusową wodą — odkryj najpiękniejsze miejsca Kalabrii.</p>
<!-- /wp:paragraph -->', $pl_id );

	$pl_destinations = array(
		'reggio-calabria-pl' => array( 'Reggio Calabria', 'Stolica metropolitalna Kalabrii, dom słynnych Brązów z Riace i jednego z najpiękniejszych bulwarów Włoch — Lungomare Falcomatà.' ),
		'tropea-pl'          => array( 'Tropea', 'Wisząca na klifie nad Morzem Tyrreńskim, Tropea to najbardziej ikoniczne miejsce Kalabrii z krystalicznie czystą wodą.' ),
		'scilla-pl'          => array( 'Scilla', 'Nazwana od mitologicznego morskiego potwora z Odysei Homera. Dzielnica rybacka Chianalea to jedna z najurokliwszych w Italii.' ),
		'pizzo-pl'           => array( 'Pizzo', 'Miejsce narodzin tartufo — najsłynniejszych włoskich lodów. Z tajemniczym kościołem wykutym w skale Piedigrotta.' ),
		'bova-pl'            => array( 'Bova', 'Stolica kulturowa Grecanici — potomków starożytnych greckich osadników, którzy wciąż mówią w języku Griko.' ),
	);

	foreach ( $pl_destinations as $slug => $d ) {
		$create_page( $d[0], $slug, '<!-- wp:heading {"level":1,"fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-heading-font-family has-xx-large-font-size">' . esc_html( $d[0] ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">' . esc_html( $d[1] ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Pełny przewodnik wkrótce.</p>
<!-- /wp:paragraph -->', $pl_dest_id );
	}

	$pl_nature_id = $create_page( 'Natura', 'natura', '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-xx-large-font-size">Natura i Outdoor</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Trzy parki narodowe, 800 km wybrzeża, dzikie góry i dziewicze plaże.</p>
<!-- /wp:paragraph -->', $pl_id );

	$create_page( 'Kuchnia', 'kuchnia', '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-xx-large-font-size">Kuchnia Kalabryjska</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Pikantna \'nduja, świeży miecznik, ręcznie robiony makaron fileja i najlepszy bergamot na świecie.</p>
<!-- /wp:paragraph -->', $pl_id );

	$create_page( 'Kultura i Historia', 'kultura', '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-xx-large-font-size">Kultura i Historia</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Od Magna Graecia po Normanów — 3000 lat kultury Kalabrii.</p>
<!-- /wp:paragraph -->', $pl_id );

	$create_page( 'Praktyczne', 'praktyczne', '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-xx-large-font-size">Informacje Praktyczne</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Wszystko czego potrzebujesz by zaplanować podróż do Kalabrii.</p>
<!-- /wp:paragraph -->', $pl_id );

	$create_page( 'O nas', 'o-nas', '<!-- wp:heading {"level":1,"fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-heading-font-family has-xx-large-font-size">O Best of Calabria</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Jesteśmy pasjonatami podróży, których misją jest pokazanie światu piękna najlepiej strzeżonego sekretu południowych Włoch.</p>
<!-- /wp:paragraph -->', $pl_id );

	$create_page( 'Kontakt', 'kontakt-pl', '<!-- wp:heading {"level":1,"fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-heading-font-family has-xx-large-font-size">Kontakt</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Email: hello@bestofcalabria.com</p>
<!-- /wp:paragraph -->', $pl_id );

	// ═══ CATEGORIES ═══
	echo "\n=== CREATING CATEGORIES ===\n\n";

	$cat_dest   = $create_cat( 'Destinations', 'destinations', 'Articles about places to visit in Calabria' );
	$cat_nature = $create_cat( 'Nature', 'nature', 'Nature and outdoor activities' );
	$cat_food   = $create_cat( 'Cuisine', 'cuisine', 'Food, recipes, restaurants and wine' );
	$cat_cult   = $create_cat( 'Culture', 'culture', 'History, traditions, art and language' );
	$cat_pract  = $create_cat( 'Practical', 'practical', 'Travel tips, transport, accommodation' );
	$cat_gems   = $create_cat( 'Hidden Gems', 'hidden-gems', 'Off-the-beaten-path places' );

	// ═══ SAMPLE POSTS ═══
	echo "\n=== CREATING SAMPLE POSTS ===\n\n";

	$create_post(
		'10 Reasons Why Calabria Should Be Your Next Italian Destination',
		'10-reasons-calabria-next-destination',
		'<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">While millions flock to Rome and the Amalfi Coast, Calabria remains beautifully unspoiled. Here\'s why it deserves a spot on your bucket list.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">1. Beaches that rival the Caribbean</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Tropea, Capo Vaticano, and the Costa degli Aranci offer turquoise waters and white sand at a fraction of the price.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">2. Authentic Italian food at its best</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>From spicy \'nduja to hand-rolled fileja pasta with goat ragu, the flavors are bold and unforgettable.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">3. Three national parks</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Aspromonte, Sila, and Pollino — from gentle lake walks to challenging mountain treks.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">4. 3,000 years of history</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Greek colonies, Byzantine churches, Norman castles, and living Greek-speaking communities.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">5. Incredibly affordable</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Expect to pay significantly less than northern Italy for accommodation, dining, and activities.</p>
<!-- /wp:paragraph -->',
		array( $cat_dest, $cat_pract )
	);

	$create_post(
		'The Perfect 7-Day Calabria Itinerary',
		'perfect-7-day-calabria-itinerary',
		'<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">One week to experience the highlights — from the Tyrrhenian Coast to the mountains and down to the toe of the boot.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Day 1-2: Tropea &amp; Capo Vaticano</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Start in Tropea, explore the old town, visit Santa Maria dell\'Isola, and spend a day at Capo Vaticano beaches.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Day 3: Pizzo</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Original tartufo gelato and the mysterious Piedigrotta cave church.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Day 4: Scilla</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The fishing village of Chianalea — one of Italy\'s most romantic spots.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Day 5: Reggio Calabria</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Bronzi di Riace and the Lungomare — "the most beautiful kilometer in Italy."</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Day 6: Aspromonte &amp; Bova</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Wild mountains and the ancient Greek-Calabrian village of Bova.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Day 7: Gerace &amp; Ionian Coast</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Medieval Gerace and the sandy Ionian beaches to finish your trip.</p>
<!-- /wp:paragraph -->',
		array( $cat_pract, $cat_dest )
	);

	$create_post(
		'What is \'Nduja? The Ultimate Guide to Calabria\'s Famous Spicy Spread',
		'what-is-nduja-guide',
		'<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">\'Nduja is a fiery, spreadable pork salami from Spilinga in Calabria. Here\'s everything you need to know.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Origins</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The name likely derives from the French "andouille", brought to Calabria during the Napoleonic period. The Calabrians made it their own with generous peperoncino.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">How to eat it</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Spread on crusty bread, melted into pasta sauces, on pizza, stirred into risotto, or to flavor beans and vegetables.</p>
<!-- /wp:paragraph -->',
		array( $cat_food )
	);

	$create_post(
		'Chianalea di Scilla: Italy\'s Hidden "Little Venice"',
		'chianalea-scilla-little-venice',
		'<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Forget Venice or Cinque Terre. A tiny fishing quarter clings to the rocks where the Tyrrhenian Sea meets the Strait of Messina.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Chianalea is the ancient fishing quarter of Scilla. Houses are built directly on the rocks, narrow alleys wind between colorful buildings, and the scent of grilled swordfish fills the air. This isn\'t a tourist village — it\'s a living fishing community.</p>
<!-- /wp:paragraph -->',
		array( $cat_dest, $cat_gems )
	);

	// ═══ SETTINGS ═══
	echo "\n=== CONFIGURING SETTINGS ===\n\n";

	if ( $home_id ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );
		echo "Set static homepage\n";
	}

	if ( $blog_id ) {
		update_option( 'page_for_posts', $blog_id );
		echo "Set blog page for posts listing\n";
	}

	update_option( 'blogname', 'Best of Calabria' );
	update_option( 'blogdescription', 'The Ultimate Guide to Southern Italy' );
	echo "Updated site title and tagline\n";

	update_option( 'permalink_structure', '/%postname%/' );
	echo "Set permalinks to /%postname%/\n";

	flush_rewrite_rules();
	echo "Flushed rewrite rules\n";

	update_option( 'boc_content_imported', true );

	echo "\n================================\n";
	echo "DONE! All content imported.\n";
	echo "================================\n";
	echo '</pre>';
}
