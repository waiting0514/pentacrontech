## ADDED Requirements

### Requirement: Banner image processing
The ImageService SHALL provide a `processBannerImage` method that accepts an UploadedFile and a type ('pc' or 'mobile'). PC images SHALL be resized to 1920px width maintaining aspect ratio. Mobile images SHALL be resized to 800px width maintaining aspect ratio. Both SHALL be converted to JPG at quality 85.

#### Scenario: Process PC banner image
- **WHEN** `processBannerImage` is called with type 'pc'
- **THEN** the image is resized to 1920px width (aspect ratio maintained), saved as JPG quality 85 at `uploads/banners/pc/`

#### Scenario: Process mobile banner image
- **WHEN** `processBannerImage` is called with type 'mobile'
- **THEN** the image is resized to 800px width (aspect ratio maintained), saved as JPG quality 85 at `uploads/banners/mobile/`

#### Scenario: Image smaller than target width
- **WHEN** an uploaded image is smaller than the target width
- **THEN** the image is NOT upscaled, only converted to JPG at quality 85
