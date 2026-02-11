<?php
/**
 * Best of Calabria - Content Setup Script
 *
 * Upload this file to your WordPress root and run it once:
 * https://bestofcalabria.com/import-content.php
 *
 * It will create all pages, categories, and menus.
 * DELETE THIS FILE after running it!
 *
 * @package BestOfCalabria
 */

// Load WordPress
// Try multiple paths to find wp-load.php
$paths = array(
	__DIR__ . '/wp-load.php',           // same directory
	__DIR__ . '/../wp-load.php',        // parent directory (if in subfolder)
	__DIR__ . '/../../wp-load.php',     // two levels up
);

$loaded = false;
foreach ( $paths as $path ) {
	if ( file_exists( $path ) ) {
		require_once $path;
		$loaded = true;
		break;
	}
}

if ( ! $loaded ) {
	die( 'Could not find wp-load.php. Place this file in your WordPress root directory (next to wp-config.php).' );
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'You must be logged in as admin to run this script.' );
}

// Prevent running twice
if ( get_option( 'boc_content_imported' ) ) {
	wp_die( 'Content has already been imported. Delete option "boc_content_imported" in DB to re-run.' );
}

echo '<h1>Best of Calabria - Content Setup</h1>';
echo '<pre>';

// ─── Helper ───
function boc_create_page( $title, $slug, $content = '', $parent_id = 0, $template = '' ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		echo "Page already exists: {$slug}\n";
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
		echo "ERROR creating page {$slug}: " . $id->get_error_message() . "\n";
		return 0;
	}

	if ( $template ) {
		update_post_meta( $id, '_wp_page_template', $template );
	}

	echo "Created page: {$title} (/{$slug}/)\n";
	return $id;
}

function boc_create_category( $name, $slug, $description = '', $parent_id = 0 ) {
	$existing = get_term_by( 'slug', $slug, 'category' );
	if ( $existing ) {
		echo "Category already exists: {$slug}\n";
		return $existing->term_id;
	}

	$result = wp_insert_term( $name, 'category', array(
		'slug'        => $slug,
		'description' => $description,
		'parent'      => $parent_id,
	) );

	if ( is_wp_error( $result ) ) {
		echo "ERROR creating category {$slug}: " . $result->get_error_message() . "\n";
		return 0;
	}

	echo "Created category: {$name}\n";
	return $result['term_id'];
}

function boc_create_post( $title, $slug, $content, $category_ids = array(), $status = 'draft' ) {
	$existing = get_page_by_path( $slug, OBJECT, 'post' );
	if ( $existing ) {
		echo "Post already exists: {$slug}\n";
		return $existing->ID;
	}

	$id = wp_insert_post( array(
		'post_title'    => $title,
		'post_name'     => $slug,
		'post_content'  => $content,
		'post_status'   => $status,
		'post_type'     => 'post',
		'post_category' => $category_ids,
	) );

	if ( is_wp_error( $id ) ) {
		echo "ERROR creating post {$slug}: " . $id->get_error_message() . "\n";
		return 0;
	}

	$label = $status === 'publish' ? 'Published' : 'Draft';
	echo "{$label} post: {$title}\n";
	return $id;
}

// ═══════════════════════════════════════
// 1. PAGES
// ═══════════════════════════════════════
echo "\n═══ CREATING PAGES ═══\n\n";

// Homepage
$home_content = '<!-- wp:pattern {"slug":"best-of-calabria/hero-home"} /-->
<!-- wp:pattern {"slug":"best-of-calabria/section-categories"} /-->
<!-- wp:pattern {"slug":"best-of-calabria/featured-destinations"} /-->
<!-- wp:pattern {"slug":"best-of-calabria/newsletter-cta"} /-->';

$home_id = boc_create_page( 'Home', 'home', $home_content, 0, 'page-landing.html' );

// Destinations hub
$destinations_content = '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-xx-large-font-size">Destinations in Calabria</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">From ancient Greek colonies to cliff-top villages perched above turquoise waters — explore the best places Calabria has to offer.</p>
<!-- /wp:paragraph -->

<!-- wp:pattern {"slug":"best-of-calabria/featured-destinations"} /-->';

$dest_id = boc_create_page( 'Destinations', 'destinations', $destinations_content );

// Individual destination pages
$destinations = array(
	'reggio-calabria' => array(
		'title' => 'Reggio Calabria',
		'desc'  => 'The capital of Calabria\'s metropolitan area, home to the famous Bronzi di Riace and one of the most beautiful waterfronts in Italy — the Lungomare Falcomatà, described by Gabriele D\'Annunzio as "the most beautiful kilometer in Italy."',
	),
	'tropea' => array(
		'title' => 'Tropea',
		'desc'  => 'Perched on a cliff above the Tyrrhenian Sea, Tropea is Calabria\'s most iconic destination. Its crystal-clear waters, the dramatic Santa Maria dell\'Isola church on a rocky outcrop, and the famous Tropea red onion make it unforgettable.',
	),
	'scilla' => array(
		'title' => 'Scilla',
		'desc'  => 'Named after the mythological sea monster from Homer\'s Odyssey, Scilla sits at the northern entrance of the Strait of Messina. The Chianalea fishing quarter — "Little Venice of the South" — is one of Italy\'s most charming neighborhoods.',
	),
	'pizzo' => array(
		'title' => 'Pizzo',
		'desc'  => 'This small seaside town is the birthplace of tartufo — Italy\'s most beloved gelato. Beyond the famous ice cream, Pizzo offers the mysterious Piedigrotta cave church carved entirely from rock and a charming historic center.',
	),
	'bova' => array(
		'title' => 'Bova',
		'desc'  => 'One of Italy\'s most beautiful villages and the cultural capital of the Grecanici — descendants of ancient Greek settlers who still speak Griko, a language rooted in ancient Greek. Bova is a living museum of Magna Graecia heritage.',
	),
	'gerace' => array(
		'title' => 'Gerace',
		'desc'  => 'Home to the largest Norman cathedral in Calabria, Gerace is a perfectly preserved medieval town perched on a rocky plateau. Its labyrinth of narrow streets reveals Byzantine churches, noble palaces, and panoramic views.',
	),
	'stilo' => array(
		'title' => 'Stilo',
		'desc'  => 'Famous for the Cattolica di Stilo, a 9th-century Byzantine church and UNESCO World Heritage candidate. This small mountain town represents the deep Byzantine influence on Calabrian culture and architecture.',
	),
	'cosenza' => array(
		'title' => 'Cosenza',
		'desc'  => 'The "Athens of Calabria" — a university city with a vibrant old town, the Telesio theater, and the striking MAB open-air museum. Cosenza bridges Calabria\'s ancient past with its contemporary cultural scene.',
	),
	'catanzaro' => array(
		'title' => 'Catanzaro',
		'desc'  => 'The regional capital of Calabria sits on a hilltop between two seas. Known for its spectacular belvedere views, silk-weaving tradition, and as a gateway to the beautiful Ionian coast.',
	),
	'locri' => array(
		'title' => 'Locri',
		'desc'  => 'Home to the archaeological site of Locri Epizefiri, one of the most important Greek colonies in Magna Graecia. The ruins, museum, and nearby coast offer a journey back to the 7th century BC.',
	),
);

foreach ( $destinations as $slug => $data ) {
	$content = '<!-- wp:heading {"level":1,"fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-heading-font-family has-xx-large-font-size">' . esc_html( $data['title'] ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">' . esc_html( $data['desc'] ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">What to see</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Content coming soon — we\'re working on a comprehensive guide to ' . esc_html( $data['title'] ) . '.</p>
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

	boc_create_page( $data['title'], $slug, $content, $dest_id );
}

// Nature hub
$nature_content = '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-xx-large-font-size">Nature & Outdoors</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Three national parks, 800 kilometers of coastline, wild mountains and pristine beaches — Calabria is a paradise for nature lovers.</p>
<!-- /wp:paragraph -->';

$nature_id = boc_create_page( 'Nature', 'nature', $nature_content );

$nature_pages = array(
	'aspromonte'  => array( 'Aspromonte National Park', 'Wild mountains at the toe of Italy\'s boot. Ancient forests, dramatic waterfalls, and the last remnants of Greek-speaking villages.' ),
	'sila'        => array( 'Sila National Park', 'The "Great Forest of Italy" — vast highland plateaus, pristine lakes, and Calabria\'s best skiing in winter.' ),
	'pollino'     => array( 'Pollino National Park', 'Italy\'s largest national park, shared with Basilicata. Home to ancient Bosnian pines, dramatic canyons, and world-class rafting.' ),
	'costa-viola' => array( 'Costa Viola', 'The "Purple Coast" stretching from Scilla to Palmi — dramatic cliffs, hidden coves, and legendary sunsets over the Aeolian Islands.' ),
	'capo-vaticano' => array( 'Capo Vaticano', 'Granite cliffs plunging into crystal-clear waters. One of the top 10 beaches in the world according to many travel guides.' ),
	'beaches'     => array( 'Best Beaches', 'A comprehensive guide to Calabria\'s best beaches — from the white sands of Tropea to the wild shores of the Ionian coast.' ),
);

foreach ( $nature_pages as $slug => $data ) {
	$content = '<!-- wp:heading {"level":1,"fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-heading-font-family has-xx-large-font-size">' . esc_html( $data[0] ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">' . esc_html( $data[1] ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Full guide coming soon.</p>
<!-- /wp:paragraph -->';

	boc_create_page( $data[0], $slug, $content, $nature_id );
}

// Cuisine hub
$cuisine_id = boc_create_page( 'Cuisine', 'cuisine', '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-xx-large-font-size">Calabrian Cuisine</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Spicy \'nduja, fresh swordfish, hand-rolled fileja pasta, and the world\'s best bergamot — discover the bold flavors of Calabria.</p>
<!-- /wp:paragraph -->' );

foreach ( array(
	'recipes'     => 'Recipes',
	'products'    => 'Local Products',
	'restaurants' => 'Restaurant Guide',
	'wine'        => 'Calabrian Wine',
	'street-food' => 'Street Food',
) as $slug => $title ) {
	boc_create_page( $title, $slug, '', $cuisine_id );
}

// Culture hub
$culture_id = boc_create_page( 'Culture & History', 'culture', '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-xx-large-font-size">Culture & History</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">From Magna Graecia to the Normans, from Byzantine churches to living Greek-speaking communities — Calabria\'s culture is 3,000 years deep.</p>
<!-- /wp:paragraph -->' );

foreach ( array(
	'history'    => 'History',
	'traditions' => 'Traditions & Festivals',
	'language'   => 'Language & Dialect',
	'art'        => 'Art & Crafts',
) as $slug => $title ) {
	boc_create_page( $title, $slug, '', $culture_id );
}

// Practical hub
$practical_id = boc_create_page( 'Practical Info', 'practical', '<!-- wp:heading {"level":1,"textAlign":"center","fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-text-align-center has-heading-font-family has-xx-large-font-size">Practical Information</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Everything you need to plan your trip to Calabria — flights, car rentals, accommodation, weather, safety tips, and ready-made itineraries.</p>
<!-- /wp:paragraph -->' );

foreach ( array(
	'getting-there'  => 'Getting There',
	'accommodation'  => 'Where to Stay',
	'car-rental'     => 'Car Rental',
	'safety'         => 'Safety Tips',
	'weather'        => 'Weather & Best Time to Visit',
	'itineraries'    => 'Itineraries',
) as $slug => $title ) {
	boc_create_page( $title, $slug, '', $practical_id );
}

// Static pages
boc_create_page( 'About', 'about', '<!-- wp:heading {"level":1,"fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-heading-font-family has-xx-large-font-size">About Best of Calabria</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">We\'re passionate travelers and Calabria enthusiasts on a mission to share the beauty of southern Italy\'s best-kept secret with the world.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Calabria is often overlooked by tourists heading to Tuscany, the Amalfi Coast, or Sicily. But those who venture to the toe of Italy\'s boot discover something extraordinary — pristine beaches, wild mountains, ancient Greek heritage, and the most authentic Italian food you\'ll ever taste.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Best of Calabria is your comprehensive guide to this incredible region. We cover destinations, nature, cuisine, culture, and all the practical information you need to plan the perfect trip.</p>
<!-- /wp:paragraph -->' );

boc_create_page( 'Contact', 'contact', '<!-- wp:heading {"level":1,"fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-heading-font-family has-xx-large-font-size">Contact Us</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Have a question, suggestion, or partnership inquiry? We\'d love to hear from you.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Email: hello@bestofcalabria.com</p>
<!-- /wp:paragraph -->' );

boc_create_page( 'Privacy Policy', 'privacy-policy' );

boc_create_page( 'Partnership', 'partnership', '<!-- wp:heading {"level":1,"fontSize":"xx-large","fontFamily":"heading"} -->
<h1 class="wp-block-heading has-heading-font-family has-xx-large-font-size">Partner With Us</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Reach travelers interested in Calabria through sponsored content, featured listings, and advertising on Best of Calabria.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>We offer partnership opportunities for hotels, tour operators, restaurants, and local businesses in the Calabria region. Contact us at partners@bestofcalabria.com to discuss collaboration.</p>
<!-- /wp:paragraph -->' );

// ═══════════════════════════════════════
// 2. CATEGORIES
// ═══════════════════════════════════════
echo "\n═══ CREATING CATEGORIES ═══\n\n";

$cat_destinations = boc_create_category( 'Destinations', 'destinations', 'Articles about places to visit in Calabria' );
$cat_nature       = boc_create_category( 'Nature', 'nature', 'Nature and outdoor activities in Calabria' );
$cat_cuisine      = boc_create_category( 'Cuisine', 'cuisine', 'Food, recipes, restaurants and wine of Calabria' );
$cat_culture      = boc_create_category( 'Culture', 'culture', 'History, traditions, art and language of Calabria' );
$cat_practical    = boc_create_category( 'Practical', 'practical', 'Travel tips, transport, accommodation' );
$cat_hidden_gems  = boc_create_category( 'Hidden Gems', 'hidden-gems', 'Off-the-beaten-path places and experiences' );

// ═══════════════════════════════════════
// 3. SAMPLE BLOG POSTS (as drafts)
// ═══════════════════════════════════════
echo "\n═══ CREATING SAMPLE POSTS ═══\n\n";

boc_create_post(
	'10 Reasons Why Calabria Should Be Your Next Italian Destination',
	'10-reasons-calabria-next-destination',
	'<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">While millions flock to Rome, Florence, and the Amalfi Coast, a region at the very tip of Italy\'s boot remains beautifully unspoiled. Here\'s why Calabria deserves a spot on your travel bucket list.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">1. Beaches that rival the Caribbean</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Tropea, Capo Vaticano, and the Costa degli Aranci offer turquoise waters and white sand that would look at home in the Maldives — at a fraction of the price.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">2. Authentic Italian food at its best</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Forget tourist-trap restaurants. In Calabria, every meal is an event. From spicy \'nduja spread to hand-rolled fileja pasta with goat ragù, the flavors here are bold and unforgettable.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">3. Three national parks</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Aspromonte, Sila, and Pollino offer everything from gentle lake walks to challenging mountain treks through ancient forests.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">4. 3,000 years of history</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ancient Greek colonies, Byzantine churches, Norman castles, and living Greek-speaking communities make Calabria a cultural treasure chest.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">5. Incredibly affordable</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Compared to northern Italy, Calabria offers exceptional value. Expect to pay significantly less for accommodation, dining, and activities.</p>
<!-- /wp:paragraph -->',
	array( $cat_destinations, $cat_practical ),
	'draft'
);

boc_create_post(
	'The Perfect 7-Day Calabria Itinerary',
	'perfect-7-day-calabria-itinerary',
	'<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">One week is the perfect amount of time to experience the highlights of Calabria. This itinerary takes you from the Tyrrhenian Coast to the mountains and down to the toe of the boot.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Day 1-2: Tropea & Capo Vaticano</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Start your journey in Tropea, Calabria\'s crown jewel. Explore the old town perched on the cliff, visit Santa Maria dell\'Isola, and spend an afternoon at the beach. On day 2, drive to Capo Vaticano for world-class beaches and stunning granite formations.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Day 3: Pizzo & the Violet Coast</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Head to Pizzo for the original tartufo gelato and the mysterious Piedigrotta cave church. Continue along the coast exploring hidden coves.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Day 4: Scilla & the Strait of Messina</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The fishing village of Chianalea in Scilla is one of Italy\'s most romantic spots. Watch the sunset over Sicily from the castle.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Day 5: Reggio Calabria</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Visit the National Museum to see the legendary Bronzi di Riace, then stroll the Lungomare — "the most beautiful kilometer in Italy." Take an optional day trip to the Aspromonte foothills.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Day 6: Aspromonte & Bova</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Drive up into the wild mountains of Aspromonte. Visit Bova, the ancient capital of the Greek-Calabrian community, where some elders still speak Griko.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Day 7: Gerace & the Ionian Coast</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>End your trip with the medieval gem of Gerace and its magnificent Norman cathedral, then relax on the long sandy beaches of the Ionian coast.</p>
<!-- /wp:paragraph -->',
	array( $cat_practical, $cat_destinations ),
	'draft'
);

boc_create_post(
	'What is \'Nduja? The Ultimate Guide to Calabria\'s Famous Spicy Spread',
	'what-is-nduja-guide',
	'<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">\'Nduja (pronounced en-DOO-ya) is a fiery, spreadable pork salami from Spilinga in Calabria. It has taken the culinary world by storm — here\'s everything you need to know about it.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Origins</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>\'Nduja originated in the small town of Spilinga in the province of Vibo Valentia. The name likely derives from the French "andouille" (sausage), brought to Calabria during the Napoleonic period. But the Calabrians made it uniquely their own by adding generous amounts of local peperoncino.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">How it\'s made</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Traditional \'nduja is made from pork — including head meat and fat — mixed with a large proportion of Calabrian hot peppers (peperoncino). The mixture is stuffed into a natural casing and smoked, then aged for several months. The result is a soft, spreadable, intensely spicy and smoky salami.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">How to eat it</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The simplest way is spread on crusty bread — the heat hits you first, then the rich pork flavor follows. Calabrians also melt it into pasta sauces, spread it on pizza, stir it into risotto, or use it to flavor beans and vegetable dishes.</p>
<!-- /wp:paragraph -->',
	array( $cat_cuisine ),
	'draft'
);

boc_create_post(
	'Chianalea di Scilla: Italy\'s Hidden "Little Venice"',
	'chianalea-scilla-little-venice',
	'<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Forget the crowds of Venice or Cinque Terre. In the far south of Italy, a tiny fishing quarter clings to the rocks where the Tyrrhenian Sea meets the Strait of Messina. Welcome to Chianalea.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Chianalea is the ancient fishing quarter of Scilla, a town named after the mythological sea monster from Homer\'s Odyssey. Houses here are built directly on the rocks, with the sea literally lapping at their foundations. Narrow alleys wind between colorful buildings, fishing boats are pulled up on tiny patches of beach, and the scent of grilled swordfish fills the air.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>What makes Chianalea special is its authenticity. This isn\'t a restored tourist village — it\'s a living, breathing fishing community that has existed for centuries. Fishermen still set out at dawn to catch swordfish using traditional methods, and the restaurants here serve what was caught that morning.</p>
<!-- /wp:paragraph -->',
	array( $cat_destinations, $cat_hidden_gems ),
	'draft'
);

// ═══════════════════════════════════════
// 4. WORDPRESS SETTINGS
// ═══════════════════════════════════════
echo "\n═══ CONFIGURING SETTINGS ═══\n\n";

// Set homepage as static front page
if ( $home_id ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_id );
	echo "Set homepage as static front page\n";
}

// Update site title and tagline
update_option( 'blogname', 'Best of Calabria' );
update_option( 'blogdescription', 'The Ultimate Guide to Southern Italy' );
echo "Updated site title: Best of Calabria\n";
echo "Updated tagline: The Ultimate Guide to Southern Italy\n";

// Set permalink structure
update_option( 'permalink_structure', '/%postname%/' );
echo "Set permalinks to: /%postname%/\n";

// Mark as imported
update_option( 'boc_content_imported', true );

echo "\n═══════════════════════════════════\n";
echo "DONE! All content has been created.\n";
echo "═══════════════════════════════════\n\n";
echo "Next steps:\n";
echo "1. DELETE this script from your server!\n";
echo "2. Go to Settings > Permalinks and click Save (to flush rewrite rules)\n";
echo "3. Install Polylang plugin for multilingual support\n";
echo "4. Add featured images to pages\n";
echo "5. Edit draft posts and publish them when ready\n";
echo '</pre>';
