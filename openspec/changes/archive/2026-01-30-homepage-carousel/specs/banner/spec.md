## ADDED Requirements

### Requirement: Banner CRUD API
The system SHALL provide a RESTful API for managing banners with endpoints for list, create, update, sort, and delete operations. Banners SHALL be ordered by sort_order.

#### Scenario: List all banners
- **WHEN** a GET request is made to `/api/banners`
- **THEN** all banners are returned ordered by sort_order ascending

#### Scenario: Create a banner
- **WHEN** a POST request is made with title, link_url, image_pc file, and image_mobile file
- **THEN** a new banner record is created with processed images stored in the filesystem

#### Scenario: Update a banner
- **WHEN** a PUT request is made to `/api/banners/{id}` with updated fields
- **THEN** the banner is updated; if new images are provided, old images are deleted and replaced

#### Scenario: Delete a banner
- **WHEN** a DELETE request is made to `/api/banners/{id}`
- **THEN** the banner record and its associated image files are deleted from storage

#### Scenario: Update banner sort order
- **WHEN** a PATCH request is made to `/api/banners/{id}/sort` with a new sort_order value
- **THEN** the banner's sort_order is updated

### Requirement: Banner management UI
The BannerManager Vue component SHALL provide a list view with PC thumbnails, sort_order input, is_active toggle, edit/delete buttons, and a form modal for creating/editing banners with PC and Mobile image uploads with preview.

#### Scenario: Admin creates a banner
- **WHEN** admin fills in the form with title, link_url, PC image, and Mobile image and submits
- **THEN** the banner is created and appears in the list

#### Scenario: Admin toggles banner active state
- **WHEN** admin clicks the is_active toggle on a banner
- **THEN** the banner's active state is updated immediately

#### Scenario: Admin previews images before upload
- **WHEN** admin selects a PC or Mobile image file in the form
- **THEN** a preview of the selected image is displayed before submission
