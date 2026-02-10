# ReggioCalabria.hub - Centrum Wiedzy o Regionie

## Wizja projektu

Serwis informacyjno-turystyczny o Reggio Calabria i prowincji - docelowo monetyzowany
przez afiliacje, reklamy, treści sponsorowane i usługi lokalne.

---

## Struktura serwisu

### 1. Strona Glowna (`/`)
- Hero z panoramą miasta i Cieśniną Mesyńską
- Sekcja "Dlaczego Reggio Calabria?" (3-4 karty z USP)
- Wyróżnione artykuły / aktualności
- Newsletter signup (zbieranie bazy)
- Szybkie linki do głównych sekcji

### 2. Przewodnik turystyczny (`/turystyka/`)
- `/turystyka/atrakcje/` - zabytki, muzea (Brązy z Riace, Museo Nazionale, Lungomare)
- `/turystyka/plaze/` - plaże i wybrzeże (Costa Viola, Scilla, Capo Vaticano)
- `/turystyka/szlaki/` - trekking, Aspromonte, szlaki górskie
- `/turystyka/mapa/` - interaktywna mapa z POI

### 3. Kultura i historia (`/kultura/`)
- `/kultura/historia/` - od Magna Graecia po współczesność
- `/kultura/tradycje/` - święta, festiwale (Festa della Madonna, Regata)
- `/kultura/jezyk/` - dialekt calabrese, słowniczek
- `/kultura/sztuka/` - rzemiosło, tkactwo, ceramika

### 4. Kuchnia (`/kuchnia/`)
- `/kuchnia/przepisy/` - lokalne przepisy (nduja, bergamotto, pesce spada)
- `/kuchnia/produkty/` - lokalne produkty DOP/IGP
- `/kuchnia/restauracje/` - przewodnik po restauracjach (afiliacja)
- `/kuchnia/wino/` - wina Calabrii (Cirò, Greco di Bianco)

### 5. Praktyczne informacje (`/praktyczne/`)
- `/praktyczne/dojazd/` - loty, pociągi, promy, autobusy
- `/praktyczne/noclegi/` - hotele, B&B, agriturismi (afiliacja Booking/Airbnb)
- `/praktyczne/wynajem-aut/` - wypożyczalnie (afiliacja)
- `/praktyczne/bezpieczenstwo/` - porady dla turystów
- `/praktyczne/pogoda/` - klimat, kiedy jechać

### 6. Blog / Aktualności (`/blog/`)
- Artykuły tematyczne (SEO long-tail)
- Relacje z podróży
- Wywiady z lokalnymi ludźmi
- Sezonowe przewodniki

### 7. Okolice - prowincja (`/okolice/`)
- `/okolice/scilla/` - Scilla i Chianalea
- `/okolice/aspromonte/` - Park Narodowy Aspromonte
- `/okolice/locri/` - Locri Epizefiri
- `/okolice/tropea/` - Tropea i Costa degli Dei
- `/okolice/stilo/` - Cattolica di Stilo, bizantyjskie dziedzictwo

### 8. Strony statyczne
- `/o-nas/` - o projekcie
- `/kontakt/` - formularz kontaktowy
- `/polityka-prywatnosci/`
- `/wspolpraca/` - oferta dla partnerów, reklama

---

## Model monetyzacji

| Kanał | Opis | Priorytet |
|-------|------|-----------|
| **Afiliacja Booking/Airbnb** | Linki do noclegów | Wysoki |
| **Afiliacja wynajem aut** | Discover Cars, Rentalcars | Wysoki |
| **Google AdSense** | Reklamy display | Średni |
| **Treści sponsorowane** | Artykuły od lokalnych biznesów | Średni |
| **E-book / przewodnik PDF** | Płatny przewodnik do pobrania | Niski (faza 2) |
| **Newsletter sponsorowany** | Reklama w mailingu | Niski (faza 2) |

---

## Stack technologiczny (propozycja)

| Warstwa | Technologia | Dlaczego |
|---------|-------------|----------|
| Framework | **Astro** | Szybki, statyczny, świetny SEO |
| Styling | **Tailwind CSS** | Szybkie prototypowanie, responsywność |
| CMS | **Markdown / MDX** | Proste zarządzanie treścią bez backendu |
| Hosting | **Netlify / Vercel** | Darmowy tier, CDN, szybki deploy |
| Analytics | **Plausible / Umami** | GDPR-friendly, lekkie |
| Newsletter | **Mailerlite** | Darmowy do 1000 subskrybentów |

---

## Struktura katalogów projektu

```
/
├── src/
│   ├── layouts/
│   │   ├── BaseLayout.astro        # Główny layout
│   │   ├── BlogLayout.astro        # Layout dla artykułów
│   │   └── CategoryLayout.astro    # Layout dla kategorii
│   ├── components/
│   │   ├── Header.astro
│   │   ├── Footer.astro
│   │   ├── Hero.astro
│   │   ├── Navigation.astro
│   │   ├── Card.astro
│   │   ├── Newsletter.astro
│   │   ├── Map.astro
│   │   └── SEO.astro
│   ├── pages/
│   │   ├── index.astro
│   │   ├── turystyka/
│   │   ├── kultura/
│   │   ├── kuchnia/
│   │   ├── praktyczne/
│   │   ├── blog/
│   │   ├── okolice/
│   │   └── [...slug].astro
│   ├── content/                     # Treści w Markdown/MDX
│   │   ├── blog/
│   │   ├── turystyka/
│   │   ├── kultura/
│   │   ├── kuchnia/
│   │   ├── praktyczne/
│   │   └── okolice/
│   └── styles/
│       └── global.css
├── public/
│   ├── images/
│   ├── fonts/
│   └── favicon.svg
├── astro.config.mjs
├── tailwind.config.mjs
└── package.json
```

---

## Fazy rozwoju

### Faza 1 - MVP
- Strona główna
- 2-3 artykuły w każdej sekcji
- Podstawowe SEO (meta, OG, sitemap)
- Newsletter signup
- Responsywny design

### Faza 2 - Wzrost
- 20+ artykułów
- Interaktywna mapa
- Integracja afiliacji (Booking, wynajem aut)
- Google AdSense
- Blog regularny (2-4 posty/miesiąc)

### Faza 3 - Monetyzacja pełna
- E-book / płatny przewodnik
- Treści sponsorowane
- Współpraca z lokalnymi biznesami
- Wersja wielojęzyczna (PL, EN, IT)
