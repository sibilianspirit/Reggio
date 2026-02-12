# BestOfCalabria.com — Complete Site Architecture v3

## Vision
Comprehensive knowledge hub about Calabria, southern Italy.
Monetized through affiliates, ads, sponsored content.
EN (default, root) + PL (/pl/).
Theme: **GeneratePress Premium**.

---

## URL Architecture

All destinations use **hierarchical pages** (parent/child) so WordPress
generates proper URLs and breadcrumbs automatically.

```
bestofcalabria.com/                         ← Homepage
├── destinations/                           ← Hub: all destinations
│   ├── reggio-calabria/                    ← City overview
│   │   ├── bronzi-di-riace/               ← Attraction
│   │   ├── museo-nazionale/               ← Attraction
│   │   ├── lungomare/                     ← Attraction
│   │   ├── arena-dello-stretto/           ← Attraction
│   │   └── where-to-eat-reggio/           ← Practical subpage
│   ├── tropea/
│   │   ├── santa-maria-dell-isola/
│   │   ├── tropea-beaches/
│   │   ├── red-onion-festival/
│   │   └── where-to-eat-tropea/
│   ├── scilla/
│   │   ├── chianalea/
│   │   ├── castello-ruffo/
│   │   ├── swordfish-tradition/
│   │   └── scilla-beaches/
│   ├── pizzo/
│   │   ├── piedigrotta-church/
│   │   ├── tartufo-gelato/
│   │   └── castello-murat/
│   ├── bova/
│   │   ├── grecanico-heritage/
│   │   └── what-to-see-bova/
│   ├── gerace/
│   │   ├── norman-cathedral/
│   │   └── medieval-old-town/
│   ├── stilo/
│   │   └── cattolica-di-stilo/
│   ├── cosenza/
│   │   ├── old-town/
│   │   ├── mab-museum/
│   │   └── teatro-rendano/
│   ├── catanzaro/
│   │   └── belvedere-viewpoints/
│   └── locri/
│       └── locri-epizefiri/
├── nature/                                 ← Hub: nature & outdoors
│   ├── aspromonte/
│   │   ├── trekking-routes/
│   │   ├── waterfalls/
│   │   └── villages/
│   ├── sila/
│   │   ├── lake-arvo/
│   │   └── sila-winter/
│   ├── pollino/
│   │   ├── rafting/
│   │   └── pino-loricato/
│   ├── costa-viola/
│   ├── capo-vaticano/
│   └── beaches/
│       ├── best-beaches-tyrrhenian/
│       └── best-beaches-ionian/
├── cuisine/                                ← Hub: food & drink
│   ├── nduja/
│   ├── bergamot/
│   ├── fileja-pasta/
│   ├── pesce-spada/
│   ├── calabrian-wine/
│   ├── street-food/
│   ├── products/
│   └── restaurants/
├── culture/                                ← Hub: history & traditions
│   ├── magna-graecia/
│   ├── byzantine-heritage/
│   ├── traditions-festivals/
│   ├── grecanico-language/
│   └── crafts-ceramics/
├── practical/                              ← Hub: travel planning
│   ├── getting-there/
│   ├── car-rental/
│   ├── accommodation/
│   ├── weather-best-time/
│   ├── safety/
│   ├── itinerary-3-days/
│   ├── itinerary-7-days/
│   └── itinerary-14-days/
├── blog/                                   ← Blog posts (category-based)
├── about/
├── contact/
├── privacy-policy/
└── partnership/
```

---

## Page Template Strategy (GeneratePress)

### City Page (e.g. /destinations/reggio-calabria/)

```
┌─────────────────────────────────────────────┐
│ HERO: Full-width image + city name          │
│ "Reggio Calabria — Capital of Calabria"     │
├─────────────────────────────────────────────┤
│ INTRO: 2-3 paragraph overview               │
│ Quick facts: population, province, airport  │
├─────────────────────────────────────────────┤
│ TOP ATTRACTIONS — 3-column card grid:       │
│ [Bronzi di Riace] [Lungomare] [Museo]       │
│ Each card → links to child page             │
├─────────────────────────────────────────────┤
│ MAP: Embedded Google Map of the city        │
├─────────────────────────────────────────────┤
│ PRACTICAL: Getting there, where to stay     │
│ Booking.com widget / affiliate link         │
├─────────────────────────────────────────────┤
│ RELATED: "Nearby destinations"              │
│ [Scilla - 20 min] [Bova - 45 min]          │
├─────────────────────────────────────────────┤
│ NEWSLETTER CTA                              │
└─────────────────────────────────────────────┘
```

### Attraction Page (e.g. /destinations/reggio-calabria/bronzi-di-riace/)

```
┌─────────────────────────────────────────────┐
│ BREADCRUMB: Home > Destinations > Reggio    │
│            Calabria > Bronzi di Riace       │
├─────────────────────────────────────────────┤
│ HERO IMAGE                                  │
├─────────────────────────────────────────────┤
│ ARTICLE CONTENT                             │
│ - History                                   │
│ - What to see                               │
│ - Practical info (hours, tickets, address)  │
├─────────────────────────────────────────────┤
│ INFO BOX (sidebar or inline):               │
│ 📍 Address | 🕐 Hours | 💰 Price | 🌐 Web │
├─────────────────────────────────────────────┤
│ "More in Reggio Calabria" → sibling pages   │
├─────────────────────────────────────────────┤
│ GetYourGuide widget (affiliate)             │
└─────────────────────────────────────────────┘
```

### Hub Page (e.g. /destinations/, /nature/, /cuisine/)

```
┌─────────────────────────────────────────────┐
│ HERO: Section title + tagline               │
├─────────────────────────────────────────────┤
│ GRID: All child pages as cards with image   │
│ [Reggio] [Tropea] [Scilla] [Pizzo]         │
│ [Bova]   [Gerace] [Stilo]  [Cosenza]       │
├─────────────────────────────────────────────┤
│ LATEST BLOG POSTS in this category          │
├─────────────────────────────────────────────┤
│ NEWSLETTER CTA                              │
└─────────────────────────────────────────────┘
```

---

## Internal Linking Strategy

### Every city page includes:
1. **Child attraction links** — card grid at top
2. **"Nearby" section** — links to neighboring cities (with distance)
3. **Related nature** — e.g., Reggio → Aspromonte
4. **Related cuisine** — e.g., Pizzo → Tartufo, Reggio → Bergamot
5. **Practical links** — "How to get to X", "Where to stay in X"

### Linking matrix (key connections):
| From | To | Context |
|------|----|---------|
| Reggio | Scilla | "20 min drive along the coast" |
| Reggio | Bova | "Day trip to the Greek-speaking mountains" |
| Reggio | Aspromonte | "The wild mountains behind the city" |
| Tropea | Pizzo | "30 min north along the coast" |
| Tropea | Capo Vaticano | "The beaches south of Tropea" |
| Scilla | Costa Viola | "Part of the Purple Coast" |
| Pizzo | Tartufo gelato | Cuisine cross-link |
| Bova | Grecanico language | Culture cross-link |
| Cosenza | Sila | "Gateway to Sila National Park" |

---

## Navigation Structure

### Primary Menu (header):
```
Destinations ▾        Nature ▾         Cuisine    Culture    Practical ▾    Blog
├── Reggio Calabria   ├── Aspromonte                        ├── Getting There
├── Tropea            ├── Sila                              ├── Car Rental
├── Scilla            ├── Pollino                           ├── Where to Stay
├── Pizzo             ├── Best Beaches                      └── Itineraries
├── Bova              └── Capo Vaticano
├── Gerace
├── Cosenza
└── All destinations →
```

### Footer:
```
Column 1: About        Column 2: Top Destinations    Column 3: Plan Your Trip    Column 4: Newsletter
```

---

## Blog Categories (for /blog/ posts)
- Destinations
- Nature & Outdoors
- Food & Drink
- Culture & History
- Travel Tips
- Hidden Gems
- Itineraries

---

## Monetization Placement

| Location | Monetization |
|----------|-------------|
| City page "Where to Stay" section | Booking.com search widget |
| City page sidebar/bottom | GetYourGuide tours widget |
| Practical > Car Rental | DiscoverCars affiliate |
| Practical > Accommodation | Booking.com affiliate |
| Blog posts | AdSense (after 2nd heading) |
| All pages footer area | Newsletter signup (MailerLite) |
| Cuisine > Restaurants | TripAdvisor affiliate |

---

## Image Requirements

### Per city page:
- 1x hero (1920×800)
- 1x per attraction card (800×600)
- 3-5 inline content photos

### Per attraction page:
- 1x hero (1920×800)
- 2-3 content photos
- 1x info box/map

### Hub pages:
- 1x hero (1920×600)
- 1x per card (800×600)

**Sources:** Unsplash, Pexels (free), own photos
**Format:** WebP preferred, fallback JPEG, max 200KB per image

---

## GeneratePress Setup Notes

### Required plugins:
- GeneratePress Premium (GP Elements, Colors, Typography, etc.)
- GenerateBlocks (free) — for advanced layouts
- RankMath SEO — breadcrumbs, schema, sitemap
- WP Rocket or LiteSpeed Cache — caching
- MailerLite — newsletter
- Polylang — multilingual (EN + PL)

### GP Elements to create:
1. **Hero Element** — Hook: `after_header`, display on specific pages
2. **City Attractions Grid** — Hook: `after_content`, display on city pages
3. **Nearby Destinations** — Hook: `after_content`
4. **Newsletter CTA** — Hook: `before_footer`
5. **Booking Widget** — Hook: `after_content`, display on city pages

### Typography:
- Headings: Playfair Display (or similar serif from GP)
- Body: Inter / System font stack
- Load via GP Typography module (no extra plugin needed)

---

## Phase 1 Scope (MVP: ~35 pages)

### Pages to create:
- 1 Homepage
- 1 Destinations hub
- 6 City pages (Reggio, Tropea, Scilla, Pizzo, Bova, Cosenza)
- 15 Attraction subpages (2-3 per city)
- 1 Nature hub + 3 nature pages (Aspromonte, Beaches, Capo Vaticano)
- 1 Cuisine hub + 2 cuisine pages (Nduja, Calabrian Wine)
- 1 Culture hub
- 1 Practical hub + 2 practical pages (Getting There, 7-day Itinerary)
- 4 Static pages (About, Contact, Privacy, Partnership)

### Blog posts (drafts):
- 10 Reasons to Visit Calabria
- 7-Day Itinerary
- What is Nduja?
- Chianalea: Hidden Little Venice
- Best Beaches in Calabria
