## MODIFIED Requirements

### Requirement: Banner CRUD API
The system SHALL provide a RESTful API for managing banners. All responses SHALL use the unified ApiResponse format and BannerResource for data transformation.

#### Scenario: List all banners
- **WHEN** a GET request is made to `/api/banners`
- **THEN** all banners are returned ordered by sort_order, wrapped in the unified success format

#### Scenario: Create a banner
- **WHEN** a POST request is made with title, link_url, image_pc file, and image_mobile file
- **THEN** a new banner is created and returned wrapped in the unified success format with code 201

### Requirement: Banner management UI
The BannerManager Vue component SHALL use BannerService for all API calls instead of direct Axios usage.

#### Scenario: Admin creates a banner
- **WHEN** admin fills in the form and submits
- **THEN** BannerService.create() is called and the banner appears in the list
