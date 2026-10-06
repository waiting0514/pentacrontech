## ADDED Requirements

### Requirement: Product images data model
The system SHALL store product images in a `product_images` table with `product_id` (FK), `paths` (JSON with main/thumb/tiny keys), `is_primary` (boolean), and `sort_order` (integer). Each product MAY have multiple images. Exactly one image per product SHALL be marked as primary.

#### Scenario: First image becomes primary
- **WHEN** the first image is uploaded to a product with no images
- **THEN** the image is automatically marked as `is_primary = true`

#### Scenario: Delete primary image
- **WHEN** the primary image is deleted and other images exist
- **THEN** the next image by sort_order becomes the new primary

### Requirement: Product image upload API
The system SHALL provide API endpoints for managing product images: upload (POST, supports multiple files), delete (DELETE, removes files from storage), set primary (PUT), and reorder (PUT).

#### Scenario: Upload multiple images
- **WHEN** a POST request with multiple image files is made to `/api/products/{product}/images`
- **THEN** each image is processed via ImageService and stored as a ProductImage record, returning the created records

#### Scenario: Delete an image
- **WHEN** a DELETE request is made to `/api/products/{product}/images/{image}`
- **THEN** the image files (all three sizes) are deleted from storage and the database record is removed

#### Scenario: Set image as primary
- **WHEN** a PUT request is made to `/api/products/{product}/images/{image}/primary`
- **THEN** the specified image is marked as primary and all other images for that product are unmarked

#### Scenario: Reorder images
- **WHEN** a PUT request with an ordered array of image IDs is made to `/api/products/{product}/images/reorder`
- **THEN** each image's sort_order is updated to match the array position
