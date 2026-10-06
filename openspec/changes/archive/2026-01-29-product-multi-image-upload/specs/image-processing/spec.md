## ADDED Requirements

### Requirement: Image processing service
The system SHALL provide an `ImageService` class with a `processProductImage` method that accepts an `UploadedFile` and optional folder name, processes the image into three 1:1 JPG versions (main 1000x1000, thumb 500x500, tiny 150x150) using Intervention Image v3 with contain strategy and white background padding at quality 80, and stores them in `uploads/{folder}/{size}/` on the public disk.

#### Scenario: Process uploaded image
- **WHEN** an image file is passed to `processProductImage`
- **THEN** the system generates three JPG files at 1000x1000, 500x500, and 150x150 pixels with 1:1 aspect ratio, white background fill, and returns an array with keys `main`, `thumb`, `tiny` containing the relative file paths

#### Scenario: Non-square image processing
- **WHEN** a non-square image (e.g., 1600x900) is processed
- **THEN** the image is contained (fit within square without cropping) and padded with white (#ffffff) background to fill the square

#### Scenario: Unique filenames
- **WHEN** multiple images are processed
- **THEN** each image receives a unique filename using date and UUID pattern to prevent collisions
