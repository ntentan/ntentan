# CHANGELOG

## v0.12.1 - 2026-08-26
### Changed
- Updated dependencies.

## v0.12.0 - 2026-08-25
### Added
- Completed PSR-7 HTTP message specifications implementation across HTTP classes (`Message`, `Request`, `Response`, `Stream`, `StringStream`, `UploadedFile`, `Uri`).
- Added comprehensive unit test suite for HTTP message components.

### Changed
- Refactored `MimeHeader` filter `match()` method to accept `ServerRequestInterface`.
- Improved HTTP Request handling including nested uploaded file normalization and attribute management.
- Enhanced HTTP Response status code assertions and reason phrase lookups.

## v0.11.4 - 2026-06-20
### Fixed
- Fixed the order in which forwarding IP headers are checked in `Context::getIpAddress()`, prioritizing headers like `HTTP_X_FORWARDED_FOR` over `HTTP_CLIENT_IP`.

## v0.11.3 - 2026-06-19
### Added
- Added `getIpAddress()` method to the `Context` class to retrieve client IP addresses (handling various forwarding headers).

### Fixed
- Fixed `Request::getParsedBody()` to return an empty array by default instead of `null` if the content type is unrecognized or unsupported.

### Removed
- Removed the deprecated authentication middleware components (`src/middleware/auth/`).
- Removed `RequestsMiddleware` (`src/middleware/requests/`).

## v0.11.2 - 2026-03-29
### Fixes
- Reworked the logic for the `FileSessionHandler` to be more robust to already existing session files.

## v0.11.1 - 2026-03-23
### Fixes
- Fixed an issue where the `FileSessionHandler` was not working as expected when saving file sessions.

## v0.11.0 - 2026-03-02
### Added
- New middleware filtering system with `PrefixFilter` and `ConfigurableFilter`.
- `MiddlewareFilter` for structured middleware filtering logic.

### Changed
- Refactored `ApplicationBuilder` to support new middleware filtering and improved instance access.
- Enhanced middleware filter management and cleanup.

### Fixed
- Fixed access to instances within the `ApplicationBuilder`.

## v0.10.0 - 2025-12-23
### Added
- Added a new ApplicationBuilder class to make it easy to create an instance of the Application class.
- Added initial unit tests for some essential components within this package.

## v0.9.3 - 2025-09-07
- Fixed a bug in the HTTPAuthentication middleware.

## v0.9.2 - 2025-04-13
- Removed unneeded exceptions from the core into submodules where there are necessary.  

## v0.9.1 - 2025-03-15
## Fixes
 - The header method in the implementation of PHP Message interface was completely implemented.

## v0.9.0 - 2025-01-18
First breakup of the ntentan core for a release:
 - Removed all the MVC components so they would be implemented externally.
 - Using a pipeline of requests and response objects that pass through a series of middleware to handle all application requests.

## v0.8.0 - 2024-07-13
Cleaning up the code before breaking apart the ntentan/core.

## v0.6.0 - 2020-02-25
First version with a CHANGELOG