## Why
Users need a way to configure products as "Inquiry Only" to hide prices and encourage direct contact for specific items or by default. This supports B2B use cases or catalog-style listings where pricing is negotiable or not public.

## What Changes
- Add `is_inquiry_only` boolean field to `products` table (modifying existing migration).
- Add `product_default_inquiry_mode` boolean field to `projects` table (modifying existing migration).
- Update product creation logic to initialize `is_inquiry_only` based on the project's default setting.
- Create a new batch update API endpoint (`POST /api/products/batch-action`) to set inquiry mode for multiple products.
- Add a global setting toggle "Default Inquiry Mode for New Products" in the Settings page.
- Update Product Form to include an "Inquiry Only" toggle that defaults to the global setting.
- Update Product List to include a "Inquiry Mode" column/indicator and batch actions ("Set to Inquiry Mode", "Set to Show Price").

## Capabilities

### New Capabilities
- `inquiry-mode`: Configuration and logic for handling inquiry-only state for products.

### Modified Capabilities
- `product`: Products now support an inquiry-only state which affects their display and interaction properties.

## Impact
- **Database**: Modify existing `products` and `projects` migration files directly (dev mode).
- **API**: New endpoint for batch actions; updates to product CRUD resources.
- **UI**: Updates to Product List, Product Form, and Settings pages.
