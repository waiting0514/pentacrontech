## Context

The public site is server-rendered Blade + Tailwind v4 + Alpine.js, single-tenant, bilingual (`zh-TW` / `en` via `lroute()` and `lang/*/frontend.php`). Site-wide data comes from `SiteSetting::current()`. Banners, featured categories/products and news are already supplied to `pages/home` by the controller. The design source is the "Industrial Blue" artifact (Tokens, Main homepage, MobileNav).

## Goals / Non-Goals

**Goals**
- Faithfully apply the design tokens and homepage rhythm using existing dynamic data.
- Make the re-skin cascade: changing shared classes/tokens should restyle inner pages with minimal per-page edits.
- Accessibility: 2px focus rings, 44px+ touch targets, color contrast per design notes (Precision Blue only for ≥24px text/lines), `prefers-reduced-motion`.

**Non-Goals**
- Backend, API, DB or admin SPA changes.
- New CMS models for "Applications", "Machining Results" or "Automation steps" — the homepage renders these as static, translatable blocks (lang files) flagged as demo/TODO where the mockup flags them.
- Replacing Alpine.js or Swiper.

## Decisions

1. **Tokens in `@theme`** (`--color-navy`, `--color-deep`, `--color-eblue`, `--color-pblue`, `--color-plight`, `--color-steel`, `--color-lsteel`, `--color-off`, fonts `--font-display` / `--font-mono`). Utilities like `bg-navy` are then available in Blade. Rationale: native Tailwind v4 approach, no config file.
2. **Keep existing class names** (`mod-btn`, `mod-card`, `ind-*`) and change their definitions; add new component classes for design-specific parts (eyebrow, display type, metric, placeholder). Rationale: ~30 views reference them; avoids a risky mass rename.
3. **Header modes**: homepage uses an absolute transparent header over the hero; scroll (>8px) or any other page switches to solid navy via Alpine `x-data` + `:class`. Inner pages render the header as sticky solid navy.
4. **Mobile nav** is a full-screen navy drawer (Alpine; `Esc` closes, focus returns to toggle, body scroll locked) with Products expandable using `aria-expanded`.
5. **Homepage data mapping**: Hero = banner carousel when banners exist, else designed navy hero; "Machines" = featured categories (first large, rest small); "Featured machine" = first featured product; "News" = latest news (first large, rest list); Automation / Applications / Metrics / Company = static blocks from `lang` keys; CTA/Footer contact = `SiteSetting`. "Machining Results" is omitted (no data source).
6. **Fonts**: Archivo (variable width/weight), Noto Sans TC, IBM Plex Mono via one Google Fonts request replacing the old one; `font-stretch` used for display type.
7. **Brand mark**: use `SiteSetting->logo_url` if set; otherwise the hexagon SVG + site name, per design.

## Risks / Trade-offs

- Restyling shared classes may leave pink remnants in hard-coded `#BE185D` utilities → mitigate with a repo-wide grep sweep (`BE185D`, `pink-`, `Playfair`) as an explicit task.
- Static homepage blocks contain placeholder copy → show a "Demo" tag only outside production (`@unless(app()->isProduction())`) and track in tasks.
- Transparent header over arbitrary banner images can hurt contrast → add a top navy gradient scrim behind the header.
- Verification is visual; use browser screenshots at 1440 / 1024 / 390 px for each changed page.
