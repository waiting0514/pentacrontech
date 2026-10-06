## MODIFIED Requirements

### Requirement: Product data model
The Product model SHALL include a nullable category_id foreign key referencing the product_categories table. Deleting a category SHALL set the product's category_id to null. The `image_url` field SHALL be removed. The Product model SHALL have a `hasMany` relationship to `ProductImage` and a helper to retrieve the primary image.

#### Scenario: Product belongs to a category
- **WHEN** a product is created or updated with a valid category_id
- **THEN** the product is associated with that product category

#### Scenario: Category is deleted
- **WHEN** a product category is deleted
- **THEN** all products in that category SHALL have their category_id set to null

#### Scenario: Product has images
- **WHEN** a product is loaded with images
- **THEN** the product's images are accessible via the `images` relationship ordered by sort_order

#### Scenario: Product primary image
- **WHEN** a product's primary image is requested
- **THEN** the system returns the image record where `is_primary` is true

### Requirement: Product management UI supports images
The ProductManager Vue component SHALL replace the image URL input with a multi-image upload area. Users can click to upload multiple images, the first image is automatically set as primary (indicated by a green border), users can click another image to set it as primary, and each image has a delete button.

#### Scenario: Upload images in product form
- **WHEN** admin clicks the upload area or drags images into it
- **THEN** images are uploaded to the server, processed, and displayed as thumbnails in the form

#### Scenario: Set primary image
- **WHEN** admin clicks on a non-primary image thumbnail
- **THEN** that image becomes the primary image with visual indicator updated

#### Scenario: Delete an image
- **WHEN** admin clicks the delete button on an image thumbnail
- **THEN** the image is removed from the server and the UI
