# GeneratePress Premium — Setup Guide for BestOfCalabria.com

## 1. Typography (Customize > Typography)

### Headings
- **Font:** Playfair Display (Google Fonts — available in GP)
- **Weight:** 700
- **Color:** #0F2E42 (Primary Dark)

### Body
- **Font:** Inter (or System Default — faster)
- **Weight:** 400
- **Size:** 17px
- **Line height:** 1.7
- **Color:** #3D3A36 (Neutral Dark)

---

## 2. Colors (Customize > Colors)

| Element | Color | Hex |
|---------|-------|-----|
| Primary link | Mediterranean Blue | #1B4D6E |
| Link hover | Terracotta | #C75B39 |
| Header background | White | #FFFFFF |
| Header text | Primary Dark | #0F2E42 |
| Footer background | Primary Dark | #0F2E42 |
| Footer text | White | #FFFFFF |
| Button bg | Mediterranean Blue | #1B4D6E |
| Button hover | Calabrian Gold | #D4A853 |

---

## 3. Layout (Customize > Layout)

### Container
- **Width:** 1200px
- **Inner content width:** 800px (for articles)

### Header
- **Preset:** Navigation floating right
- **Logo width:** 48px

### Sidebar
- **Default:** No sidebar (content-only)
- **Blog archives:** Right sidebar (optional)

### Footer
- **Widgets:** 4 columns
- **Background:** #0F2E42

---

## 4. Navigation / Menu Setup

### Primary Menu (Appearance > Menus)

```
Destinations                    Nature           Cuisine    Culture    Practical         Blog
├── Reggio Calabria            ├── Aspromonte                         ├── Getting There
│   ├── Bronzi di Riace        ├── Sila                              ├── Car Rental
│   ├── Lungomare              ├── Pollino                           ├── Where to Stay
│   ├── Museo Nazionale        ├── Best Beaches                      └── 7-Day Itinerary
│   └── Arena dello Stretto    ├── Capo Vaticano
├── Tropea                     └── Costa Viola
│   ├── Santa Maria dell'Isola
│   ├── Tropea Beaches
│   └── Red Onion Festival
├── Scilla
│   ├── Chianalea
│   ├── Castello Ruffo
│   └── Swordfish Tradition
├── Pizzo
│   ├── Piedigrotta Church
│   ├── Tartufo Gelato
│   └── Castello Murat
├── Bova
├── Gerace
├── Cosenza
└── All Destinations →
```

**How to create:**
1. Go to Appearance > Menus
2. Create a menu called "Primary"
3. Add pages in the hierarchy above
4. In GP, dropdown items appear automatically for child items
5. For "All Destinations →" add a custom link to /destinations/

### Footer Menu
- About | Contact | Privacy Policy | Partnership

---

## 5. GP Elements (Appearance > Elements)

GP Elements inject custom content into specific locations. Here's what to create:

### Element 1: City Page Hero Section
- **Type:** Hook
- **Hook:** `after_header`
- **Display Rules:** Pages > "Reggio Calabria", "Tropea", "Scilla", "Pizzo", "Bova", "Cosenza"
- **Content:** (GenerateBlocks container with background image + city name overlay)
- **Note:** Add featured images to city pages, then use GP Elements to display them as hero banners

### Element 2: Newsletter CTA
- **Type:** Hook
- **Hook:** `before_footer`
- **Display Rules:** Entire site
- **Content:**
```html
<section style="background:#1B4D6E;color:#fff;padding:3rem 1rem;text-align:center">
  <div style="max-width:600px;margin:0 auto">
    <h2 style="font-family:'Playfair Display',serif;color:#D4A853;margin-bottom:0.5rem">
      Get Calabria Travel Tips
    </h2>
    <p style="opacity:0.9;margin-bottom:1.5rem">
      Join our newsletter for insider guides, hidden gems, and trip planning advice.
    </p>
    <div class="ml-embedded" data-form="FORM_ID"></div>
  </div>
</section>
```

### Element 3: Breadcrumbs
- **Type:** Hook
- **Hook:** `after_header` (priority 5, before hero)
- **Display Rules:** All pages except Homepage
- **Content:** `<?php if (function_exists('rank_math_the_breadcrumbs')) rank_math_the_breadcrumbs(); ?>`
- **Execute PHP:** Yes

### Element 4: "Nearby Destinations" on City Pages
- **Type:** Hook
- **Hook:** `after_content`
- **Display Rules:** City pages
- **Note:** This is handled by the page content itself (internal links in each city page)

### Element 5: Language Switcher (EN | PL)
- **Type:** Hook
- **Hook:** `inside_navigation` or `after_navigation`
- **Content:**
```html
<div class="boc-lang-switcher" style="display:inline-flex;align-items:center;gap:4px;margin-left:1rem;padding-left:1rem;border-left:1px solid #E8E0D4">
  <a href="/" style="font-size:0.8rem;font-weight:600;text-decoration:none;color:#fff;background:#1B4D6E;padding:2px 8px;border-radius:3px">EN</a>
  <span style="color:#ccc;font-size:0.7rem"> | </span>
  <a href="/pl/" style="font-size:0.8rem;font-weight:600;text-decoration:none;color:#3D3A36;padding:2px 8px;border-radius:3px">PL</a>
</div>
```

---

## 6. Recommended Plugins

| Plugin | Purpose | Priority |
|--------|---------|----------|
| GenerateBlocks | Advanced layouts, hero sections | High |
| RankMath SEO | Breadcrumbs, schema, sitemap | High |
| WP Rocket / LiteSpeed Cache | Performance | High |
| Polylang | EN + PL multilingual | Medium |
| MailerLite | Newsletter forms | Medium |
| WP Pusher | Deploy from GitHub | Already installed |

---

## 7. Custom CSS (Customize > Additional CSS)

```css
/* ── Card hover effects ── */
.gb-container.boc-card {
  transition: box-shadow 0.3s ease, transform 0.3s ease;
  border: 1px solid rgba(0,0,0,0.04);
  border-radius: 12px;
  overflow: hidden;
}
.gb-container.boc-card:hover {
  box-shadow: 0 12px 32px rgba(0,0,0,0.12);
  transform: translateY(-4px);
}

/* ── Smooth scrolling ── */
html { scroll-behavior: smooth; }

/* ── Navigation hover ── */
.main-navigation a:hover { color: #C75B39 !important; }

/* ── Hero text shadow ── */
.page-hero h1, .page-hero h2 { text-shadow: 0 2px 12px rgba(0,0,0,0.4); }

/* ── Language switcher ── */
.boc-lang-switcher a:hover { background: #F5F1EB; color: #1B4D6E; }

/* ── Footer links ── */
.site-footer a:hover { color: #D4A853 !important; }

/* ── Breadcrumbs ── */
.rank-math-breadcrumb { font-size: 0.85rem; padding: 0.75rem 0; color: #666; }
.rank-math-breadcrumb a { color: #1B4D6E; }

/* ── Responsive ── */
@media (max-width: 768px) {
  .boc-lang-switcher { margin-left: 0.5rem; padding-left: 0.5rem; }
}
```

---

## 8. Page Setup Checklist

After running the BOC Importer (Tools > BOC Import):

- [ ] Set featured images on city pages (Reggio, Tropea, Scilla, Pizzo, Bova, Cosenza)
- [ ] Create primary navigation menu with hierarchy
- [ ] Create footer menu
- [ ] Set up footer widgets (4 columns: About, Destinations, Plan Trip, Newsletter)
- [ ] Add custom CSS in Customize
- [ ] Configure RankMath breadcrumbs
- [ ] Create GP Elements (hero, newsletter CTA, breadcrumbs, lang switcher)
- [ ] Install MailerLite and create newsletter form
- [ ] Test all internal links
- [ ] Add Google Analytics / Search Console

---

## 9. Image Strategy (Quick Start)

For launch, add at minimum:
- **6 featured images** for city pages (800x600 or wider)
- **1 homepage hero** (1920x800)
- Source: Unsplash/Pexels, search "Tropea", "Reggio Calabria", "Scilla Italy", etc.
- Format: WebP preferred, max 200KB

See IMAGE-PLAN.md for detailed image requirements per page.
