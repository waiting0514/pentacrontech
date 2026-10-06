## ADDED Requirements

### Requirement: Homepage carousel rendering
The homepage SHALL display active banners as a Swiper.js carousel with navigation arrows, pagination dots, and 5-second autoplay. Each slide SHALL use a `<picture>` tag with separate PC and Mobile image sources.

#### Scenario: Render carousel with banners
- **WHEN** the homepage is loaded and active banners exist
- **THEN** a full-width Swiper carousel is rendered with all active banners ordered by sort_order

#### Scenario: No banners exist
- **WHEN** the homepage is loaded and no active banners exist
- **THEN** the carousel section is not rendered

### Requirement: LCP optimization
The first slide SHALL use `loading="eager"` and `fetchpriority="high"` on its image tag. All subsequent slides SHALL use `loading="lazy"`.

#### Scenario: First slide loads eagerly
- **WHEN** the carousel renders
- **THEN** the first slide's `<img>` tag has `loading="eager"` and `fetchpriority="high"`

#### Scenario: Other slides load lazily
- **WHEN** the carousel renders with multiple slides
- **THEN** all slides after the first have `loading="lazy"` on their `<img>` tags

### Requirement: Responsive image delivery
Each slide SHALL use a `<picture>` element with `<source media="(max-width: 768px)">` for the mobile image and a fallback `<img>` for the PC image.

#### Scenario: Mobile device views carousel
- **WHEN** viewport width is 768px or less
- **THEN** the mobile image is displayed

#### Scenario: Desktop views carousel
- **WHEN** viewport width is greater than 768px
- **THEN** the PC image is displayed
