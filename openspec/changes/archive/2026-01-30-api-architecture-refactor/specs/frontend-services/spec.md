## ADDED Requirements

### Requirement: Centralized Axios instance
The application SHALL provide a configured Axios instance at `resources/js/utils/http.js` with baseURL `/api`, automatic Bearer token injection, response unwrapping, 401 redirect, and error handling.

#### Scenario: Successful response unwrapping
- **WHEN** an API call succeeds
- **THEN** the interceptor returns `response.data` directly (unwrapped from axios wrapper)

#### Scenario: 401 unauthorized handling
- **WHEN** an API call returns 401
- **THEN** the interceptor clears the stored token and redirects to `/login`

#### Scenario: 422 validation error passthrough
- **WHEN** an API call returns 422
- **THEN** the interceptor rejects with the error object so the caller can handle form errors

### Requirement: Domain service layer
Each domain SHALL have a dedicated service file in `resources/js/services/` that abstracts API calls. Vue components SHALL import services instead of using Axios directly.

#### Scenario: Component uses service for data
- **WHEN** a Vue component needs to fetch products
- **THEN** it imports `ProductService` and calls `ProductService.getAll()` instead of `axios.get('/api/products')`

#### Scenario: Service handles request formatting
- **WHEN** a service method is called with parameters
- **THEN** the service constructs the proper HTTP request (method, URL, headers, body) internally
