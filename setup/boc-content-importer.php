<?php
/**
 * Plugin Name: Best of Calabria - Content Importer
 * Description: Import hierarchical pages, categories, posts. Tools > BOC Import.
 * Version: 4.0.0
 * Author: SibilianSpirit
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', function () {
	add_management_page( 'BOC Import', 'BOC Import', 'manage_options', 'boc-import', 'boc_import_page' );
} );

function boc_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );

	echo '<div class="wrap"><h1>Best of Calabria — Content Importer v4</h1>';

	if ( isset( $_POST['boc_reset'] ) && check_admin_referer( 'boc_nonce' ) ) {
		boc_reset_content();
	}
	if ( isset( $_POST['boc_import'] ) && check_admin_referer( 'boc_nonce' ) ) {
		boc_run_import();
	}
	if ( isset( $_POST['boc_import_pl'] ) && check_admin_referer( 'boc_nonce' ) ) {
		boc_run_import_pl();
	}

	// Diagnostics
	echo '<h2>Diagnostics</h2><div style="background:#f5f5f5;padding:15px;font-family:monospace;font-size:13px">';
	$theme = wp_get_theme();
	echo 'Theme: <b>' . esc_html( $theme->get('Name') ) . '</b> v' . esc_html( $theme->get('Version') ) . '<br>';
	echo 'Theme dir: ' . esc_html( get_template_directory() ) . '<br>';
	$pages = wp_count_posts('page'); $posts = wp_count_posts('post');
	echo 'Pages: ' . $pages->publish . ' published, ' . $pages->draft . ' drafts<br>';
	echo 'Posts: ' . $posts->publish . ' published, ' . $posts->draft . ' drafts<br>';
	$cats = get_categories(array('hide_empty'=>false));
	echo 'Categories: ' . count($cats) . '<br>';
	echo '</div>';

	echo '<form method="post" style="margin:20px 0">';
	wp_nonce_field( 'boc_nonce' );
	echo '<h2>Step 1: Reset</h2><p>Deletes ALL pages, posts, categories.</p>';
	echo '<button type="submit" name="boc_reset" value="1" class="button" onclick="return confirm(\'Delete ALL?\')">Reset All Content</button>';
	echo '<h2 style="margin-top:20px">Step 2: Import EN</h2><p>Creates ~50 English pages, categories, sample posts, configures settings.</p>';
	echo '<button type="submit" name="boc_import" value="1" class="button button-primary button-hero">Import EN Content</button>';
	echo '<h2 style="margin-top:20px">Step 3: Import PL</h2><p>Creates ~50 Polish pages under /pl/ parent. Run AFTER English import.</p>';
	echo '<button type="submit" name="boc_import_pl" value="1" class="button button-primary button-hero">Import PL Content</button>';
	echo '</form></div>';
}

function boc_reset_content() {
	echo '<pre>';
	foreach ( array('page','post') as $type ) {
		$items = get_posts(array('post_type'=>$type,'numberposts'=>-1,'post_status'=>'any'));
		foreach ($items as $p) { wp_delete_post($p->ID, true); echo "Deleted {$type}: {$p->post_title}\n"; }
	}
	foreach (get_categories(array('hide_empty'=>false)) as $c) {
		if ($c->slug !== 'uncategorized') { wp_delete_term($c->term_id,'category'); echo "Deleted cat: {$c->name}\n"; }
	}
	// Clear WP template/navigation cache (for block themes or hybrid)
	foreach (array('wp_template','wp_template_part','wp_navigation','wp_global_styles') as $cpt) {
		foreach (get_posts(array('post_type'=>$cpt,'numberposts'=>-1,'post_status'=>'any')) as $t) {
			wp_delete_post($t->ID, true);
		}
	}
	wp_cache_flush();
	delete_option('boc_content_imported');
	echo "Done.\n</pre>";
}

// ═══════════════════════════════════════════════════════════════
// HTML BOX HELPERS
// ═══════════════════════════════════════════════════════════════

function boc_box_attractions($items) {
	$html = '<div class="boc-attractions-box">';
	$html .= '<h2 class="boc-section-title"><span class="boc-icon">🏛️</span> Główne atrakcje</h2>';
	$html .= '<div class="boc-cards-grid">';
	foreach ($items as $i => $it) {
		$html .= '<a href="' . $it['href'] . '" class="boc-attraction-card">';
		$html .= '<div class="boc-attraction-num">' . ($i+1) . '</div>';
		$html .= '<div><h3>' . $it['title'] . '</h3>';
		if (!empty($it['desc'])) $html .= '<p>' . $it['desc'] . '</p>';
		$html .= '</div></a>';
	}
	$html .= '</div></div>';
	return $html;
}

function boc_box_transport($items) {
	$html = '<div class="boc-transport-box">';
	$html .= '<h2 class="boc-section-title"><span class="boc-icon">🗺️</span> Jak dojechać</h2>';
	$html .= '<div class="boc-transport-grid">';
	foreach ($items as $it) {
		$html .= '<div class="boc-transport-item">';
		$html .= '<span class="boc-transport-emoji">' . $it['emoji'] . '</span>';
		$html .= '<strong>' . $it['label'] . '</strong>';
		$html .= '<p>' . $it['desc'] . '</p>';
		$html .= '</div>';
	}
	$html .= '</div></div>';
	return $html;
}

function boc_box_nearby($items) {
	$html = '<div class="boc-nearby-box">';
	$html .= '<h2 class="boc-section-title"><span class="boc-icon">📍</span> W pobliżu</h2>';
	$html .= '<div class="boc-nearby-grid">';
	foreach ($items as $it) {
		$html .= '<a href="' . $it['href'] . '" class="boc-nearby-pill">';
		$html .= $it['name'];
		if (!empty($it['dist'])) $html .= ' <span class="boc-dist">· ' . $it['dist'] . '</span>';
		$html .= '</a>';
	}
	$html .= '</div></div>';
	return $html;
}

function boc_box_info($items) {
	$html = '<div class="boc-info-box">';
	$html .= '<h2 class="boc-section-title"><span class="boc-icon">ℹ️</span> Informacje praktyczne</h2>';
	$html .= '<table class="boc-info-table"><tbody>';
	foreach ($items as $it) {
		$html .= '<tr><td>' . $it['emoji'] . '</td>';
		$html .= '<td>' . $it['label'] . '</td>';
		$html .= '<td>' . $it['value'] . '</td></tr>';
	}
	$html .= '</tbody></table></div>';
	return $html;
}

function boc_back_link($href, $label) {
	return '<a href="' . $href . '" class="boc-back-link">' . $label . '</a>';
}

function boc_faq_box($faqs) {
	$html = '<div class="boc-faq-box">';
	$html .= '<h2 class="boc-section-title"><span class="boc-icon">❓</span> Często zadawane pytania</h2>';
	foreach ($faqs as $faq) {
		$html .= '<details><summary>' . $faq['q'] . '</summary><div><p>' . $faq['a'] . '</p></div></details>';
	}
	$html .= '</div>';
	return $html;
}

// EN versions (same structure, different title labels)
function boc_box_attractions_en($items) {
	$html = '<div class="boc-attractions-box">';
	$html .= '<h2 class="boc-section-title"><span class="boc-icon">🏛️</span> Top Attractions</h2>';
	$html .= '<div class="boc-cards-grid">';
	foreach ($items as $i => $it) {
		$html .= '<a href="' . $it['href'] . '" class="boc-attraction-card">';
		$html .= '<div class="boc-attraction-num">' . ($i+1) . '</div>';
		$html .= '<div><h3>' . $it['title'] . '</h3>';
		if (!empty($it['desc'])) $html .= '<p>' . $it['desc'] . '</p>';
		$html .= '</div></a>';
	}
	$html .= '</div></div>';
	return $html;
}

function boc_box_transport_en($items) {
	$html = '<div class="boc-transport-box">';
	$html .= '<h2 class="boc-section-title"><span class="boc-icon">🗺️</span> Getting There</h2>';
	$html .= '<div class="boc-transport-grid">';
	foreach ($items as $it) {
		$html .= '<div class="boc-transport-item">';
		$html .= '<span class="boc-transport-emoji">' . $it['emoji'] . '</span>';
		$html .= '<strong>' . $it['label'] . '</strong>';
		$html .= '<p>' . $it['desc'] . '</p>';
		$html .= '</div>';
	}
	$html .= '</div></div>';
	return $html;
}

function boc_box_nearby_en($items) {
	$html = '<div class="boc-nearby-box">';
	$html .= '<h2 class="boc-section-title"><span class="boc-icon">📍</span> Nearby</h2>';
	$html .= '<div class="boc-nearby-grid">';
	foreach ($items as $it) {
		$html .= '<a href="' . $it['href'] . '" class="boc-nearby-pill">';
		$html .= $it['name'];
		if (!empty($it['dist'])) $html .= ' <span class="boc-dist">· ' . $it['dist'] . '</span>';
		$html .= '</a>';
	}
	$html .= '</div></div>';
	return $html;
}

function boc_box_info_en($items) {
	$html = '<div class="boc-info-box">';
	$html .= '<h2 class="boc-section-title"><span class="boc-icon">ℹ️</span> Practical Info</h2>';
	$html .= '<table class="boc-info-table"><tbody>';
	foreach ($items as $it) {
		$html .= '<tr><td>' . $it['emoji'] . '</td>';
		$html .= '<td>' . $it['label'] . '</td>';
		$html .= '<td>' . $it['value'] . '</td></tr>';
	}
	$html .= '</tbody></table></div>';
	return $html;
}

// ═══════════════════════════════════════════════════════════════
// ENGLISH CONTENT
// ═══════════════════════════════════════════════════════════════

function boc_run_import() {
	echo '<pre style="background:#f5f5f5;padding:15px;max-height:500px;overflow:auto">';

	$p = function($title, $slug, $content='', $parent=0) {
		$e = get_page_by_path($slug);
		if ($e) { return $e->ID; }
		$id = wp_insert_post(array('post_title'=>$title,'post_name'=>$slug,'post_content'=>$content,
			'post_status'=>'publish','post_type'=>'page','post_parent'=>$parent));
		if (is_wp_error($id)) { echo "! {$slug}\n"; return 0; }
		echo "+ {$title}\n"; return $id;
	};

	// ═══ HOMEPAGE ═══
	echo "--- HOMEPAGE ---\n";
	$home = $p('Home','home','<h2>Welcome to Best of Calabria</h2>
<p>The ultimate guide to southern Italy\'s best-kept secret. Discover stunning beaches, wild mountains, ancient Greek heritage, and the most authentic Italian food.</p>

<h2>Featured Destinations</h2>
<p>Explore the most beautiful places in Calabria — from the cliff-top town of Tropea to the ancient streets of Reggio Calabria.</p>

<h3><a href="/destinations/tropea/">Tropea</a></h3>
<p>Crystal-clear waters and the iconic Santa Maria dell\'Isola church perched on a rocky outcrop.</p>

<h3><a href="/destinations/reggio-calabria/">Reggio Calabria</a></h3>
<p>Home to the legendary Bronzi di Riace and the Lungomare — "the most beautiful kilometer in Italy."</p>

<h3><a href="/destinations/scilla/">Scilla</a></h3>
<p>The Chianalea fishing quarter, known as "Little Venice of the South."</p>

<h3><a href="/destinations/pizzo/">Pizzo</a></h3>
<p>Birthplace of tartufo gelato with a mysterious cave church carved from rock.</p>

<h2>Explore by Category</h2>
<ul>
<li><a href="/destinations/">All Destinations</a> — Cities, towns, and villages</li>
<li><a href="/nature/">Nature & Outdoors</a> — National parks, beaches, mountains</li>
<li><a href="/cuisine/">Calabrian Cuisine</a> — Food, wine, recipes</li>
<li><a href="/culture/">Culture & History</a> — 3,000 years of heritage</li>
<li><a href="/practical/">Plan Your Trip</a> — Getting there, accommodation, itineraries</li>
</ul>');

	// ═══ DESTINATIONS ═══
	echo "\n--- DESTINATIONS ---\n";
	$dest = $p('Destinations','destinations','<p>From ancient Greek colonies to cliff-top villages above turquoise waters — explore the best places Calabria has to offer.</p>

<h2>Tyrrhenian Coast</h2>
<ul>
<li><a href="/destinations/tropea/">Tropea</a> — The jewel of the Tyrrhenian coast</li>
<li><a href="/destinations/pizzo/">Pizzo</a> — Birthplace of tartufo gelato</li>
<li><a href="/destinations/scilla/">Scilla</a> — Homer\'s mythological fishing village</li>
</ul>

<h2>Metropolitan Reggio</h2>
<ul>
<li><a href="/destinations/reggio-calabria/">Reggio Calabria</a> — The capital, Bronzi di Riace</li>
<li><a href="/destinations/bova/">Bova</a> — Greek-speaking mountain village</li>
</ul>

<h2>Inland & Ionian</h2>
<ul>
<li><a href="/destinations/gerace/">Gerace</a> — Norman cathedral, medieval town</li>
<li><a href="/destinations/stilo/">Stilo</a> — Byzantine Cattolica</li>
<li><a href="/destinations/cosenza/">Cosenza</a> — The Athens of Calabria</li>
<li><a href="/destinations/catanzaro/">Catanzaro</a> — Regional capital</li>
<li><a href="/destinations/locri/">Locri</a> — Ancient Greek archaeological site</li>
</ul>');

	// ─── Reggio Calabria + attractions ───
	$reggio_attractions = boc_box_attractions_en(array(
		array('href'=>'/destinations/reggio-calabria/bronzi-di-riace/','title'=>'Bronzi di Riace','desc'=>'Two legendary Greek bronze warriors from 450 BC'),
		array('href'=>'/destinations/reggio-calabria/lungomare/','title'=>'Lungomare Falcomata','desc'=>'"The most beautiful kilometer in Italy"'),
		array('href'=>'/destinations/reggio-calabria/museo-nazionale/','title'=>'Museo Nazionale','desc'=>'World-class collection of Magna Graecia artifacts'),
		array('href'=>'/destinations/reggio-calabria/arena-dello-stretto/','title'=>'Arena dello Stretto','desc'=>'Open-air amphitheater with views of Sicily'),
	));
	$reggio_transport = boc_box_transport_en(array(
		array('emoji'=>'✈️','label'=>'By Air','desc'=>'Reggio Calabria Airport (REG) — domestic flights from Rome, Milan'),
		array('emoji'=>'🚂','label'=>'By Train','desc'=>'High-speed Frecciarossa to Reggio from Rome (~4h) and Milan (~6h)'),
		array('emoji'=>'⛴️','label'=>'By Ferry','desc'=>'Ferries to Messina, Sicily every 20 minutes from Villa San Giovanni'),
		array('emoji'=>'🚗','label'=>'By Car','desc'=>'A2 motorway from Naples — approximately 5h drive'),
	));
	$reggio_nearby = boc_box_nearby_en(array(
		array('href'=>'/destinations/scilla/','name'=>'Scilla','dist'=>'20 min'),
		array('href'=>'/destinations/bova/','name'=>'Bova','dist'=>'45 min'),
		array('href'=>'/nature/aspromonte/','name'=>'Aspromonte National Park','dist'=>'30 min'),
		array('href'=>'/destinations/gerace/','name'=>'Gerace','dist'=>'1h'),
	));
	$reggio = $p('Reggio Calabria','reggio-calabria','<p>The capital of Calabria\'s metropolitan area sits at the very tip of Italy\'s boot, separated from Sicily by the narrow Strait of Messina. Home to the world-famous Bronzi di Riace and one of the most beautiful waterfronts in Italy.</p>
' . $reggio_attractions . $reggio_transport . $reggio_nearby, $dest);

	$p('Bronzi di Riace','bronzi-di-riace','<p>The Bronzi di Riace (Riace Bronzes) are two full-size Greek bronze statues of naked warriors, cast around 450 BC. Discovered by a recreational diver in the Ionian Sea near Riace in 1972, they are considered among the finest examples of ancient Greek sculpture ever found.</p>

<h2>History</h2>
<p>The statues were likely thrown overboard from a Roman ship, possibly during a storm, while being transported from Greece to Rome. They spent over 2,000 years on the seabed before their accidental discovery.</p>

<h2>What to See</h2>
<p>The bronzes are displayed in a dedicated climate-controlled room at the Museo Nazionale della Magna Grecia. Standing nearly 2 meters tall, they depict a younger and older warrior with extraordinary anatomical detail.
' . boc_box_info_en(array(
		array('emoji'=>'📍','label'=>'Location','value'=>'Museo Nazionale della Magna Grecia, Piazza De Nava, Reggio Calabria'),
		array('emoji'=>'🕐','label'=>'Hours','value'=>'Tue–Sun 9:00–20:00 (closed Monday)'),
		array('emoji'=>'🎫','label'=>'Tickets','value'=>'€8 full price · €4 reduced'),
		array('emoji'=>'🌐','label'=>'Website','value'=>'<a href="https://museoarcheologicoreggiocalabria.it" target="_blank">museoarcheologicoreggiocalabria.it</a>'),
	)) . boc_back_link('/destinations/reggio-calabria/','Back to Reggio Calabria') . '</p>', $reggio);

	$p('Lungomare Falcomata','lungomare','<p>The Lungomare Falcomata is Reggio Calabria\'s stunning seafront promenade, stretching for over a kilometer along the Strait of Messina. The Italian poet Gabriele D\'Annunzio called it "the most beautiful kilometer in Italy" — and it\'s hard to argue.</p>

<h2>What to See</h2>
<p>Walk along the promenade with views of Mount Etna and Sicily across the strait. At sunset, the light plays tricks creating the famous "Fata Morgana" mirage. Along the way you\'ll find Art Nouveau buildings, sculptures, exotic plants, and several cafes.</p>

<p><a href="/destinations/reggio-calabria/">← Back to Reggio Calabria</a></p>', $reggio);

	$p('Museo Nazionale della Magna Grecia','museo-nazionale','<p>One of Italy\'s most important archaeological museums, housing an extraordinary collection of artifacts from the Greek colonies of Magna Graecia. The star attractions are the Bronzi di Riace.</p>

<h2>Practical Info</h2>
<p><strong>Address:</strong> Piazza De Nava 26, Reggio Calabria<br>
<strong>Hours:</strong> Tue-Sun 9:00-20:00<br>
<strong>Tickets:</strong> €8</p>

<p><a href="/destinations/reggio-calabria/">← Back to Reggio Calabria</a></p>', $reggio);

	$p('Arena dello Stretto','arena-dello-stretto','<p>A modern open-air amphitheater built on the Reggio Calabria waterfront, with spectacular views of the Strait of Messina and Sicily. Hosts concerts and cultural events during summer.</p>

<p><a href="/destinations/reggio-calabria/">← Back to Reggio Calabria</a></p>', $reggio);

	// ─── Tropea + attractions ───
	$tropea = $p('Tropea','tropea','<p>Perched on a cliff 50 meters above the Tyrrhenian Sea, Tropea is Calabria\'s most iconic destination. Crystal-clear turquoise waters, the dramatic Santa Maria dell\'Isola church on a rocky outcrop, and the famous red onion — Tropea has it all.</p>

<h2>Top Attractions</h2>
<ul>
<li><a href="/destinations/tropea/santa-maria-dell-isola/">Santa Maria dell\'Isola</a> — The iconic cliff-top church</li>
<li><a href="/destinations/tropea/tropea-beaches/">Tropea Beaches</a> — White sand and turquoise water</li>
<li><a href="/destinations/tropea/red-onion-festival/">Red Onion Festival</a> — The famous cipolla rossa</li>
</ul>

<h2>Nearby</h2>
<ul>
<li><a href="/destinations/pizzo/">Pizzo</a> — 30 min north (tartufo gelato!)</li>
<li><a href="/nature/capo-vaticano/">Capo Vaticano</a> — 15 min south (top 10 beach worldwide)</li>
</ul>', $dest);

	$p('Santa Maria dell\'Isola','santa-maria-dell-isola','<p>The undisputed symbol of Tropea and one of Italy\'s most photographed landmarks. This medieval Benedictine sanctuary sits atop a dramatic rocky islet connected to the mainland, with the turquoise Tyrrhenian Sea on all sides.</p>

<h2>Visiting</h2>
<p>Climb the stone staircase carved into the rock for panoramic views. The church is a simple, whitewashed interior — the real attraction is the setting. Best photographed from the Tropea belvedere at sunset.</p>

<p><a href="/destinations/tropea/">← Back to Tropea</a></p>', $tropea);

	$p('Tropea Beaches','tropea-beaches','<p>Tropea\'s beaches are consistently rated among the best in Italy. The main beach stretches below the old town cliff, with fine white sand and crystal-clear turquoise water.</p>

<h2>Best Beaches</h2>
<p><strong>Spiaggia della Rotonda</strong> — The main beach below the old town. <strong>Spiaggia del Cannone</strong> — More secluded, next to the Isola. <strong>Mare Piccolo</strong> — Family-friendly with shallow water.</p>

<p><a href="/destinations/tropea/">← Back to Tropea</a></p>', $tropea);

	$p('Red Onion Festival','red-onion-festival','<p>The Tropea red onion (cipolla rossa di Tropea) is famous throughout Italy for its sweet, mild flavor. Every summer the town celebrates with the Sagra della Cipolla Rossa — a festival of onion-based dishes, live music, and local culture.</p>

<p><a href="/destinations/tropea/">← Back to Tropea</a></p>', $tropea);

	// ─── Scilla + attractions ───
	$scilla = $p('Scilla','scilla','<p>Named after the mythological sea monster from Homer\'s Odyssey, Scilla guards the northern entrance of the Strait of Messina. Its ancient fishing quarter Chianalea — "Little Venice of the South" — is one of Italy\'s most charming neighborhoods.</p>

<h2>Top Attractions</h2>
<ul>
<li><a href="/destinations/scilla/chianalea/">Chianalea</a> — The legendary fishing quarter</li>
<li><a href="/destinations/scilla/castello-ruffo/">Castello Ruffo</a> — Medieval castle with panoramic views</li>
<li><a href="/destinations/scilla/swordfish-tradition/">Swordfish Tradition</a> — Ancient fishing heritage</li>
</ul>

<h2>Nearby</h2>
<ul>
<li><a href="/destinations/reggio-calabria/">Reggio Calabria</a> — 20 min south</li>
<li><a href="/nature/costa-viola/">Costa Viola</a> — The Purple Coast continues north</li>
</ul>', $dest);

	$p('Chianalea','chianalea','<p>Chianalea is the ancient fishing quarter of Scilla, where colorful houses are built directly on the rocks with the sea lapping at their foundations. Narrow alleys, fishing boats on tiny beaches, and the scent of grilled swordfish — this is the real, unchanged Italy.</p>

<p>The name means "flat water" in the local dialect, referring to the calm waters of the small cove. Restaurants serve what was caught that morning. Come at sunset for unforgettable views across the Strait to Sicily.</p>

<p><a href="/destinations/scilla/">← Back to Scilla</a></p>', $scilla);

	$p('Castello Ruffo','castello-ruffo','<p>Perched on the rocky promontory between Chianalea and the main beach, Castello Ruffo has guarded the Strait of Messina since the 5th century. Today it hosts exhibitions and offers panoramic views of Calabria, Sicily, and the Aeolian Islands.</p>

<p><a href="/destinations/scilla/">← Back to Scilla</a></p>', $scilla);

	$p('Swordfish Tradition','swordfish-tradition','<p>Scilla has a centuries-old tradition of swordfish hunting in the Strait of Messina. Fishermen still use traditional "passerelle" — tall lookout towers on boats — to spot the fish. The annual Sagra del Pesce Spada celebrates this heritage with fresh swordfish prepared every possible way.</p>

<p><a href="/destinations/scilla/">← Back to Scilla</a></p>', $scilla);

	// ─── Pizzo + attractions ───
	$pizzo = $p('Pizzo','pizzo','<p>This charming seaside town on the Gulf of Sant\'Eufemia is the birthplace of tartufo — Italy\'s most famous gelato. Beyond ice cream, Pizzo offers the mysterious Piedigrotta cave church and a picturesque historic center.</p>

<h2>Top Attractions</h2>
<ul>
<li><a href="/destinations/pizzo/piedigrotta-church/">Piedigrotta Church</a> — Carved entirely from rock</li>
<li><a href="/destinations/pizzo/tartufo-gelato/">Tartufo Gelato</a> — The original, born here</li>
<li><a href="/destinations/pizzo/castello-murat/">Castello Murat</a> — Where Napoleon\'s general met his end</li>
</ul>', $dest);

	$p('Piedigrotta Church','piedigrotta-church','<p>A church carved entirely from tufa rock by shipwrecked sailors in the 17th century. Inside, life-size stone figures depict biblical scenes in an underground grotto by the sea. One of Calabria\'s most unique and mysterious sites.</p>

<p><a href="/destinations/pizzo/">← Back to Pizzo</a></p>', $pizzo);

	$p('Tartufo Gelato','tartufo-gelato','<p>Tartufo di Pizzo was invented here in the 1950s at Bar Dante on the main square. It\'s a ball of hazelnut and chocolate gelato with a molten chocolate center, dusted with cocoa. Now famous worldwide, but nothing beats eating the original on Piazza della Repubblica overlooking the sea.</p>

<p><a href="/destinations/pizzo/">← Back to Pizzo</a></p>', $pizzo);

	$p('Castello Murat','castello-murat','<p>This 15th-century Aragonese castle is where Joachim Murat, Napoleon\'s brother-in-law and King of Naples, was captured and executed in 1815 after his failed attempt to reclaim the throne. The castle now houses a museum about Murat and offers sea views.</p>

<p><a href="/destinations/pizzo/">← Back to Pizzo</a></p>', $pizzo);

	// ─── Bova ───
	$bova = $p('Bova','bova','<p>One of Italy\'s "Borghi più belli" (most beautiful villages), Bova is the cultural capital of the Grecanici — descendants of ancient Greek settlers who still speak Griko, a language rooted in ancient Greek. Perched at 820m in the Aspromonte foothills, it\'s a living museum of Magna Graecia heritage.</p>

<h2>What to See</h2>
<ul>
<li><a href="/destinations/bova/grecanico-heritage/">Grecanico Heritage</a> — The living Greek tradition</li>
</ul>

<h2>Nearby</h2>
<ul>
<li><a href="/destinations/reggio-calabria/">Reggio Calabria</a> — 45 min drive</li>
<li><a href="/nature/aspromonte/">Aspromonte</a> — You\'re already in it</li>
</ul>', $dest);

	$p('Grecanico Heritage','grecanico-heritage','<p>The Grecanici are the last remnants of the Greek-speaking population that once inhabited much of southern Calabria. In Bova, some elders still speak Griko, and the town celebrates its heritage with bilingual street signs, Greek festivals, and the annual Paleariza music festival.</p>

<p><a href="/destinations/bova/">← Back to Bova</a></p>', $bova);

	// ─── Remaining cities (without sub-attractions for now) ───
	$p('Gerace','gerace','<p>Home to the largest Norman cathedral in Calabria, Gerace is a perfectly preserved medieval town perched on a rocky plateau. Its labyrinth of narrow streets reveals Byzantine churches, noble palaces, and panoramic views over the Ionian coast.</p>', $dest);
	$p('Stilo','stilo','<p>Famous for the Cattolica di Stilo, a tiny 9th-century Byzantine church and UNESCO World Heritage candidate. Five domes crown this brick structure nestled against Monte Consolino, representing the deep Byzantine influence on Calabrian culture.</p>', $dest);
	$cosenza = $p('Cosenza','cosenza','<p>The "Athens of Calabria" — a university city where a vibrant old town meets contemporary culture. The centro storico along the Crati River is one of the best-preserved medieval centers in southern Italy. Don\'t miss the MAB open-air museum and the stunning Telesio theater.</p>

<h2>Nearby</h2>
<ul>
<li><a href="/nature/sila/">Sila National Park</a> — Gateway to the great forest</li>
</ul>', $dest);
	$p('Catanzaro','catanzaro','<p>The regional capital of Calabria sits on a hilltop between the Tyrrhenian and Ionian seas. Known for spectacular belvedere viewpoints, a silk-weaving tradition dating back centuries, and as a gateway to the beautiful Ionian coast.</p>', $dest);
	$p('Locri','locri','<p>Home to the archaeological site of Locri Epizefiri, one of the most important Greek colonies in Magna Graecia. Founded in the 7th century BC, the ruins include a Greek theater, temples, and a museum with extraordinary terracotta artifacts.</p>', $dest);

	// ═══ NATURE ═══
	echo "\n--- NATURE ---\n";
	$nature = $p('Nature','nature','<p>Three national parks, 800 km of coastline, wild mountains, and pristine beaches — Calabria is a paradise for nature lovers.</p>
<ul>
<li><a href="/nature/aspromonte/">Aspromonte National Park</a></li>
<li><a href="/nature/sila/">Sila National Park</a></li>
<li><a href="/nature/pollino/">Pollino National Park</a></li>
<li><a href="/nature/beaches/">Best Beaches</a></li>
<li><a href="/nature/capo-vaticano/">Capo Vaticano</a></li>
<li><a href="/nature/costa-viola/">Costa Viola</a></li>
</ul>');
	$p('Aspromonte National Park','aspromonte','<p>Wild mountains at the toe of Italy\'s boot. Ancient forests, dramatic waterfalls like the Cascate di Maesano, and the last remnants of Greek-speaking villages. Aspromonte means "white mountain" — and its peaks reach nearly 2,000m.</p>', $nature);
	$p('Sila National Park','sila','<p>The "Great Forest of Italy" — vast highland plateaus, pristine lakes (Arvo, Ampollino, Cecita), and Calabria\'s best skiing in winter. In summer, hiking through centuries-old Laricio pine forests.</p>', $nature);
	$p('Pollino National Park','pollino','<p>Italy\'s largest national park, shared with Basilicata. Home to the ancient Pino Loricato (Bosnian pine), dramatic canyons, and world-class rafting on the Lao River.</p>', $nature);
	$p('Best Beaches','beaches','<p>A guide to Calabria\'s best beaches — from Tropea\'s white sand to the wild Ionian shores.</p>
<h2>Tyrrhenian Coast (West)</h2>
<p>Tropea, Capo Vaticano, Pizzo, Scilla — turquoise water, dramatic cliffs.</p>
<h2>Ionian Coast (East)</h2>
<p>Long sandy beaches, less crowded, warmer water. Soverato, Le Castella, Capo Rizzuto.</p>', $nature);
	$p('Capo Vaticano','capo-vaticano','<p>Granite cliffs plunging into crystal-clear water. Rated among the top 10 beaches in the world. 15 minutes south of Tropea, with hidden coves accessible by boat or steep paths.</p>', $nature);
	$p('Costa Viola','costa-viola','<p>The "Purple Coast" from Scilla to Palmi — dramatic cliffs, hidden coves, and legendary sunsets over the Aeolian Islands. Named for the violet hues the sea takes at sunset.</p>', $nature);

	// ═══ CUISINE ═══
	echo "\n--- CUISINE ---\n";
	$cuisine = $p('Cuisine','cuisine','<p>Calabrian cuisine is bold, spicy, and deeply rooted in tradition. From the fiery \'nduja to fresh swordfish, hand-rolled pasta, and world-class bergamot.</p>
<ul>
<li><a href="/cuisine/nduja/">\'Nduja</a> — The famous spicy spread</li>
<li><a href="/cuisine/bergamot/">Bergamot</a> — Calabria\'s unique citrus</li>
<li><a href="/cuisine/calabrian-wine/">Calabrian Wine</a></li>
<li><a href="/cuisine/fileja-pasta/">Fileja Pasta</a></li>
<li><a href="/cuisine/street-food/">Street Food</a></li>
</ul>');
	$p('\'Nduja','nduja','<p>A fiery, spreadable pork salami from Spilinga. Made with pork and a generous proportion of Calabrian peperoncino, \'nduja has taken the culinary world by storm. Spread on bread, melt into pasta, or add to pizza.</p>', $cuisine);
	$p('Bergamot','bergamot','<p>95% of the world\'s bergamot grows in a narrow strip along Calabria\'s Ionian coast near Reggio. This unique citrus is used in Earl Grey tea, perfume, and Calabrian cuisine — from bergamot marmalade to liqueur.</p>', $cuisine);
	$p('Calabrian Wine','calabrian-wine','<p>Calabria\'s wine tradition goes back to the Greeks. Key varieties: Ciro (Italy\'s oldest DOC), Greco di Bianco (ancient sweet wine), Gaglioppo (the main red grape). Emerging quality producers are putting Calabrian wine on the map.</p>', $cuisine);
	$p('Fileja Pasta','fileja-pasta','<p>Hand-rolled pasta made by twisting dough around a thin stick. Typically served with \'nduja ragu, goat sauce, or a simple tomato and ricotta salata. A Calabrian specialty you won\'t find anywhere else.</p>', $cuisine);
	$p('Street Food','street-food','<p>Tartufo di Pizzo (the original!), zeppole (fried dough), grattachecca (shaved ice), cudduraci (Easter cookies), and of course fresh arancini. Calabria\'s street food scene is unpretentious and delicious.</p>', $cuisine);

	// ═══ CULTURE ═══
	echo "\n--- CULTURE ---\n";
	$culture = $p('Culture','culture','<p>From Magna Graecia to the Normans, from Byzantine churches to living Greek-speaking communities — Calabria\'s culture is 3,000 years deep.</p>
<ul>
<li><a href="/culture/magna-graecia/">Magna Graecia</a></li>
<li><a href="/culture/byzantine-heritage/">Byzantine Heritage</a></li>
<li><a href="/culture/traditions-festivals/">Traditions & Festivals</a></li>
</ul>');
	$p('Magna Graecia','magna-graecia','<p>From the 8th century BC, Greek colonists founded cities across southern Calabria — Rhegion (Reggio), Kroton (Crotone), Locri, Sybaris. Their legacy lives on in archaeology, language, and culture.</p>', $culture);
	$p('Byzantine Heritage','byzantine-heritage','<p>After the fall of Rome, Calabria became a stronghold of Byzantine culture for centuries. The Cattolica di Stilo, the Greek monasteries of Aspromonte, and the Codex Purpureus Rossanensis are testaments to this heritage.</p>', $culture);
	$p('Traditions & Festivals','traditions-festivals','<p>The Varia di Palmi (UNESCO heritage), Festa della Madonna della Consolazione in Reggio, Paleariza Greek music festival in Bova, and countless sagre (food festivals) throughout summer.</p>', $culture);

	// ═══ PRACTICAL ═══
	echo "\n--- PRACTICAL ---\n";
	$practical = $p('Practical Info','practical','<p>Everything you need to plan your trip to Calabria.</p>
<ul>
<li><a href="/practical/getting-there/">Getting There</a> — Flights, trains, ferries</li>
<li><a href="/practical/car-rental/">Car Rental</a> — Essential for exploring</li>
<li><a href="/practical/accommodation/">Where to Stay</a></li>
<li><a href="/practical/itinerary-7-days/">7-Day Itinerary</a></li>
</ul>');
	$p('Getting There','getting-there','<p><strong>By Air:</strong> Lamezia Terme (SUF) is the main airport with international flights. Reggio Calabria (REG) has domestic connections. <strong>By Train:</strong> High-speed trains reach Lamezia and Reggio. <strong>By Ferry:</strong> Regular ferries from Sicily (Messina) to Reggio and Villa San Giovanni.</p>', $practical);
	$p('Car Rental','car-rental','<p>A car is essential to explore Calabria properly. Public transport exists but is limited. Rent from Lamezia airport for the best rates. Roads are good along the coast; mountain roads can be winding but scenic.</p>', $practical);
	$p('Where to Stay','accommodation','<p>Options range from luxury seaside hotels to agriturismi (farm stays) in the mountains. Best areas: Tropea coast for beaches, Reggio for city life, Aspromonte for nature. Budget: €50-100/night for good quality accommodation.</p>', $practical);
	$p('7-Day Itinerary','itinerary-7-days','<p><strong>Day 1-2: Tropea & Capo Vaticano</strong> — Cliff-top town, world-class beaches.</p>
<p><strong>Day 3: Pizzo</strong> — Tartufo gelato, Piedigrotta cave church.</p>
<p><strong>Day 4: Scilla</strong> — Chianalea fishing village, sunset over Sicily.</p>
<p><strong>Day 5: Reggio Calabria</strong> — Bronzi di Riace, Lungomare.</p>
<p><strong>Day 6: Aspromonte & Bova</strong> — Wild mountains, Greek-speaking village.</p>
<p><strong>Day 7: Gerace & Ionian Coast</strong> — Norman cathedral, sandy beaches.</p>', $practical);

	// ═══ STATIC PAGES ═══
	echo "\n--- STATIC ---\n";
	$blog = $p('Blog','blog','');
	$p('About','about','<p>Best of Calabria is the ultimate English-language guide to Calabria, southern Italy. We cover destinations, nature, cuisine, culture, and practical information for travelers.</p>');
	$p('Contact','contact','<p>Questions, suggestions, or partnerships? Email us at hello@bestofcalabria.com</p>');
	$p('Privacy Policy','privacy-policy','');
	$p('Partnership','partnership','<p>We offer partnership opportunities for hotels, tour operators, restaurants, and local businesses in Calabria. Contact: partners@bestofcalabria.com</p>');

	// ═══ CATEGORIES ═══
	echo "\n--- CATEGORIES ---\n";
	$mk_cat = function($n,$s) { $e=get_term_by('slug',$s,'category'); if($e) return $e->term_id;
		$r=wp_insert_term($n,'category',array('slug'=>$s)); if(is_wp_error($r)) return 0;
		echo "+ cat: {$n}\n"; return $r['term_id']; };
	$c1=$mk_cat('Destinations','destinations'); $c2=$mk_cat('Nature','nature');
	$c3=$mk_cat('Food & Drink','food-drink'); $c4=$mk_cat('Culture','culture');
	$c5=$mk_cat('Travel Tips','travel-tips'); $c6=$mk_cat('Hidden Gems','hidden-gems');
	$c7=$mk_cat('Itineraries','itineraries');

	// ═══ SAMPLE POSTS ═══
	echo "\n--- POSTS ---\n";
	$mk_post = function($t,$s,$c,$cats) { $e=get_page_by_path($s,OBJECT,'post'); if($e) return;
		wp_insert_post(array('post_title'=>$t,'post_name'=>$s,'post_content'=>$c,
			'post_status'=>'draft','post_type'=>'post','post_category'=>$cats));
		echo "+ draft: {$t}\n"; };

	$mk_post('10 Reasons Why Calabria Should Be Your Next Destination','10-reasons-calabria',
		'<p>While millions flock to Rome and the Amalfi Coast, Calabria remains beautifully unspoiled. Here\'s why.</p>
<h2>1. Beaches that rival the Caribbean</h2><p>Tropea and Capo Vaticano at a fraction of the price.</p>
<h2>2. The best food you\'ve never had</h2><p>Spicy \'nduja, hand-rolled fileja, fresh swordfish.</p>
<h2>3. Three national parks</h2><p>Aspromonte, Sila, Pollino — wild and uncrowded.</p>
<h2>4. 3,000 years of history</h2><p>Greek colonies, Byzantine churches, Norman castles.</p>
<h2>5. Incredibly affordable</h2><p>Half the price of northern Italy.</p>',
		array($c1,$c5));

	$mk_post('The Perfect 7-Day Calabria Itinerary','7-day-calabria-itinerary',
		'<p>One week from Tropea to the toe of the boot.</p>
<h2>Day 1-2: Tropea</h2><p>Santa Maria dell\'Isola, beaches, old town.</p>
<h2>Day 3: Pizzo</h2><p>Tartufo gelato, Piedigrotta church.</p>
<h2>Day 4: Scilla</h2><p>Chianalea, sunset over Sicily.</p>
<h2>Day 5: Reggio</h2><p>Bronzi di Riace, Lungomare.</p>
<h2>Day 6: Aspromonte & Bova</h2><p>Mountains and the Greek village.</p>
<h2>Day 7: Gerace</h2><p>Norman cathedral, Ionian beaches.</p>',
		array($c7,$c1));

	$mk_post('What is \'Nduja? The Complete Guide','what-is-nduja',
		'<p>A fiery spreadable salami from Spilinga that conquered the world.</p>
<h2>Origins</h2><p>From French "andouille", transformed with Calabrian peperoncino.</p>
<h2>How to eat it</h2><p>On bread, in pasta, on pizza, in risotto.</p>',
		array($c3));

	$mk_post('Chianalea: Italy\'s Hidden Little Venice','chianalea-little-venice',
		'<p>A tiny fishing quarter where houses cling to rocks above the sea.</p>
<p>Not a tourist village — a living fishing community. Swordfish caught at dawn, served at lunch.</p>',
		array($c1,$c6));

	$mk_post('Best Beaches in Calabria: A Complete Guide','best-beaches-calabria',
		'<h2>Tyrrhenian Coast</h2><p>Tropea, Capo Vaticano, Pizzo — turquoise water, white sand, dramatic cliffs.</p>
<h2>Ionian Coast</h2><p>Soverato, Le Castella, Capo Rizzuto — long sandy stretches, warm water, fewer crowds.</p>',
		array($c2,$c5));

	// ═══ SETTINGS ═══
	echo "\n--- SETTINGS ---\n";
	if ($home) { update_option('show_on_front','page'); update_option('page_on_front',$home); echo "Homepage set\n"; }
	if ($blog) { update_option('page_for_posts',$blog); echo "Blog page set\n"; }
	update_option('blogname','Best of Calabria');
	update_option('blogdescription','The Ultimate Guide to Southern Italy');
	update_option('permalink_structure','/%postname%/');
	flush_rewrite_rules();
	echo "Settings configured\n\nDONE! " . count(get_posts(array('post_type'=>'page','numberposts'=>-1))) . " pages created.\n</pre>";
}

// ═══════════════════════════════════════════════════════════════
// POLISH CONTENT
// ═══════════════════════════════════════════════════════════════

function boc_run_import_pl() {
	echo '<pre style="background:#f5f5f5;padding:15px;max-height:500px;overflow:auto">';
	echo "=== IMPORTING POLISH CONTENT ===\n\n";

	$p = function($title, $slug, $content='', $parent=0) {
		// For hierarchical pages, build full path for lookup
		$path = $slug;
		if ($parent) {
			$parent_post = get_post($parent);
			if ($parent_post) {
				// Build full path by walking up parents
				$ancestors = array($parent_post->post_name);
				$current = $parent_post;
				while ($current->post_parent) {
					$current = get_post($current->post_parent);
					if ($current) $ancestors[] = $current->post_name;
				}
				$path = implode('/', array_reverse($ancestors)) . '/' . $slug;
			}
		}
		$e = get_page_by_path($path);
		if ($e) { echo "= {$title} (exists)\n"; return $e->ID; }
		$id = wp_insert_post(array('post_title'=>$title,'post_name'=>$slug,'post_content'=>$content,
			'post_status'=>'publish','post_type'=>'page','post_parent'=>$parent));
		if (is_wp_error($id)) { echo "! {$slug}\n"; return 0; }
		echo "+ {$title}\n"; return $id;
	};

	// ═══ PL ROOT ═══
	echo "--- PL ROOT ---\n";
	$pl = $p('Strona główna','pl','<h2>Witaj w Best of Calabria</h2>
<p>Najlepszy przewodnik po południowych Włoszech — najlepiej strzeżonym sekrecie Europy. Odkryj oszałamiające plaże, dzikie góry, starożytne greckie dziedzictwo i najbardziej autentyczną włoską kuchnię.</p>

<h2>Polecane kierunki</h2>
<p>Poznaj najpiękniejsze miejsca Kalabrii — od klifowego miasteczka Tropea po starożytne ulice Reggio Calabria.</p>

<h3><a href="/pl/kierunki/tropea/">Tropea</a></h3>
<p>Krystalicznie czysta woda i kultowy kościół Santa Maria dell\'Isola na skalistym cyplu.</p>

<h3><a href="/pl/kierunki/reggio-calabria/">Reggio Calabria</a></h3>
<p>Dom legendarnych Brązów z Riace i Lungomare — „najpiękniejszy kilometr we Włoszech".</p>

<h3><a href="/pl/kierunki/scilla/">Scilla</a></h3>
<p>Dzielnica rybacka Chianalea, znana jako „Mała Wenecja Południa".</p>

<h3><a href="/pl/kierunki/pizzo/">Pizzo</a></h3>
<p>Ojczyzna lodów tartufo i tajemniczy kościół wykuty w skale.</p>

<h2>Przeglądaj według kategorii</h2>
<ul>
<li><a href="/pl/kierunki/">Wszystkie kierunki</a> — Miasta, miasteczka i wioski</li>
<li><a href="/pl/natura/">Natura i przyroda</a> — Parki narodowe, plaże, góry</li>
<li><a href="/pl/kuchnia/">Kuchnia kalabryjska</a> — Jedzenie, wino, przepisy</li>
<li><a href="/pl/kultura/">Kultura i historia</a> — 3000 lat dziedzictwa</li>
<li><a href="/pl/praktyczne/">Zaplanuj podróż</a> — Dojazd, noclegi, plan podróży</li>
</ul>');

	// ═══ KIERUNKI (DESTINATIONS) ═══
	echo "\n--- KIERUNKI ---\n";
	$dest = $p('Kierunki','kierunki','<p>Od starożytnych greckich kolonii po klifowe wioski nad turkusową wodą — odkryj najlepsze miejsca, jakie Kalabria ma do zaoferowania.</p>

<h2>Wybrzeże Tyrreńskie</h2>
<ul>
<li><a href="/pl/kierunki/tropea/">Tropea</a> — Perła wybrzeża tyrreńskiego</li>
<li><a href="/pl/kierunki/pizzo/">Pizzo</a> — Ojczyzna lodów tartufo</li>
<li><a href="/pl/kierunki/scilla/">Scilla</a> — Mitologiczna wioska rybacka Homera</li>
</ul>

<h2>Metropolia Reggio</h2>
<ul>
<li><a href="/pl/kierunki/reggio-calabria/">Reggio Calabria</a> — Stolica, Brązy z Riace</li>
<li><a href="/pl/kierunki/bova/">Bova</a> — Greckojęzyczna wioska górska</li>
</ul>

<h2>Interior i wybrzeże jońskie</h2>
<ul>
<li><a href="/pl/kierunki/gerace/">Gerace</a> — Normandzka katedra, średniowieczne miasto</li>
<li><a href="/pl/kierunki/stilo/">Stilo</a> — Bizantyjska Cattolica</li>
<li><a href="/pl/kierunki/cosenza/">Cosenza</a> — Ateny Kalabrii</li>
<li><a href="/pl/kierunki/catanzaro/">Catanzaro</a> — Stolica regionu</li>
<li><a href="/pl/kierunki/locri/">Locri</a> — Starożytne greckie stanowisko archeologiczne</li>
</ul>', $pl);

	// ─── Reggio Calabria + atrakcje ───
	$reggio_atrakcje = boc_box_attractions(array(
		array('href'=>'/pl/kierunki/reggio-calabria/bronzy-z-riace/','title'=>'Brązy z Riace','desc'=>'Dwaj legendarni greccy wojownicy z brązu z 450 r. p.n.e.'),
		array('href'=>'/pl/kierunki/reggio-calabria/lungomare/','title'=>'Lungomare Falcomata','desc'=>'„Najpiękniejszy kilometr we Włoszech"'),
		array('href'=>'/pl/kierunki/reggio-calabria/museo-nazionale/','title'=>'Museo Nazionale','desc'=>'Światowej klasy zbiory Magna Graecia'),
		array('href'=>'/pl/kierunki/reggio-calabria/arena-dello-stretto/','title'=>'Arena dello Stretto','desc'=>'Amfiteatr z widokiem na Sycylię'),
	));
	$reggio_dojazd = boc_box_transport(array(
		array('emoji'=>'✈️','label'=>'Samolotem','desc'=>'Lotnisko Reggio Calabria (REG) — loty krajowe z Rzymu i Mediolanu'),
		array('emoji'=>'🚂','label'=>'Pociągiem','desc'=>'Frecciarossa z Rzymu ok. 4h, z Mediolanu ok. 6h'),
		array('emoji'=>'⛴️','label'=>'Promem','desc'=>'Promy do Mesyny (Sycylia) co 20 minut z Villa San Giovanni'),
		array('emoji'=>'🚗','label'=>'Samochodem','desc'=>'Autostrada A2 z Neapolu — ok. 5h jazdy'),
	));
	$reggio_poblizu = boc_box_nearby(array(
		array('href'=>'/pl/kierunki/scilla/','name'=>'Scilla','dist'=>'20 min'),
		array('href'=>'/pl/kierunki/bova/','name'=>'Bova','dist'=>'45 min'),
		array('href'=>'/pl/natura/aspromonte/','name'=>'Park Narodowy Aspromonte','dist'=>'30 min'),
		array('href'=>'/pl/kierunki/gerace/','name'=>'Gerace','dist'=>'1h'),
	));
	$reggio = $p('Reggio Calabria','reggio-calabria','<p>Stolica obszaru metropolitalnego Kalabrii leży na samym czubku włoskiego buta, oddzielona od Sycylii wąską Cieśniną Mesyńską. Dom słynnych na cały świat Brązów z Riace i jednego z najpiękniejszych nadmorskich deptaków we Włoszech.</p>
' . $reggio_atrakcje . $reggio_dojazd . $reggio_poblizu, $dest);

	$p('Brązy z Riace','bronzy-z-riace','<p>Brązy z Riace to dwa pełnowymiarowe greckie posągi z brązu przedstawiające nagich wojowników, odlane około 450 r. p.n.e. Odkryte przez nurka-amatora w Morzu Jońskim koło Riace w 1972 roku, uważane są za jedne z najwspanialszych przykładów starożytnej rzeźby greckiej.</p>

<h2>Historia</h2>
<p>Posągi zostały prawdopodobnie wyrzucone za burtę ze statku rzymskiego, być może podczas burzy, w trakcie transportu z Grecji do Rzymu. Spędziły ponad 2000 lat na dnie morskim przed przypadkowym odkryciem.</p>

<h2>Co zobaczyć</h2>
<p>Brązy eksponowane są w specjalnym klimatyzowanym pomieszczeniu w Museo Nazionale della Magna Grecia. Mając prawie 2 metry wysokości, przedstawiają młodszego i starszego wojownika z niezwykłym detalem anatomicznym.</p>
' . boc_box_info(array(
		array('emoji'=>'📍','label'=>'Lokalizacja','value'=>'Museo Nazionale della Magna Grecia, Piazza De Nava, Reggio Calabria'),
		array('emoji'=>'🕐','label'=>'Godziny','value'=>'Wt–Nd 9:00–20:00 (zamknięte w poniedziałki)'),
		array('emoji'=>'🎫','label'=>'Bilety','value'=>'8€ normalny · 4€ ulgowy'),
		array('emoji'=>'🌐','label'=>'Strona','value'=>'<a href="https://museoarcheologicoreggiocalabria.it" target="_blank">museoarcheologicoreggiocalabria.it</a>'),
	)) . boc_back_link('/pl/kierunki/reggio-calabria/','Powrót do Reggio Calabria'), $reggio);

	$p('Lungomare Falcomata','lungomare','<p>Lungomare Falcomata to oszałamiający nadmorski deptak Reggio Calabria, rozciągający się na ponad kilometr wzdłuż Cieśniny Mesyńskiej. Włoski poeta Gabriele D\'Annunzio nazwał go „najpiękniejszym kilometrem we Włoszech" — i trudno się z tym nie zgodzić.</p>

<h2>Co zobaczyć</h2>
<p>Spaceruj deptakiem z widokiem na Etnę i Sycylię po drugiej stronie cieśniny. O zachodzie słońca światło tworzy słynny miraż „Fata Morgana". Po drodze znajdziesz budynki secesyjne, rzeźby, egzotyczne rośliny i kilka kawiarni.</p>

<p><a href="/pl/kierunki/reggio-calabria/">← Powrót do Reggio Calabria</a></p>', $reggio);

	$p('Museo Nazionale della Magna Grecia','museo-nazionale','<p>Jedno z najważniejszych muzeów archeologicznych we Włoszech, mieszczące niezwykłą kolekcję artefaktów z greckich kolonii Magna Graecia. Główną atrakcją są Brązy z Riace.</p>

<h2>Informacje praktyczne</h2>
<p><strong>Adres:</strong> Piazza De Nava 26, Reggio Calabria<br>
<strong>Godziny:</strong> Wt-Nd 9:00-20:00<br>
<strong>Bilety:</strong> 8€</p>

<p><a href="/pl/kierunki/reggio-calabria/">← Powrót do Reggio Calabria</a></p>', $reggio);

	$p('Arena dello Stretto','arena-dello-stretto','<p>Nowoczesny amfiteatr na wolnym powietrzu zbudowany na nabrzeżu Reggio Calabria, ze spektakularnymi widokami na Cieśninę Mesyńską i Sycylię. Latem odbywa się tu wiele koncertów i wydarzeń kulturalnych.</p>

<p><a href="/pl/kierunki/reggio-calabria/">← Powrót do Reggio Calabria</a></p>', $reggio);

	// ─── Tropea + atrakcje ───
	$tropea = $p('Tropea','tropea','<p>Usytuowana na klifie 50 metrów nad Morzem Tyrreńskim, Tropea jest najbardziej ikonicznym kierunkiem Kalabrii. Krystalicznie czysta turkusowa woda, dramatyczny kościół Santa Maria dell\'Isola na skalistym cyplu i słynna czerwona cebula — Tropea ma wszystko.</p>

<h2>Główne atrakcje</h2>
<ul>
<li><a href="/pl/kierunki/tropea/santa-maria-dell-isola/">Santa Maria dell\'Isola</a> — Kultowy kościół na klifie</li>
<li><a href="/pl/kierunki/tropea/plaze-tropea/">Plaże Tropea</a> — Biały piasek i turkusowa woda</li>
<li><a href="/pl/kierunki/tropea/festiwal-czerwonej-cebuli/">Festiwal Czerwonej Cebuli</a> — Słynna cipolla rossa</li>
</ul>

<h2>W pobliżu</h2>
<ul>
<li><a href="/pl/kierunki/pizzo/">Pizzo</a> — 30 min na północ (lody tartufo!)</li>
<li><a href="/pl/natura/capo-vaticano/">Capo Vaticano</a> — 15 min na południe (top 10 plaż świata)</li>
</ul>', $dest);

	$p('Santa Maria dell\'Isola','santa-maria-dell-isola','<p>Niekwestionowany symbol Tropei i jeden z najczęściej fotografowanych zabytków Włoch. Ta średniowieczna benedyktyńska świątynia stoi na szczycie dramatycznego skalistego cypla połączonego ze stałym lądem, otoczonego turkusowym Morzem Tyrreńskim.</p>

<h2>Zwiedzanie</h2>
<p>Wejdź po kamiennych schodach wykutych w skale, by podziwiać panoramiczne widoki. Wnętrze kościoła jest proste i bielone — prawdziwą atrakcją jest otoczenie. Najlepiej fotografować z belwederu Tropei o zachodzie słońca.</p>

<p><a href="/pl/kierunki/tropea/">← Powrót do Tropea</a></p>', $tropea);

	$p('Plaże Tropea','plaze-tropea','<p>Plaże Tropei niezmiennie klasyfikowane są jako jedne z najlepszych we Włoszech. Główna plaża rozciąga się pod klifem starego miasta — drobny biały piasek i krystalicznie czysta turkusowa woda.</p>

<h2>Najlepsze plaże</h2>
<p><strong>Spiaggia della Rotonda</strong> — Główna plaża pod starym miastem. <strong>Spiaggia del Cannone</strong> — Bardziej odosobniona, obok Isoli. <strong>Mare Piccolo</strong> — Idealna dla rodzin z płytką wodą.</p>

<p><a href="/pl/kierunki/tropea/">← Powrót do Tropea</a></p>', $tropea);

	$p('Festiwal Czerwonej Cebuli','festiwal-czerwonej-cebuli','<p>Czerwona cebula z Tropei (cipolla rossa di Tropea) jest słynna w całych Włoszech ze swojego słodkiego, łagodnego smaku. Co lato miasteczko świętuje Sagra della Cipolla Rossa — festiwal potraw z cebuli, muzyki na żywo i lokalnej kultury.</p>

<p><a href="/pl/kierunki/tropea/">← Powrót do Tropea</a></p>', $tropea);

	// ─── Scilla + atrakcje ───
	$scilla = $p('Scilla','scilla','<p>Nazwana od mitologicznego potwora morskiego z Odysei Homera, Scilla strzeże północnego wejścia do Cieśniny Mesyńskiej. Jej starożytna dzielnica rybacka Chianalea — „Mała Wenecja Południa" — jest jedną z najbardziej urokliwych okolic we Włoszech.</p>

<h2>Główne atrakcje</h2>
<ul>
<li><a href="/pl/kierunki/scilla/chianalea/">Chianalea</a> — Legendarna dzielnica rybacka</li>
<li><a href="/pl/kierunki/scilla/castello-ruffo/">Castello Ruffo</a> — Średniowieczny zamek z panoramicznymi widokami</li>
<li><a href="/pl/kierunki/scilla/tradycja-polowu-miecznika/">Tradycja polowu na miecznika</a> — Starożytne dziedzictwo rybackie</li>
</ul>

<h2>W pobliżu</h2>
<ul>
<li><a href="/pl/kierunki/reggio-calabria/">Reggio Calabria</a> — 20 min na południe</li>
<li><a href="/pl/natura/costa-viola/">Costa Viola</a> — Purpurowe Wybrzeże ciągnie się na północ</li>
</ul>', $dest);

	$p('Chianalea','chianalea','<p>Chianalea to starożytna dzielnica rybacka Scilli, gdzie kolorowe domy zbudowane są bezpośrednio na skałach, a morze obmywa ich fundamenty. Wąskie uliczki, łodzie rybackie na maleńkich plażach i zapach grillowanego miecznika — to są prawdziwe, niezmienione Włochy.</p>

<p>Nazwa oznacza „płaską wodę" w lokalnym dialekcie, nawiązując do spokojnych wód małej zatoczki. Restauracje serwują to, co złowiono tego ranka. Przyjdź o zachodzie słońca na niezapomniane widoki przez Cieśninę na Sycylię.</p>

<p><a href="/pl/kierunki/scilla/">← Powrót do Scilla</a></p>', $scilla);

	$p('Castello Ruffo','castello-ruffo','<p>Usytuowany na skalistym cyplu między Chianaleą a główną plażą, Castello Ruffo strzeże Cieśniny Mesyńskiej od V wieku. Dziś mieści wystawy i oferuje panoramiczne widoki na Kalabrię, Sycylię i Wyspy Liparyjskie.</p>

<p><a href="/pl/kierunki/scilla/">← Powrót do Scilla</a></p>', $scilla);

	$p('Tradycja polowu na miecznika','tradycja-polowu-miecznika','<p>Scilla ma wielowiekową tradycję polowania na mieczniki w Cieśninie Mesyńskiej. Rybacy nadal używają tradycyjnych „passerelle" — wysokich wież obserwacyjnych na łodziach — do wypatrywania ryb. Coroczna Sagra del Pesce Spada świętuje to dziedzictwo, serwując świeżego miecznika na każdy możliwy sposób.</p>

<p><a href="/pl/kierunki/scilla/">← Powrót do Scilla</a></p>', $scilla);

	// ─── Pizzo + atrakcje ───
	$pizzo = $p('Pizzo','pizzo','<p>To urokliwe nadmorskie miasteczko nad Zatoką Sant\'Eufemia jest ojczyzną tartufo — najsłynniejszych włoskich lodów. Poza lodami Pizzo oferuje tajemniczy skalny kościół Piedigrotta i malownicze centrum historyczne.</p>

<h2>Główne atrakcje</h2>
<ul>
<li><a href="/pl/kierunki/pizzo/kosciol-piedigrotta/">Kościół Piedigrotta</a> — Wykuty w całości w skale</li>
<li><a href="/pl/kierunki/pizzo/tartufo-gelato/">Lody Tartufo</a> — Oryginał, tu się narodziły</li>
<li><a href="/pl/kierunki/pizzo/castello-murat/">Castello Murat</a> — Gdzie generał Napoleona spotkał swój koniec</li>
</ul>', $dest);

	$p('Kościół Piedigrotta','kosciol-piedigrotta','<p>Kościół wykuty w całości z tufu przez rozbitków w XVII wieku. Wewnątrz naturalnej wielkości kamienne figury przedstawiają sceny biblijne w podziemnej grocie nad morzem. Jedno z najbardziej unikalnych i tajemniczych miejsc Kalabrii.</p>

<p><a href="/pl/kierunki/pizzo/">← Powrót do Pizzo</a></p>', $pizzo);

	$p('Lody Tartufo','tartufo-gelato','<p>Tartufo di Pizzo zostało wynalezione tutaj w latach 50. w Bar Dante na głównym placu. To kula lodów orzechowo-czekoladowych z płynnym czekoladowym środkiem, oprószona kakao. Dziś słynne na całym świecie, ale nic nie pobije jedzenia oryginału na Piazza della Repubblica z widokiem na morze.</p>

<p><a href="/pl/kierunki/pizzo/">← Powrót do Pizzo</a></p>', $pizzo);

	$p('Castello Murat','castello-murat','<p>Ten XV-wieczny aragońsko zamek to miejsce, gdzie Joachim Murat, szwagier Napoleona i król Neapolu, został schwytany i stracony w 1815 roku po nieudanej próbie odzyskania tronu. Zamek mieści obecnie muzeum o Muracie i oferuje widoki na morze.</p>

<p><a href="/pl/kierunki/pizzo/">← Powrót do Pizzo</a></p>', $pizzo);

	// ─── Bova ───
	$bova = $p('Bova','bova','<p>Jedna z włoskich „Borghi più belli" (najpiękniejszych wiosek), Bova jest kulturalną stolicą Grekaników — potomków starożytnych greckich osadników, którzy nadal mówią w Griko, języku wywodzącym się ze starożytnej greki. Położona na wysokości 820 m n.p.m. u podnóża Aspromonte, to żywe muzeum dziedzictwa Magna Graecia.</p>

<h2>Co zobaczyć</h2>
<ul>
<li><a href="/pl/kierunki/bova/dziedzictwo-grekanickie/">Dziedzictwo grekaniczne</a> — Żywa tradycja grecka</li>
</ul>

<h2>W pobliżu</h2>
<ul>
<li><a href="/pl/kierunki/reggio-calabria/">Reggio Calabria</a> — 45 min jazdy</li>
<li><a href="/pl/natura/aspromonte/">Aspromonte</a> — Już tu jesteś</li>
</ul>', $dest);

	$p('Dziedzictwo grekaniczne','dziedzictwo-grekanickie','<p>Grekanici to ostatni potomkowie greckojęzycznej populacji, która niegdyś zamieszkiwała znaczną część południowej Kalabrii. W Bovie niektórzy starsi mieszkańcy nadal mówią w Griko, a miasteczko świętuje swoje dziedzictwo dwujęzycznymi tablicami, greckimi festiwalami i corocznym festiwalem muzycznym Paleariza.</p>

<p><a href="/pl/kierunki/bova/">← Powrót do Bova</a></p>', $bova);

	// ─── Pozostałe miasta ───
	$p('Gerace','gerace','<p>Dom największej normandzkiej katedry w Kalabrii, Gerace to doskonale zachowane średniowieczne miasto na skalistym plateau. Labirynt wąskich uliczek kryje bizantyjskie kościoły, szlacheckie pałace i panoramiczne widoki na wybrzeże jońskie.</p>', $dest);
	$p('Stilo','stilo','<p>Słynne z Cattolica di Stilo — maleńkiego bizantyjskiego kościoła z IX wieku, kandydata na Listę Światowego Dziedzictwa UNESCO. Pięć kopuł wieńczy tę ceglaną budowlę przylegającą do Monte Consolino, reprezentując głęboki wpływ Bizancjum na kulturę Kalabrii.</p>', $dest);
	$cosenza = $p('Cosenza','cosenza','<p>„Ateny Kalabrii" — miasto uniwersyteckie, gdzie pulsujące starówka łączy się ze współczesną kulturą. Centro storico wzdłuż rzeki Crati to jeden z najlepiej zachowanych średniowiecznych ośrodków w południowych Włoszech. Nie przegap otwartego muzeum MAB i wspaniałego teatru Telesio.</p>

<h2>W pobliżu</h2>
<ul>
<li><a href="/pl/natura/sila/">Park Narodowy Sila</a> — Brama do wielkiego lasu</li>
</ul>', $dest);
	$p('Catanzaro','catanzaro','<p>Stolica regionu Kalabria leży na wzgórzu między Morzem Tyrreńskim a Jońskim. Znana ze spektakularnych punktów widokowych, wielowiekowej tradycji tkactwa jedwabiu i jako brama na piękne wybrzeże jońskie.</p>', $dest);
	$p('Locri','locri','<p>Dom stanowiska archeologicznego Locri Epizefiri, jednej z najważniejszych greckich kolonii w Magna Graecia. Założone w VII w. p.n.e., ruiny obejmują grecki teatr, świątynie i muzeum z niezwykłymi terakotowymi artefaktami.</p>', $dest);

	// ═══ NATURA ═══
	echo "\n--- NATURA ---\n";
	$nature = $p('Natura','natura','<p>Trzy parki narodowe, 800 km linii brzegowej, dzikie góry i dziewicze plaże — Kalabria to raj dla miłośników przyrody.</p>
<ul>
<li><a href="/pl/natura/aspromonte/">Park Narodowy Aspromonte</a></li>
<li><a href="/pl/natura/sila/">Park Narodowy Sila</a></li>
<li><a href="/pl/natura/pollino/">Park Narodowy Pollino</a></li>
<li><a href="/pl/natura/plaze/">Najlepsze plaże</a></li>
<li><a href="/pl/natura/capo-vaticano/">Capo Vaticano</a></li>
<li><a href="/pl/natura/costa-viola/">Costa Viola</a></li>
</ul>', $pl);

	$p('Park Narodowy Aspromonte','aspromonte','<p>Dzikie góry na czubku włoskiego buta. Pradawne lasy, dramatyczne wodospady jak Cascate di Maesano i ostatnie greckojęzyczne wioski. Aspromonte oznacza „biała góra" — a jej szczyty sięgają prawie 2000 m n.p.m.</p>', $nature);
	$p('Park Narodowy Sila','sila','<p>„Wielki Las Włoch" — rozległe górskie płaskowyże, krystalicznie czyste jeziora (Arvo, Ampollino, Cecita) i najlepsze w Kalabrii narty zimą. Latem piesze wędrówki przez wielowiekowe lasy sosny czarnej.</p>', $nature);
	$p('Park Narodowy Pollino','pollino','<p>Największy park narodowy we Włoszech, dzielony z Bazylikatą. Dom pradawnej sosny bośniackiej (Pino Loricato), dramatycznych kanionów i światowej klasy raftingu na rzece Lao.</p>', $nature);
	$p('Najlepsze plaże','plaze','<p>Przewodnik po najlepszych plażach Kalabrii — od białego piasku Tropei po dzikie jońskie wybrzeża.</p>
<h2>Wybrzeże tyrreńskie (zachód)</h2>
<p>Tropea, Capo Vaticano, Pizzo, Scilla — turkusowa woda, dramatyczne klify.</p>
<h2>Wybrzeże jońskie (wschód)</h2>
<p>Długie piaszczyste plaże, mniej tłumów, cieplejsza woda. Soverato, Le Castella, Capo Rizzuto.</p>', $nature);
	$p('Capo Vaticano','capo-vaticano','<p>Granitowe klify opadające do krystalicznie czystej wody. Uznawane za jedną z 10 najlepszych plaż na świecie. 15 minut na południe od Tropei, z ukrytymi zatoczkami dostępnymi łodzią lub stromymi ścieżkami.</p>', $nature);
	$p('Costa Viola','costa-viola','<p>„Purpurowe Wybrzeże" od Scilli do Palmi — dramatyczne klify, ukryte zatoczki i legendarne zachody słońca nad Wyspami Liparyjskimi. Nazwa pochodzi od fioletowych odcieni, jakie morze przybiera o zachodzie słońca.</p>', $nature);

	// ═══ KUCHNIA ═══
	echo "\n--- KUCHNIA ---\n";
	$cuisine = $p('Kuchnia kalabryjska','kuchnia','<p>Kuchnia kalabryjska jest odważna, pikantna i głęboko zakorzeniona w tradycji. Od ognistej \'nduja po świeżego miecznika, ręcznie robiony makaron i bergamotkę najwyższej klasy.</p>
<ul>
<li><a href="/pl/kuchnia/nduja/">\'Nduja</a> — Słynna pikantna pasta</li>
<li><a href="/pl/kuchnia/bergamotka/">Bergamotka</a> — Unikalny cytrus Kalabrii</li>
<li><a href="/pl/kuchnia/wino-kalabryjskie/">Wino kalabryjskie</a></li>
<li><a href="/pl/kuchnia/makaron-fileja/">Makaron Fileja</a></li>
<li><a href="/pl/kuchnia/street-food/">Street Food</a></li>
</ul>', $pl);

	$p('\'Nduja','nduja','<p>Ognista, smarowalna salami wieprzowa ze Spilingi. Robiona z wieprzowiny i hojnej porcji kalabryjskiego peperoncino, \'nduja podbiła świat kulinarny. Smaruj na chlebie, rozpuszczaj w makaronie lub dodawaj do pizzy.</p>', $cuisine);
	$p('Bergamotka','bergamotka','<p>95% światowej bergamotki rośnie na wąskim pasie wzdłuż jońskiego wybrzeża Kalabrii koło Reggio. Ten unikalny cytrus używany jest w herbacie Earl Grey, perfumach i kuchni kalabryjskiej — od marmolady po likier bergamotkowy.</p>', $cuisine);
	$p('Wino kalabryjskie','wino-kalabryjskie','<p>Tradycja winiarska Kalabrii sięga czasów greckich. Kluczowe odmiany: Ciro (najstarsze DOC we Włoszech), Greco di Bianco (starożytne wino deserowe), Gaglioppo (główna czerwona odmiana). Nowi producenci umieszczają kalabryjskie wino na mapie świata.</p>', $cuisine);
	$p('Makaron Fileja','makaron-fileja','<p>Ręcznie robiony makaron formowany przez skręcanie ciasta wokół cienkiego patyczka. Typowo podawany z ragù z \'nduja, sosem z kozy lub prostym sosem pomidorowym z ricottą salatą. Kalabryjska specjalność, której nie znajdziesz nigdzie indziej.</p>', $cuisine);
	$p('Street Food','street-food','<p>Tartufo di Pizzo (oryginał!), zeppole (smażone ciasto), grattachecca (granita), cudduraci (wielkanocne ciastka) i oczywiście świeże arancini. Scena street food Kalabrii jest bezpretensjonalna i pyszna.</p>', $cuisine);

	// ═══ KULTURA ═══
	echo "\n--- KULTURA ---\n";
	$culture = $p('Kultura i historia','kultura','<p>Od Magna Graecia po Normanów, od bizantyjskich kościołów po żywe greckojęzyczne społeczności — kultura Kalabrii ma 3000 lat głębokości.</p>
<ul>
<li><a href="/pl/kultura/magna-graecia/">Magna Graecia</a></li>
<li><a href="/pl/kultura/dziedzictwo-bizantyjskie/">Dziedzictwo bizantyjskie</a></li>
<li><a href="/pl/kultura/tradycje-i-festiwale/">Tradycje i festiwale</a></li>
</ul>', $pl);

	$p('Magna Graecia','magna-graecia','<p>Od VIII w. p.n.e. greccy koloniści zakładali miasta na terenie południowej Kalabrii — Rhegion (Reggio), Kroton (Crotone), Locri, Sybaris. Ich dziedzictwo żyje w archeologii, języku i kulturze.</p>', $culture);
	$p('Dziedzictwo bizantyjskie','dziedzictwo-bizantyjskie','<p>Po upadku Rzymu Kalabria stała się bastionem kultury bizantyjskiej na wieki. Cattolica di Stilo, greckie klasztory Aspromonte i Codex Purpureus Rossanensis są świadectwem tego dziedzictwa.</p>', $culture);
	$p('Tradycje i festiwale','tradycje-i-festiwale','<p>Varia di Palmi (dziedzictwo UNESCO), Festa della Madonna della Consolazione w Reggio, festiwal muzyki greckiej Paleariza w Bovie i niezliczone sagre (festiwale kulinarne) przez całe lato.</p>', $culture);

	// ═══ INFORMACJE PRAKTYCZNE ═══
	echo "\n--- PRAKTYCZNE ---\n";
	$practical = $p('Informacje praktyczne','praktyczne','<p>Wszystko, czego potrzebujesz, żeby zaplanować podróż do Kalabrii.</p>
<ul>
<li><a href="/pl/praktyczne/jak-dojechac/">Jak dojechać</a> — Loty, pociągi, promy</li>
<li><a href="/pl/praktyczne/wynajem-samochodu/">Wynajem samochodu</a> — Niezbędny do zwiedzania</li>
<li><a href="/pl/praktyczne/noclegi/">Noclegi</a></li>
<li><a href="/pl/praktyczne/plan-7-dni/">Plan na 7 dni</a></li>
</ul>', $pl);

	$p('Jak dojechać','jak-dojechac','<p><strong>Samolotem:</strong> Lamezia Terme (SUF) to główne lotnisko z lotami międzynarodowymi. Reggio Calabria (REG) ma połączenia krajowe. <strong>Pociągiem:</strong> Szybkie pociągi docierają do Lamezia i Reggio. <strong>Promem:</strong> Regularne promy z Sycylii (Mesyna) do Reggio i Villa San Giovanni.</p>', $practical);
	$p('Wynajem samochodu','wynajem-samochodu','<p>Samochód jest niezbędny do porządnego zwiedzania Kalabrii. Transport publiczny istnieje, ale jest ograniczony. Wypożycz na lotnisku Lamezia, by uzyskać najlepsze ceny. Drogi są dobre wzdłuż wybrzeża; górskie drogi mogą być kręte, ale malownicze.</p>', $practical);
	$p('Noclegi','noclegi','<p>Opcje od luksusowych hoteli nadmorskich po agriturismi (gospodarstwa agroturystyczne) w górach. Najlepsze rejony: wybrzeże Tropei na plaże, Reggio na życie miejskie, Aspromonte na przyrodę. Budżet: 50-100€/noc za dobrej jakości noclegi.</p>', $practical);
	$p('Plan na 7 dni','plan-7-dni','<p><strong>Dzień 1-2: Tropea i Capo Vaticano</strong> — Klifowe miasteczko, światowej klasy plaże.</p>
<p><strong>Dzień 3: Pizzo</strong> — Lody tartufo, skalny kościół Piedigrotta.</p>
<p><strong>Dzień 4: Scilla</strong> — Wioska rybacka Chianalea, zachód słońca nad Sycylią.</p>
<p><strong>Dzień 5: Reggio Calabria</strong> — Brązy z Riace, Lungomare.</p>
<p><strong>Dzień 6: Aspromonte i Bova</strong> — Dzikie góry, greckojęzyczna wioska.</p>
<p><strong>Dzień 7: Gerace i wybrzeże jońskie</strong> — Normandzka katedra, piaszczyste plaże.</p>', $practical);

	// ═══ STRONY STATYCZNE ═══
	echo "\n--- STATYCZNE ---\n";
	$p('Blog','blog-pl','', $pl);
	$p('O nas','o-nas','<p>Best of Calabria to najlepszy polskojęzyczny przewodnik po Kalabrii, południowych Włoszech. Opisujemy kierunki podróży, przyrodę, kuchnię, kulturę i praktyczne informacje dla podróżnych.</p>', $pl);
	$p('Kontakt','kontakt','<p>Pytania, sugestie lub współpraca? Napisz do nas: hello@bestofcalabria.com</p>', $pl);
	$p('Polityka prywatności','polityka-prywatnosci','', $pl);
	$p('Współpraca','wspolpraca','<p>Oferujemy możliwości współpracy dla hoteli, biur podróży, restauracji i lokalnych firm w Kalabrii. Kontakt: partners@bestofcalabria.com</p>', $pl);

	// ═══ POLSKIE KATEGORIE ═══
	echo "\n--- KATEGORIE PL ---\n";
	$mk_cat = function($n,$s) { $e=get_term_by('slug',$s,'category'); if($e) return $e->term_id;
		$r=wp_insert_term($n,'category',array('slug'=>$s)); if(is_wp_error($r)) return 0;
		echo "+ cat: {$n}\n"; return $r['term_id']; };
	$mk_cat('Kierunki','kierunki-pl'); $mk_cat('Przyroda','przyroda-pl');
	$mk_cat('Jedzenie i picie','jedzenie-picie-pl'); $mk_cat('Kultura','kultura-pl');
	$mk_cat('Porady podróżne','porady-podrozne-pl'); $mk_cat('Ukryte perły','ukryte-perly-pl');
	$mk_cat('Trasy','trasy-pl');

	$total = count(get_posts(array('post_type'=>'page','numberposts'=>-1)));
	echo "\nGOTOWE! Łącznie {$total} stron w serwisie.\n</pre>";
}
