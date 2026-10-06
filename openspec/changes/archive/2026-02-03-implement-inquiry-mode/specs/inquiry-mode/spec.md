## ADDED Requirements

### Requirement: Configure Inquiry Only Mode
The system SHALL allow configuring whether a product is "Inquiry Only".

#### Scenario: Product is inquiry only
- **WHEN** `is_inquiry_only` is set to true for a product
- **THEN** the product price SHALL NOT be displayed in the API response or UI
- **THEN** the "Add to Cart" button SHALL be replaced by an inquiry CTA

### Requirement: Default Inquiry Mode
The system SHALL allow configuring a project-level default for inquiry mode.

#### Scenario: Creating a new product
- **WHEN** a new product is initialized in the UI
- **THEN** the `is_inquiry_only` toggle SHALL default to the value of `product_default_inquiry_mode` from the project settings

### Requirement: Batch Inquiry Configuration
The system SHALL allow batch updating the inquiry status of multiple products.

#### Scenario: Batch set to inquiry only
- **WHEN** a batch update request is sent with `action: set_inquiry_mode` and `value: true` for a list of product IDs
- **THEN** all specified products SHALL have `is_inquiry_only` set to true
