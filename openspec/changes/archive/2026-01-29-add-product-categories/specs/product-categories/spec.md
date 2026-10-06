## ADDED Requirements

### Requirement: Product category CRUD management
The system SHALL provide API endpoints to create, read, update, and delete product categories. Each category SHALL have a multilingual name (JSON with zh/en keys), slug, sort_order, and is_active fields. Categories SHALL be scoped to a project via project_id.

#### Scenario: List all product categories
- **WHEN** a GET request is made to `/api/product-categories`
- **THEN** the system returns all product categories ordered by sort_order then id

#### Scenario: Create a product category
- **WHEN** a POST request is made to `/api/product-categories` with valid name, slug, sort_order, and is_active
- **THEN** the system creates the category and returns it with HTTP 201

#### Scenario: Update a product category
- **WHEN** a PUT request is made to `/api/product-categories/{id}` with updated fields
- **THEN** the system updates the category and returns the updated record

#### Scenario: Delete a product category
- **WHEN** a DELETE request is made to `/api/product-categories/{id}`
- **THEN** the system deletes the category and any products in that category SHALL have their category_id set to null

### Requirement: Product category frontend management
The system SHALL provide a ProductCategoryManager Vue component for managing product categories in the admin dashboard, with the same UI pattern as NewsCategoryManager.

#### Scenario: Admin manages product categories
- **WHEN** admin navigates to the product category management section
- **THEN** the system displays a table of categories with name (zh/en), slug, sort_order, and action buttons for edit and delete

#### Scenario: Admin creates a new product category
- **WHEN** admin clicks the create button and fills in the form
- **THEN** a modal appears with fields for name (zh/en), slug, sort_order, and is_active, and submitting creates the category
