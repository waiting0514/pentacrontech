## MODIFIED Requirements

### Requirement: Product Data Structure
The product data model SHALL include inquiry mode configuration.

#### Scenario: Fetching product details
- **WHEN** product details are requested via API
- **THEN** the response SHALL include the `is_inquiry_only` boolean field
