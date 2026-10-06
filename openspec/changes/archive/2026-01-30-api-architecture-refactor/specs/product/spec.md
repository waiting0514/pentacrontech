## MODIFIED Requirements

### Requirement: Product API supports category filtering
The product list API SHALL accept an optional category_id query parameter to filter products by category. All responses SHALL use the unified ApiResponse format and ProductResource for data transformation.

#### Scenario: Filter products by category
- **WHEN** a GET request is made to the products endpoint with `category_id` parameter
- **THEN** only products belonging to that category are returned, wrapped in the unified success format

#### Scenario: List products without category filter
- **WHEN** a GET request is made to the products endpoint without `category_id` parameter
- **THEN** all products are returned regardless of category, wrapped in the unified success format

### Requirement: Product management UI supports category
The ProductManager Vue component SHALL use ProductService for all API calls instead of direct Axios usage.

#### Scenario: Select category when editing product
- **WHEN** admin creates or edits a product
- **THEN** a dropdown showing all active product categories is available for selection

#### Scenario: Filter product list by category
- **WHEN** admin selects a category from the filter dropdown in the product list
- **THEN** only products in that category are displayed
