## ADDED Requirements

### Requirement: Unified API success response format
All API endpoints SHALL return success responses in the format: `{ success: true, code: <httpCode>, message: <string>, data: <object|array> }`.

#### Scenario: Successful data retrieval
- **WHEN** a GET request succeeds
- **THEN** the response body contains `success: true`, `code: 200`, and the data in the `data` field

#### Scenario: Successful resource creation
- **WHEN** a POST request creates a resource
- **THEN** the response body contains `success: true`, `code: 201`, and the created resource in `data`

### Requirement: Unified API error response format
All API error responses SHALL return: `{ success: false, code: <httpCode>, error_code: <string>, message: <string>, errors: <object|null> }`.

#### Scenario: Validation error
- **WHEN** a request fails validation
- **THEN** the response returns code 422, error_code `VALIDATION_ERROR`, and field-level errors in the `errors` object

#### Scenario: Resource not found
- **WHEN** a request references a non-existent resource
- **THEN** the response returns code 404, error_code `RESOURCE_NOT_FOUND`

#### Scenario: Unauthenticated request
- **WHEN** a request lacks valid authentication
- **THEN** the response returns code 401, error_code `UNAUTHENTICATED`

#### Scenario: Server error in production
- **WHEN** an unhandled exception occurs in production
- **THEN** the response returns code 500, error_code `SERVER_ERROR`, without exposing stack trace

### Requirement: Global exception handler
The application SHALL configure a global exception handler in `bootstrap/app.php` that intercepts all exceptions and returns the unified error format. Controllers SHALL NOT use try-catch blocks unless specific recovery logic is needed.

#### Scenario: Exception is automatically caught
- **WHEN** any exception is thrown in an API route
- **THEN** the global handler intercepts it and returns the appropriate error response format

### Requirement: ApiResponse trait
An `ApiResponse` trait SHALL provide `successResponse` and `errorResponse` methods used by all API controllers.

#### Scenario: Controller uses trait for success
- **WHEN** a controller action completes successfully
- **THEN** it calls `$this->successResponse($data, $message, $code)` to return the unified format

#### Scenario: Controller uses trait for error
- **WHEN** a controller needs to return a business logic error
- **THEN** it calls `$this->errorResponse($message, $errorCode, $httpCode)` to return the unified format
