## 1. Database & Backend

- [x] 1.1 Add `is_inquiry_only` to `products` table migration/schema
- [x] 1.2 Add `product_default_inquiry_mode` to `projects` table migration/schema
- [x] 1.3 Update `Product` and `Project` models (fillable fields, casts)
- [x] 1.4 Create/Update API endpoint for product batch actions (`POST /api/products/batch-action`)
- [x] 1.5 Run `migrate:fresh` and verify database structure

## 2. Frontend - Settings

- [x] 2.1 Add "Default Inquiry Mode" toggle to Project Settings page
- [x] 2.2 Wire up settings toggle to backend API
- [x] 2.3 Verify state persistence for project settings

## 3. Frontend - Product Management

- [x] 3.1 Update Product Form to include "Inquiry Only" toggle
- [x] 3.2 Initialize toggle state from project defaults on new product creation
- [x] 3.3 Update Product List to show "Inquiry Mode" status indicator
- [x] 3.4 Implement batch action UI for "Set to Inquiry Mode" / "Set to Show Price"
- [x] 3.5 Wire up batch actions to the new API endpoint

## 4. Verification

- [x] 4.1 Verify end-to-end flow: Settings -> New Product Default
- [x] 4.2 Verify end-to-end flow: Batch updates
- [x] 4.3 Check API responses for `is_inquiry_only` field