## Why

The client requires a blue industrial brand for the public site. The current Blade frontend uses a pink/serif "wholesale" look (`#BE185D`, Playfair Display, rounded cards) that no longer matches the company's positioning as a precision-machining and automation maker. A new design ("Industrial Blue" design system v0.2, with homepage and mobile-nav mockups) has been delivered and must be applied to the storefront.

## What Changes

- Introduce the Industrial Blue design tokens (colors, Archivo / Noto Sans TC / IBM Plex Mono typography, 0–2px radius, 1360px container) as Tailwind v4 `@theme` tokens plus shared component classes in `resources/css/app.css`.
- Re-skin shared component classes (`mod-btn`, `ind-btn`, `mod-card`, pills, inputs, pagination, swiper) to the blue palette so every existing page inherits the new brand without per-page rewrites.
- Rebuild `partials/header` (transparent over hero on the homepage, solid navy when scrolled / on inner pages, 6-item nav, language switch, "Request Quote" CTA) and the mobile full-screen navigation drawer with expandable Products sub-menu.
- Rebuild `partials/footer` (dark `#05131F`, contact block from `SiteSetting`, 5 link columns, social icons).
- Rebuild `pages/home` following the design's section rhythm (Hero → Machines → Featured → Automation → Applications → Metrics → Company → News → CTA) using existing CMS data (banners, featured categories/products, news, SiteSetting).
- Restyle inner pages (products index/show, news index/show, knowledge, about, contact, inquiry, auth/account, `page-hero` component) to the new tokens.
- No backend/API/database changes.

## Capabilities

### New Capabilities
- `frontend-theme`: Industrial Blue design tokens, shared component classes, header/footer/mobile nav, and homepage + inner-page layout requirements for the public Blade site.

### Modified Capabilities
<!-- none: existing specs (banner-display, multilang-routing, etc.) keep their behavior; only presentation changes -->

## Impact

- Code: `resources/css/app.css`, `resources/views/layouts/app.blade.php`, `resources/views/partials/*`, `resources/views/components/*`, `resources/views/pages/*`, `resources/views/products/*`, `resources/views/news/*`, `resources/views/knowledge/*`, `resources/views/inquiry/*`, `resources/views/auth/*`, `resources/views/account/*`, `lang/{en,zh-TW}/frontend.php`.
- Fonts: replaces Inter / Playfair Display with Archivo, IBM Plex Mono, Noto Sans TC (Google Fonts).
- Admin SPA (Vue/Element Plus) is out of scope and unchanged.
- Placeholder copy from the mockup (company intro, phone, spec values) is marked as demo/TODO and must not ship as real claims.
