# frontend-theme Specification

## Purpose
定義前台（Blade）採用的 Industrial Blue 視覺系統：設計代幣、共用元件、導覽列與頁尾，以及各頁面的一致樣式。

## Requirements

### Requirement: Industrial Blue design tokens
The public site SHALL define the Industrial Blue palette (Dark Navy `#071A2B`, Deep Industrial `#082A4A`, Engineering Blue `#0B5EA8`, Precision Blue `#168BD2`, Precision Light `#5AB4EC`, Steel Gray `#475569`, Light Steel `#E8EDF2`, Off White `#F5F7F9`) and typography (Archivo, Noto Sans TC, IBM Plex Mono) as shared theme tokens, with component radius no greater than 2px and a maximum content width of 1360px.

#### Scenario: Tokens available to views
- **WHEN** a Blade view uses a theme utility such as `bg-navy` or `text-eblue`
- **THEN** the compiled CSS resolves it to the defined hex value

#### Scenario: Legacy brand color removed
- **WHEN** the built frontend CSS and rendered public pages are inspected
- **THEN** no element uses the former pink brand color `#BE185D` or the Playfair Display font

### Requirement: Shared buttons and controls
Primary buttons SHALL use Engineering Blue with white text, have a minimum height of 54px (44px for the compact header variant), a 2px radius, and a visible 2px Precision Blue focus ring. Secondary, white-on-blue and ghost-light variants SHALL be provided.

#### Scenario: Primary button hover
- **WHEN** a user hovers a primary button
- **THEN** its background changes to `#094C89` and its arrow icon shifts right

#### Scenario: Keyboard focus
- **WHEN** a link or button receives keyboard focus
- **THEN** a 2px visible outline is shown

### Requirement: Site header
The header SHALL show the logo, six navigation items (Products, Solutions, Applications, Technology, About, News), a language switcher and a "Request Quote" button linking to the contact page. On the homepage it SHALL be transparent over the hero and become solid navy after scrolling; on all other pages it SHALL be solid navy.

#### Scenario: Homepage at top
- **WHEN** the homepage is loaded at scroll position 0
- **THEN** the header background is transparent with a subtle bottom border

#### Scenario: Scrolled
- **WHEN** the user scrolls more than 8px
- **THEN** the header becomes solid navy with a shadow

#### Scenario: Active item
- **WHEN** the current route belongs to a navigation item
- **THEN** that item shows a 2px Precision Blue underline

### Requirement: Mobile navigation
Below 1120px the desktop nav SHALL be replaced by a menu button that opens a full-screen navy drawer containing the nav items (Chinese label plus mono English label), an expandable Products sub-menu, language switch, login link and a full-width Request Quote button.

#### Scenario: Open and close
- **WHEN** the user taps the menu button, then the close button or presses Escape
- **THEN** the drawer opens (page scroll locked) and then closes, returning focus to the menu button

#### Scenario: Products expand
- **WHEN** the user taps Products
- **THEN** the sub-menu toggles and `aria-expanded` reflects the state

### Requirement: Homepage section rhythm
The homepage SHALL render sections in this order and background rhythm: Hero (navy), Machines (white), Featured Machine (light steel), Automation (deep industrial), Applications (white), Engineering Metrics (off white), Company (navy), News (white), CTA (engineering blue), Footer (`#05131F`). Machines, Featured Machine, News, CTA and Footer contact SHALL be populated from existing CMS data (`banners`, featured categories/products, news, `SiteSetting`).

#### Scenario: Populated CMS
- **WHEN** the homepage is rendered with featured categories, products and news
- **THEN** each section displays that data in the designed layout

#### Scenario: Empty CMS section
- **WHEN** a data-driven section has no records
- **THEN** the section is hidden or shows a neutral empty message without breaking layout

#### Scenario: Banner present
- **WHEN** active banners exist
- **THEN** the hero uses the banner carousel under the transparent header with a navy top scrim

### Requirement: Applications interactive list
The Applications section SHALL present six industries as a selectable list; selecting one SHALL update the stage panel (index, title, description) and set `aria-pressed` on the active button.

#### Scenario: Select industry
- **WHEN** the user clicks "Aerospace"
- **THEN** the stage shows the Aerospace content and the button is marked active

### Requirement: Footer
The footer SHALL use the dark footer color, show the logo and tagline, contact details from `SiteSetting`, five link columns and social icons for configured links only.

#### Scenario: Missing contact data
- **WHEN** `SiteSetting` has no phone or address
- **THEN** the corresponding line is omitted

### Requirement: Inner pages consistency
All other public pages (products, product detail, news, news detail, knowledge, about, contact, inquiry, auth, account) SHALL use the new tokens, page hero and components so no page shows the old visual style.

#### Scenario: Visit inner page
- **WHEN** any listed page is loaded
- **THEN** it shows the navy header, Industrial Blue typography and footer, with no layout overflow at 390px width

### Requirement: Responsive and motion behavior
Layouts SHALL adapt at 1120px (nav), 1000px and 900px (grids collapse to one or two columns) and 560px, with no horizontal scroll at 390px width. Transitions SHALL be disabled under `prefers-reduced-motion: reduce`.

#### Scenario: Mobile viewport
- **WHEN** the homepage is rendered at 390px wide
- **THEN** document width equals viewport width
