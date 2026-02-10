# Reggio Calabria Hub - WordPress Block Theme

Custom WordPress block theme (Full Site Editing) for a Reggio Calabria knowledge hub.

## Installation

1. Install WordPress (6.4+)
2. Clone this repo into `wp-content/themes/`:
   ```bash
   cd wp-content/themes/
   git clone https://github.com/sibilianspirit/Reggio.git reggio-hub
   ```
3. Activate the theme in WP Admin > Appearance > Themes
4. Download fonts (Playfair Display + Inter) from Google Fonts and place `.woff2` files in `assets/fonts/`

## Structure

```
├── style.css              # Theme header
├── theme.json             # Design tokens (colors, typography, spacing)
├── functions.php          # Theme setup, enqueues, pattern categories
├── templates/             # FSE templates
│   ├── index.html         # Homepage / blog
│   ├── single.html        # Single post
│   ├── page.html          # Static page
│   ├── archive.html       # Category/tag archive
│   ├── 404.html           # Not found
│   └── page-landing.html  # Landing page (full-width)
├── parts/                 # Template parts
│   ├── header.html
│   └── footer.html
├── patterns/              # Block patterns
│   ├── hero-home.php
│   ├── section-categories.php
│   └── newsletter-cta.php
└── assets/
    ├── css/
    │   ├── theme.css      # Frontend styles
    │   └── editor.css     # Editor styles
    ├── fonts/             # Self-hosted fonts
    └── images/
```

## Fonts

Download from Google Fonts and save as `.woff2` in `assets/fonts/`:
- [Playfair Display](https://fonts.google.com/specimen/Playfair+Display) (variable weight)
- [Inter](https://fonts.google.com/specimen/Inter) (variable weight)
