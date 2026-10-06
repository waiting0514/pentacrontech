## MODIFIED Requirements

### Requirement: Product data model
The Product model SHALL include a nullable category_id foreign key referencing the product_categories table. Deleting a category SHALL set the product's category_id to null.

#### Scenario: Product belongs to a category
- **WHEN** a product is created or updated with a valid category_id
- **THEN** the product is associated with that product category

#### Scenario: Category is deleted
- **WHEN** a product category is deleted
- **THEN** all products in that category SHALL have their category_id set to null

### Requirement: Product API supports category filtering
The product list API SHALL accept an optional category_id query parameter to filter products by category.

#### Scenario: Filter products by category
- **WHEN** a GET request is made to the products endpoint with `category_id` parameter
- **THEN** only products belonging to that category are returned

#### Scenario: List products without category filter
- **WHEN** a GET request is made to the products endpoint without `category_id` parameter
- **THEN** all products are returned regardless of category

### Requirement: Product management UI supports category
The ProductManager Vue component SHALL include a category dropdown when creating/editing products, and a category filter above the product list.

#### Scenario: Select category when editing product
- **WHEN** admin creates or edits a product
- **THEN** a dropdown showing all active product categories is available for selection

#### Scenario: Filter product list by category
- **WHEN** admin selects a category from the filter dropdown in the product list
- **THEN** only products in that category are displayed
