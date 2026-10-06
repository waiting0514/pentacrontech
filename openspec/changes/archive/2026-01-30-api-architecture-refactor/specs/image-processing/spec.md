## MODIFIED Requirements

### Requirement: Image processing service
The system SHALL provide an `ImageService` class. All API controllers using ImageService SHALL return responses in the unified ApiResponse format.

#### Scenario: Process uploaded image
- **WHEN** an image file is passed to `processProductImage`
- **THEN** the system generates three JPG files and the controller returns the result wrapped in the unified success format
