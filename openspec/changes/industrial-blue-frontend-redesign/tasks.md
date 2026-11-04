## 1. Foundation
- [x] 1.1 Add Industrial Blue `@theme` tokens, fonts and shared component classes to `resources/css/app.css`; re-skin legacy `mod-*`/`ind-*`, pagination, swiper
- [x] 1.2 Update `layouts/app.blade.php` (fonts, body classes, header mode flag, reduced-motion)
- [x] 1.3 Rebuild `partials/header.blade.php` incl. transparent/solid modes and mobile drawer
- [x] 1.4 Rebuild `partials/footer.blade.php`
- [x] 1.5 Add lang keys (zh-TW / en) for new nav, CTA and static homepage blocks

## 2. Homepage
- [x] 2.1 Hero (banner carousel + designed fallback)
- [x] 2.2 Machines (featured categories) and Featured Machine (featured product)
- [x] 2.3 Automation, Applications (Alpine), Metrics, Company static blocks
- [x] 2.4 News and CTA sections

## 3. Inner pages (parallelizable)
- [x] 3.1 `components/page-hero`, products index/show
- [x] 3.2 news index/show, knowledge index/category/show
- [x] 3.3 about, contact, maintenance
- [x] 3.4 inquiry, auth, account

## 4. Verification
- [x] 4.1 Grep sweep for `BE185D`, `pink-`, `Playfair`, `Inter` leftovers
- [x] 4.2 Visual check via headless Chrome (1440 / 500) of home, products (index/show), news, knowledge, about, contact, inquiry, login, `/en`; curl 200 on all public routes
- [x] 4.3 `artisan test` (sqlite in-memory): 35 pass, 2 fail (ExampleTest has no RefreshDatabase; login-page site-name test needs APP_LOCALE=zh-TW) — both unrelated to this change; mobile drawer click-through not exercised
- [x] 4.4 `openspec validate industrial-blue-frontend-redesign`
